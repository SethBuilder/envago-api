<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\AdminLaraController;
use App\Services\LaraService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLaraMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 400;
    public int $tries   = 1;

    public function __construct(
        private string $laraJobId,
        private string $sessionId,
        private string $userMessage,
    ) {}

    public function handle(LaraService $lara, AdminLaraController $adminLara): void
    {
        DB::table('lara_jobs')
            ->where('id', $this->laraJobId)
            ->update(['status' => 'processing', 'updated_at' => now()]);

        try {
            $history = DB::table('lara_conversations')
                ->where('session_id', $this->sessionId)
                ->where('created_at', '>=', now()->subDay())
                ->orderBy('created_at')
                ->get();

            $reply = $lara->respond(
                $history->all(),
                $this->userMessage,
                'admin',
                null,
                null,
                $adminLara->makeAdminToolExecutor(),
            );

            DB::table('lara_conversations')->insert([
                ['session_id' => $this->sessionId, 'uuid' => $this->sessionId, 'role' => 'user',      'message' => $this->userMessage, 'created_at' => now(), 'updated_at' => now()],
                ['session_id' => $this->sessionId, 'uuid' => $this->sessionId, 'role' => 'assistant', 'message' => $reply,             'created_at' => now(), 'updated_at' => now()],
            ]);

            DB::table('lara_jobs')
                ->where('id', $this->laraJobId)
                ->update(['status' => 'done', 'result_message' => $reply, 'updated_at' => now()]);

        } catch (\Throwable $e) {
            Log::error('lara_job: failed', ['job_id' => $this->laraJobId, 'error' => $e->getMessage()]);

            DB::table('lara_jobs')
                ->where('id', $this->laraJobId)
                ->update(['status' => 'failed', 'error' => $e->getMessage(), 'updated_at' => now()]);
        }
    }
}
