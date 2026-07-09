<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class YouTubeService
{
    private string $apiKey;
    private string $base = 'https://www.googleapis.com/youtube/v3';

    public function __construct()
    {
        $this->apiKey = config('services.youtube.key');
    }

    public function researchDestination(string $destination): array
    {
        $queries = [
            "$destination passeios",
            "$destination o que fazer",
            "$destination dicas viagem",
        ];

        $videoIds = [];
        foreach ($queries as $query) {
            $ids = $this->searchVideoIds($query, 4);
            $videoIds = array_unique(array_merge($videoIds, $ids));
            if (count($videoIds) >= 10) break;
        }

        $videoIds = array_slice($videoIds, 0, 10);

        $videos = $this->getVideoDetails($videoIds);

        foreach ($videos as &$video) {
            $video['top_comments'] = $this->getTopComments($video['id'], 15);
        }

        return [
            'destination' => $destination,
            'videos' => $videos,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    private function searchVideoIds(string $query, int $max): array
    {
        $response = Http::get("$this->base/search", [
            'part' => 'id',
            'q' => $query,
            'type' => 'video',
            'relevanceLanguage' => 'pt',
            'regionCode' => 'BR',
            'maxResults' => $max,
            'key' => $this->apiKey,
        ]);

        if (!$response->ok()) return [];

        return collect($response->json('items', []))
            ->pluck('id.videoId')
            ->filter()
            ->values()
            ->all();
    }

    private function getVideoDetails(array $videoIds): array
    {
        if (empty($videoIds)) return [];

        $response = Http::get("$this->base/videos", [
            'part' => 'snippet,statistics,contentDetails',
            'id' => implode(',', $videoIds),
            'key' => $this->apiKey,
        ]);

        if (!$response->ok()) return [];

        return collect($response->json('items', []))->map(function ($item) {
            return [
                'id' => $item['id'],
                'title' => $item['snippet']['title'],
                'description' => substr($item['snippet']['description'] ?? '', 0, 800),
                'channel' => $item['snippet']['channelTitle'],
                'published_at' => $item['snippet']['publishedAt'],
                'tags' => array_slice($item['snippet']['tags'] ?? [], 0, 20),
                'view_count' => (int) ($item['statistics']['viewCount'] ?? 0),
                'like_count' => (int) ($item['statistics']['likeCount'] ?? 0),
                'comment_count' => (int) ($item['statistics']['commentCount'] ?? 0),
                'duration' => $item['contentDetails']['duration'] ?? null,
            ];
        })->sortByDesc('view_count')->values()->all();
    }

    private function getTopComments(string $videoId, int $max): array
    {
        $response = Http::get("$this->base/commentThreads", [
            'part' => 'snippet',
            'videoId' => $videoId,
            'order' => 'relevance',
            'maxResults' => $max,
            'key' => $this->apiKey,
        ]);

        if (!$response->ok()) return [];

        return collect($response->json('items', []))->map(function ($item) {
            $c = $item['snippet']['topLevelComment']['snippet'];
            return [
                'text' => substr($c['textDisplay'] ?? '', 0, 300),
                'likes' => (int) ($c['likeCount'] ?? 0),
            ];
        })->filter(fn($c) => strlen($c['text']) > 20)->values()->all();
    }
}
