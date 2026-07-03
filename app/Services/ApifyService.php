<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ApifyService
{
    private const ACTOR       = 'apify~instagram-scraper';
    private const MAX_BYTES   = 100 * 1024 * 1024; // 100 MB
    private const TIMEOUT_SEC = 90;

    public function scrapeInstagramVideo(string $url): ?array
    {
        $token = config('services.apify.token');
        if (!$token) {
            Log::error('apify: APIFY_TOKEN not configured');
            return null;
        }

        try {
            $response = Http::timeout(self::TIMEOUT_SEC)
                ->post("https://api.apify.com/v2/acts/" . self::ACTOR . "/run-sync-get-dataset-items?token={$token}", [
                    'directUrls'  => [$url],
                    'resultsType' => 'details',
                    'resultsLimit' => 1,
                ]);

            if (!$response->successful()) {
                Log::warning('apify: scrape failed', ['status' => $response->status(), 'url' => $url]);
                return null;
            }

            $items = $response->json();
            if (empty($items) || !is_array($items)) {
                return null;
            }

            $item = $items[0];

            Log::info('apify: raw item keys', ['keys' => array_keys($item), 'videoUrl' => $item['videoUrl'] ?? null, 'videoPlaybackUrl' => $item['videoPlaybackUrl'] ?? null, 'musicInfo' => $item['musicInfo'] ?? null, 'coauthorProducers' => null]);

            // Video URL — Instagram Reels / videos
            $videoUrl = $item['videoUrl'] ?? $item['video_url'] ?? null;
            if (!$videoUrl) {
                Log::warning('apify: no videoUrl in response', ['url' => $url]);
                return null;
            }

            $posterUrl        = $item['displayUrl'] ?? $item['thumbnailUrl'] ?? $item['thumbnail_url'] ?? null;
            $instagramHandle  = $item['ownerUsername'] ?? $item['owner_username'] ?? null;

            return [
                'video_url'        => $videoUrl,
                'poster_url'       => $posterUrl,
                'instagram_handle' => $instagramHandle,
            ];
        } catch (\Throwable $e) {
            Log::error('apify: exception', ['error' => $e->getMessage(), 'url' => $url]);
            return null;
        }
    }

    /**
     * Download a video URL to local temp storage.
     * Returns the saved filename (basename) or null on failure.
     */
    public function downloadToTemp(string $videoUrl, string $sessionId, string $prefix = ''): ?string
    {
        try {
            // Check Content-Length before downloading
            $head = Http::timeout(15)->head($videoUrl);
            $contentLength = (int) $head->header('Content-Length');
            if ($contentLength > self::MAX_BYTES) {
                Log::warning('apify: video too large', ['bytes' => $contentLength, 'max' => self::MAX_BYTES]);
                return null;
            }

            $response = Http::timeout(self::TIMEOUT_SEC)
                ->withOptions(['stream' => true])
                ->get($videoUrl);

            if (!$response->successful()) {
                return null;
            }

            $filename = ($prefix ? $prefix . '__' : '') . 'video_' . Str::random(8) . '.mp4';
            $dest     = "temp/{$sessionId}/{$filename}";

            // Stream in chunks to avoid memory spike
            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                Log::warning('apify: downloaded body exceeds limit');
                return null;
            }

            Storage::disk('local')->put($dest, $body);

            return $filename;
        } catch (\Throwable $e) {
            Log::error('apify: download failed', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
