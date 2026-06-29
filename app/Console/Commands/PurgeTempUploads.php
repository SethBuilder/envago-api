<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeTempUploads extends Command
{
    protected $signature   = 'uploads:purge-temp';
    protected $description = 'Delete temp upload folders older than 24 hours (abandoned Lara sessions)';

    public function handle(): void
    {
        $cutoff = now()->subHours(24)->timestamp;
        $purged = 0;

        $disk = Storage::disk('local');

        foreach ($disk->directories('temp') as $dir) {
            $files = $disk->files($dir);
            if (empty($files)) {
                $disk->deleteDirectory($dir);
                $purged++;
                continue;
            }

            $newest = max(array_map(fn($f) => $disk->lastModified($f), $files));
            if ($newest < $cutoff) {
                $disk->deleteDirectory($dir);
                $purged++;
            }
        }

        $this->info("Purged {$purged} temp folder(s).");
    }
}
