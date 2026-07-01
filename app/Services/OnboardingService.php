<?php

namespace App\Services;

use App\Mail\AccountActivationMail;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingService
{
    public function __construct(private ActivityService $activityService) {}

    /**
     * Create or refresh a provider account from Lara onboarding JSON.
     *
     * @param  array   $data       Parsed onboard JSON (operator + tours)
     * @param  string  $sessionId  UUID session from /parceiros chat
     * @return string  Preview URL for the operator's page
     */
    public function onboard(array $data, string $sessionId): string
    {
        return DB::transaction(function () use ($data, $sessionId) {
            $op      = $data['operator'];
            $email   = trim($op['email'] ?? '');
            $bizName = trim($op['business_name'] ?? '');
            $city    = trim($op['city'] ?? '');
            $state   = strtoupper(trim($op['state'] ?? ''));

            $token = Str::random(64);

            $user = User::where('email', $email)->first();
            if (!$user) {
                $user = User::create([
                    'name'                        => $bizName,
                    'email'                       => $email,
                    'password'                    => Hash::make(Str::random(32)),
                    'role'                        => 'provider',
                    'activation_token'            => $token,
                    'activation_token_expires_at' => now()->addHours(72),
                ]);
            } else {
                $user->update([
                    'activation_token'            => $token,
                    'activation_token_expires_at' => now()->addHours(72),
                ]);
            }

            $provider = Provider::where('user_id', $user->id)->first();
            if (!$provider) {
                $provider = Provider::create([
                    'user_id'           => $user->id,
                    'business_name'     => $bizName,
                    'whatsapp_number'   => $op['whatsapp'] ?: null,
                    'status'            => 'pending',
                    'lara_onboarded_at' => now(),
                ]);
            } else {
                $provider->update(array_filter([
                    'whatsapp_number'   => $op['whatsapp'] ?: null,
                    'lara_onboarded_at' => $provider->lara_onboarded_at ?? now(),
                ]));
            }

            // Link conversation rows to provider
            DB::table('lara_conversations')
                ->where('session_id', $sessionId)
                ->update(['provider_id' => $provider->id, 'email' => $email, 'whatsapp' => $op['whatsapp'] ?: null]);

            // Create activities via ActivityService
            foreach ($data['tours'] ?? [] as $tour) {
                $activityData = [
                    'name'             => $tour['name'],
                    'category_slug'    => $tour['category_slug'] ?? null,
                    'category_name'    => $tour['category_name'] ?? null,
                    'price'            => $tour['price'],
                    'duration_minutes' => $tour['duration_minutes'],
                    'capacity'         => $tour['capacity'] ?? $tour['max_seats'] ?? 4,
                    'city'             => $city,
                    'state'            => $state,
                    'description'      => $tour['description'] ?? null,
                    'meeting_point'    => $tour['meeting_point'] ?? null,
                    'groups'           => $tour['schedules'] ?? [],
                ];

                // Resolve temp photos and videos for this tour
                $photos = $this->resolveTempPhotos($sessionId, $tour['photos'] ?? []);
                $videos = $this->resolveTempVideos($sessionId, $tour['videos'] ?? []);

                $this->activityService->createActivity($provider, $activityData, $photos, $videos);
            }

            // Clean up temp folder for this session
            $this->cleanTempFolder($sessionId);

            // Send activation email
            try {
                Mail::to($email)->send(new AccountActivationMail($user));
            } catch (\Throwable $e) {
                Log::error('onboarding: activation email failed', ['error' => $e->getMessage()]);
            }

            $frontendUrl = rtrim(env('FRONTEND_URL', 'https://envago.live'), '/');
            return "{$frontendUrl}/operadores/{$provider->slug}?preview=true";
        });
    }

    private function resolveTempPhotos(string $sessionId, array $filenames): array
    {
        if (empty($filenames)) return [];

        $paths = [];
        foreach ($filenames as $filename) {
            $relative = "temp/{$sessionId}/" . basename($filename);
            if (Storage::disk('local')->exists($relative)) {
                $paths[] = Storage::disk('local')->path($relative);
            } else {
                Log::warning("onboarding: temp photo not found", ['session' => $sessionId, 'file' => $filename]);
            }
        }
        return $paths;
    }

    /**
     * Resolve temp video paths + poster URLs for a tour.
     * Returns array of ['path' => ..., 'poster_url' => ...] for each video.
     */
    private function resolveTempVideos(string $sessionId, array $filenames): array
    {
        if (empty($filenames)) return [];

        $videos = [];
        foreach ($filenames as $filename) {
            $relative = "temp/{$sessionId}/" . basename($filename);
            if (!Storage::disk('local')->exists($relative)) {
                Log::warning("onboarding: temp video not found", ['session' => $sessionId, 'file' => $filename]);
                continue;
            }
            $posterPath = $relative . '.poster';
            $posterUrl  = Storage::disk('local')->exists($posterPath)
                ? trim(Storage::disk('local')->get($posterPath))
                : null;

            $videos[] = [
                'path'       => Storage::disk('local')->path($relative),
                'poster_url' => $posterUrl,
            ];
        }
        return $videos;
    }

    private function cleanTempFolder(string $sessionId): void
    {
        $tempDir = "temp/{$sessionId}";
        if (Storage::disk('local')->exists($tempDir)) {
            Storage::disk('local')->deleteDirectory($tempDir);
        }
    }

    private function parseBankDetails(?array $bank): ?array
    {
        if (empty($bank)) return null;

        return array_filter([
            'beneficiary'  => $bank['beneficiary'] ?? null,
            'cpf_cnpj'     => $bank['cpf_cnpj'] ?? null,
            'bank_name'    => $bank['bank_name'] ?? null,
            'swift'        => isset($bank['swift']) ? strtoupper($bank['swift']) : null,
            'branch'       => $bank['branch'] ?? null,
            'account'      => $bank['account'] ?? null,
            'account_type' => $bank['account_type'] ?? null,
            'address'      => $bank['address'] ?? null,
            'city'         => $bank['city'] ?? null,
            'state'        => isset($bank['state']) ? strtoupper($bank['state']) : null,
            'postcode'     => $bank['postcode'] ?? null,
            'country'      => 'Brasil',
        ]);
    }
}
