<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UrlFetcher
{
    public function buildImageBlock(string $bytes, string $mime): array
    {
        return [
            'type'   => 'image',
            'source' => [
                'type'       => 'base64',
                'media_type' => $mime ?: 'image/jpeg',
                'data'       => base64_encode($bytes),
            ],
        ];
    }

    /**
     * Fetch a URL and return Anthropic-compatible content.
     *
     * @param  callable|null  $onImage  fn(string $bytes, string $mime, string $filename): void
     * @return string|array  string for text/error, array of content blocks for image/PDF
     */
    public function fetch(string $url, array $options = [], ?callable $onImage = null): string|array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return 'Invalid URL.';
        }

        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                return "Could not fetch URL (HTTP {$response->status()}).";
            }

            $contentType = strtolower($response->header('Content-Type') ?? '');
            $mime        = explode(';', $contentType)[0];
            $body        = $response->body();

            if (str_starts_with($mime, 'image/')) {
                $filename = basename(parse_url($url, PHP_URL_PATH)) ?: uniqid('img_') . '.jpg';

                if ($onImage) {
                    $onImage($body, $mime, $filename);
                }

                return [$this->buildImageBlock($body, $mime)];
            }

            if (str_contains($mime, 'pdf')) {
                return [
                    ['type' => 'text',     'text' => "PDF fetched from {$url}"],
                    ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => base64_encode($body)]],
                ];
            }

            // HTML or plain text — strip tags, extract image URLs, truncate
            $imageUrls = $this->extractImageUrls($body, $url);
            $text      = strip_tags($body);
            $text      = preg_replace('/\s+/', ' ', trim($text));
            $text      = substr($text, 0, 4000);

            if ($imageUrls) {
                $lines = array_map(
                    fn($img) => '- ' . $img['url'] . ($img['alt'] ? " (alt: \"{$img['alt']}\")" : ''),
                    $imageUrls
                );
                $text .= "\n\nImages found on page:\n" . implode("\n", $lines);
            }

            return $text;

        } catch (\Throwable $e) {
            Log::warning('url_fetcher: fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            return "Could not fetch URL: {$e->getMessage()}";
        }
    }

    private function extractImageUrls(string $html, string $baseUrl): array
    {
        $images = [];
        preg_match_all('/<img[^>]+>/i', $html, $tags);

        foreach (array_slice($tags[0], 0, 10) as $tag) {
            preg_match('/src=["\']([^"\']+)["\']/', $tag, $srcMatch);
            $src = $srcMatch[1] ?? null;
            if (!$src || str_starts_with($src, 'data:')) continue;

            if (!str_starts_with($src, 'http')) {
                $parsed = parse_url($baseUrl);
                $src    = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . '/' . ltrim($src, '/');
            }

            $path = strtolower(parse_url($src, PHP_URL_PATH) ?? '');
            if (str_ends_with($path, '.svg') || str_ends_with($path, '.gif')) continue;

            preg_match('/alt=["\']([^"\']*)["\']/', $tag, $altMatch);
            $images[] = ['url' => $src, 'alt' => $altMatch[1] ?? ''];
        }

        return $images;
    }
}
