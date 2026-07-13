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

    public function fetchYouTubeTranscript(string $videoId): ?string
    {
        $token = config('services.apify.token');
        if (!$token) return null;

        try {
            $response = Http::timeout(30)->post(
                "https://api.apify.com/v2/acts/pintostudio~youtube-transcript-scraper/run-sync-get-dataset-items?token={$token}",
                ['videoUrl' => "https://www.youtube.com/watch?v={$videoId}"]
            );

            if (!$response->successful() && $response->status() !== 201) {
                Log::warning('apify: transcript fetch failed', [
                    'video_id' => $videoId,
                    'status'   => $response->status(),
                    'body'     => substr($response->body(), 0, 300),
                ]);
                return null;
            }

            $items = $response->json();
            if (empty($items)) return null;

            $segments = $items[0]['data'] ?? $items[0]['searchResult'] ?? [];
            if (empty($segments)) return null;

            return collect($segments)->pluck('text')->join(' ');
        } catch (\Throwable $e) {
            Log::error('apify: transcript exception', ['video_id' => $videoId, 'error' => $e->getMessage()]);
            return null;
        }
    }

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

            // Video URL — Instagram Reels / videos
            $videoUrl = $item['videoUrl'] ?? $item['video_url'] ?? null;
            if (!$videoUrl) {
                Log::warning('apify: no videoUrl in response', ['url' => $url]);
                return null;
            }

            $posterUrl       = $item['displayUrl'] ?? $item['thumbnailUrl'] ?? $item['thumbnail_url'] ?? null;
            $instagramHandle = $item['ownerUsername'] ?? $item['owner_username'] ?? null;
            $audioUrl        = $item['audioUrl'] ?? null;

            return [
                'video_url'        => $videoUrl,
                'audio_url'        => $audioUrl,
                'poster_url'       => $posterUrl,
                'instagram_handle' => $instagramHandle,
            ];
        } catch (\Throwable $e) {
            Log::error('apify: exception', ['error' => $e->getMessage(), 'url' => $url]);
            return null;
        }
    }

    /**
     * Download a video URL to local temp storage, optionally merging a separate audio stream.
     * Returns the saved filename (basename) or null on failure.
     */
    public function downloadToTemp(string $videoUrl, string $sessionId, string $prefix = '', ?string $audioUrl = null): ?string
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

            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                Log::warning('apify: downloaded body exceeds limit');
                return null;
            }

            Storage::disk('local')->put($dest, $body);

            // Merge separate audio stream in-place if provided
            if ($audioUrl) {
                $this->mergeAudioInPlace(Storage::disk('local')->path($dest), $audioUrl);
            }

            return $filename;
        } catch (\Throwable $e) {
            Log::error('apify: download failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Download audio from $audioUrl and merge it into $videoPath in-place using FFmpeg.
     * Falls back to the original video-only file if anything fails.
     */
    /**
     * Crawl a JS-rendered page using Apify's Website Content Crawler.
     * Returns markdown/text content with image references Claude can call fetch_url on.
     */
    public function crawlWebsite(string $url): string
    {
        $token = config('services.apify.token');
        if (!$token) return 'Apify not configured.';

        try {
            $response = Http::timeout(120)->post(
                "https://api.apify.com/v2/acts/apify~website-content-crawler/run-sync-get-dataset-items?token={$token}",
                [
                    'startUrls'     => [['url' => $url]],
                    'maxCrawlPages' => 1,
                    'maxCrawlDepth' => 0,
                    'crawlerType'   => 'playwright:firefox',
                ]
            );

            if (!$response->successful()) {
                Log::warning('apify: crawlWebsite failed', ['url' => $url, 'status' => $response->status()]);
                return "Could not render page (HTTP {$response->status()}).";
            }

            $items = $response->json();
            if (empty($items)) return 'Page rendered but no content found.';

            $item     = $items[0];
            $content  = $item['markdown'] ?? $item['text'] ?? strip_tags($item['content'] ?? '');
            $content  = preg_replace('/\s+/', ' ', trim($content));

            return substr($content, 0, 5000);

        } catch (\Throwable $e) {
            Log::error('apify: crawlWebsite exception', ['url' => $url, 'error' => $e->getMessage()]);
            return "Could not render page: {$e->getMessage()}";
        }
    }

    private function mergeAudioInPlace(string $videoPath, string $audioUrl): void
    {
        $audioPath  = dirname($videoPath) . '/audio_' . Str::random(8) . '.aac';
        $mergedPath = dirname($videoPath) . '/merged_' . Str::random(8) . '.mp4';

        try {
            $audioResponse = Http::timeout(self::TIMEOUT_SEC)->get($audioUrl);
            if (!$audioResponse->successful()) return;

            file_put_contents($audioPath, $audioResponse->body());

            $cmd = sprintf(
                'ffmpeg -i %s -i %s -c:v copy -c:a aac -map 0:v:0 -map 1:a:0 -shortest -y %s 2>&1',
                escapeshellarg($videoPath),
                escapeshellarg($audioPath),
                escapeshellarg($mergedPath)
            );

            exec($cmd, $output, $exitCode);

            @unlink($audioPath);

            if ($exitCode === 0 && file_exists($mergedPath)) {
                @unlink($videoPath);
                rename($mergedPath, $videoPath);
            } else {
                @unlink($mergedPath);
                Log::warning('apify: ffmpeg merge failed', ['exit' => $exitCode, 'tail' => implode("\n", array_slice($output, -5))]);
            }
        } catch (\Throwable $e) {
            @unlink($audioPath);
            @unlink($mergedPath);
            Log::error('apify: mergeAudioInPlace exception', ['error' => $e->getMessage()]);
        }
    }
}
