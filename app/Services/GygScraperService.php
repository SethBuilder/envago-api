<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GygScraperService
{
    private const GYG_PARTNER_ID  = '0CVLYT9';
    private const GYG_UTM         = 'utm_medium=online_publisher';
    private const MAX_PHOTOS       = 8;
    private const MAX_REVIEWS      = 10;

    private const ENVAGO_CATEGORIES = [
        'buggy', 'surfe', 'kitesurf', 'barco', 'caiaque',
        'trilhas', 'snorkeling', 'mergulho', 'ecoturismo',
        'pesca', 'cavalgada', 'jet-ski',
    ];

    public function buildAffiliateUrl(string $gygUrl): string
    {
        $parsed = parse_url($gygUrl);
        parse_str($parsed['query'] ?? '', $params);
        $params['partner_id'] = self::GYG_PARTNER_ID;
        $params['utm_medium'] = 'online_publisher';

        // Normalize locale to pt-br (replace /xx/ or /xx-xx/ prefix, or add if missing)
        $path = $parsed['path'] ?? '/';
        $path = preg_replace('#^/[a-z]{2}(?:-[a-z]{2})?/#i', '/pt-br/', $path);
        if (!str_starts_with($path, '/pt-br/')) {
            $path = '/pt-br' . $path;
        }

        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? 'www.getyourguide.com') . $path;
        return $base . '?' . http_build_query($params);
    }

    /**
     * Scrape a GYG activity page via Apify Playwright.
     * Returns structured data array or throws on failure.
     */
    public function scrape(string $url): array
    {
        $token = config('services.apify.token');
        if (!$token) throw new \RuntimeException('APIFY_TOKEN not configured');

        $pageFunction = <<<'JS'
async function pageFunction(context) {
    const { page } = context;

    await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {});

    // Try JSON-LD first — most reliable
    const jsonLd = await page.evaluate(() => {
        const scripts = Array.from(document.querySelectorAll('script[type="application/ld+json"]'));
        for (const s of scripts) {
            try {
                const d = JSON.parse(s.textContent);
                if (d['@type'] === 'Product') return d;
                if (Array.isArray(d['@graph'])) {
                    const p = d['@graph'].find(g => g['@type'] === 'Product');
                    if (p) return p;
                }
            } catch(e) {}
        }
        return null;
    });

    const get = async (selectors, attr = null) => {
        for (const sel of selectors) {
            try {
                const el = await page.$(sel);
                if (!el) continue;
                const val = attr ? await el.getAttribute(attr) : await el.innerText();
                if (val && val.trim()) return val.trim();
            } catch(e) {}
        }
        return null;
    };

    const title = jsonLd?.name || await get(['h1[data-testid="activity-header-title"]', 'h1']);

    const description = jsonLd?.description || await get([
        '[data-testid="activity-about-text"]',
        '.activity-about__text',
        '[class*="ActivityAbout"] p',
    ]);

    // Photos: prefer JSON-LD images, fall back to gallery imgs
    let photos = [];
    if (Array.isArray(jsonLd?.image)) {
        photos = jsonLd.image.filter(Boolean).slice(0, 8);
    } else {
        photos = await page.$$eval(
            '[data-testid="gallery"] img, [class*="Gallery"] img, [class*="gallery"] img',
            imgs => imgs.map(i => i.src || i.dataset.src).filter(s => s && s.startsWith('http') && !s.includes('placeholder'))
        ).catch(() => []);
        photos = photos.slice(0, 8);
    }

    // Price
    let price = null;
    if (jsonLd?.offers?.price) {
        price = jsonLd.offers.price;
    } else {
        const priceText = await get([
            '[data-testid="price-section"] [class*="price"]',
            '[class*="PriceSection"] [class*="amount"]',
            '[class*="price-overview"]',
        ]);
        if (priceText) {
            const match = priceText.match(/[\d.,]+/);
            if (match) price = parseFloat(match[0].replace(',', '.'));
        }
    }

    // Duration (in minutes)
    const durationText = await get([
        '[data-testid="activity-highlights"] [class*="duration"]',
        '[class*="ActivityHighlights"] [class*="duration"]',
        '[class*="Duration"]',
    ]);
    let durationMinutes = null;
    if (durationText) {
        const hMatch = durationText.match(/(\d+)\s*h/i);
        const mMatch = durationText.match(/(\d+)\s*min/i);
        if (hMatch || mMatch) {
            durationMinutes = (hMatch ? parseInt(hMatch[1]) * 60 : 0) + (mMatch ? parseInt(mMatch[1]) : 0);
        }
    }

    // Operator name — CSS selectors miss it; match text patterns instead
    const operatorName = await page.evaluate(() => {
        const patterns = [
            /Activity provider[:\s]+([^\n]+)/i,
            /Provided by[:\s]+([^\n]+)/i,
            /Offered by[:\s]+([^\n]+)/i,
            /Fornecido por[:\s]+([^\n]+)/i,
            /Operador[:\s]+([^\n]+)/i,
        ];
        const text = document.body.innerText;
        for (const re of patterns) {
            const m = text.match(re);
            if (m && m[1].trim()) return m[1].trim();
        }
        return null;
    }).catch(() => null);

    // Category from breadcrumbs
    const categoryText = await page.$$eval(
        'nav[aria-label="breadcrumb"] a, [class*="Breadcrumb"] a, [class*="breadcrumb"] a',
        els => els.map(e => e.textContent.trim()).filter(Boolean)
    ).catch(() => []);

    // Location
    const location = await get([
        '[data-testid="location-section"] [class*="city"]',
        '[class*="LocationSection"] span',
        'address',
    ]);

    // Rating
    let avgRating = null, reviewCount = 0;
    if (jsonLd?.aggregateRating) {
        avgRating = parseFloat(jsonLd.aggregateRating.ratingValue || 0);
        reviewCount = parseInt(jsonLd.aggregateRating.reviewCount || 0);
    }

    // Reviews
    let reviews = [];
    if (Array.isArray(jsonLd?.review)) {
        reviews = jsonLd.review.slice(0, 10).map(r => ({
            author: r.author?.name || 'Viajante',
            rating: parseFloat(r.reviewRating?.ratingValue || 5),
            body: r.reviewBody || '',
        })).filter(r => r.body);
    }
    if (reviews.length === 0) {
        reviews = await page.$$eval(
            '[data-testid="review-card"], [class*="ReviewCard"], [class*="review-card"]',
            cards => cards.slice(0, 10).map(card => ({
                author: card.querySelector('[class*="AuthorName"], [class*="author"]')?.textContent?.trim() || 'Viajante',
                rating: parseFloat(card.querySelector('[class*="Rating"] span, [aria-label*="star"]')?.textContent || '5'),
                body: card.querySelector('[class*="ReviewText"], [class*="review-text"], p')?.textContent?.trim() || '',
            })).filter(r => r.body)
        ).catch(() => []);
    }

    return { title, description, photos, price, durationMinutes, operatorName, categoryText, location, avgRating, reviewCount, reviews };
}
JS;

        $response = Http::timeout(120)->post(
            "https://api.apify.com/v2/acts/apify~playwright-scraper/run-sync-get-dataset-items?token={$token}",
            [
                'startUrls'    => [['url' => $url]],
                'pageFunction' => $pageFunction,
                'proxyConfiguration' => ['useApifyProxy' => true],
            ]
        );

        if (!$response->successful() && $response->status() !== 201) {
            throw new \RuntimeException("Apify returned {$response->status()}: " . substr($response->body(), 0, 300));
        }

        $items = $response->json();
        if (empty($items) || !is_array($items)) {
            throw new \RuntimeException('Apify returned empty dataset');
        }

        return $items[0];
    }

    /**
     * Download a photo URL and store it under activities/{slug}/.
     * Returns the public URL or null on failure.
     */
    public function downloadPhoto(string $photoUrl, string $activitySlug, int $index): ?string
    {
        try {
            $response = Http::timeout(30)->withOptions(['stream' => false])->get($photoUrl);
            if (!$response->successful()) return null;

            $ext      = 'jpg';
            $ct       = $response->header('Content-Type');
            if (str_contains($ct, 'png'))  $ext = 'png';
            if (str_contains($ct, 'webp')) $ext = 'webp';

            $filename = "activities/{$activitySlug}/gyg_{$index}_{$activitySlug}." . $ext;
            Storage::disk('public')->put($filename, $response->body());

            return Storage::disk('public')->url($filename);
        } catch (\Throwable $e) {
            Log::warning('gyg: photo download failed', ['url' => $photoUrl, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Rewrite a GYG description with Claude for SEO + conversion.
     */
    public function rewriteDescription(string $original, string $activityName, string $city, string $category): string
    {
        $prompt = <<<PROMPT
You are writing a tour description for Envago, a Brazilian tour marketplace.

Rewrite the following GetYourGuide description for the activity "{$activityName}" in {$city} (category: {$category}).

Requirements:
- Do NOT copy verbatim — rewrite completely in your own words
- Language: Portuguese (PT-BR)
- Tone: warm, enthusiastic, tourist-facing — like a knowledgeable local friend
- Length: 3-4 short paragraphs
- Format: Markdown with ## section headers (What you'll do, What's included, Good to know)
- SEO: naturally include the activity name and city
- Conversion: end with a clear reason to book now
- Be specific and concrete — avoid generic filler

Original description to rewrite:
{$original}
PROMPT;

        $response = Http::timeout(60)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 1024,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) {
            Log::warning('gyg: claude description rewrite failed');
            return $original;
        }

        return collect($response->json('content'))->firstWhere('type', 'text')['text'] ?? $original;
    }

    /**
     * Extract highlights from description using Claude.
     */
    public function extractHighlights(string $description, string $activityName): array
    {
        $prompt = "Extract 4-6 key highlights from this tour description for \"{$activityName}\". Return a JSON array of short strings (max 10 words each). Only the JSON array, nothing else.\n\n{$description}";

        $response = Http::timeout(30)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 256,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) return [];

        $text = collect($response->json('content'))->firstWhere('type', 'text')['text'] ?? '[]';
        $text = preg_replace('/```json|```/', '', $text);

        return json_decode(trim($text), true) ?? [];
    }

    /**
     * Map a GYG category string to an Envago category slug.
     * Returns ['slug' => ..., 'name' => ..., 'is_new' => bool].
     */
    public function mapCategory(array $breadcrumbs, string $activityTitle): array
    {
        $categoryHint = implode(', ', array_slice($breadcrumbs, -3));
        $list         = implode(', ', self::ENVAGO_CATEGORIES);

        $prompt = <<<PROMPT
Map this GetYourGuide activity to an Envago category.

Activity title: "{$activityTitle}"
GYG breadcrumbs: "{$categoryHint}"

Existing Envago categories: {$list}

Rules:
1. If the activity clearly matches one of the existing categories, return that slug exactly.
2. If nothing fits, create a new slug (lowercase, hyphens, Portuguese noun, e.g. "parapente", "quad", "sandboard") and a display name in Portuguese.

Respond with JSON only: {"slug": "...", "name": "...", "is_new": true/false}
PROMPT;

        $response = Http::timeout(20)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 64,
            'messages'   => [['role' => 'user', 'content' => $prompt]],
        ]);

        if (!$response->successful()) {
            return ['slug' => 'ecoturismo', 'name' => 'Ecoturismo', 'is_new' => false];
        }

        $text = collect($response->json('content'))->firstWhere('type', 'text')['text'] ?? '{}';
        $text = preg_replace('/```json|```/', '', $text);
        $data = json_decode(trim($text), true) ?? [];

        return [
            'slug'   => $data['slug']   ?? 'ecoturismo',
            'name'   => $data['name']   ?? 'Ecoturismo',
            'is_new' => $data['is_new'] ?? false,
        ];
    }

    /**
     * Parse price from various formats to float BRL.
     */
    public function parsePrice(mixed $raw): float
    {
        if (is_numeric($raw)) return (float) $raw;
        if (is_string($raw)) {
            $cleaned = preg_replace('/[^\d,.]/', '', $raw);
            $cleaned = str_replace(',', '.', $cleaned);
            return (float) $cleaned ?: 0.0;
        }
        return 0.0;
    }

    /**
     * Parse duration string to minutes.
     */
    public function parseDuration(mixed $raw): int
    {
        if (is_int($raw)) return $raw;
        if (is_string($raw)) {
            $h = 0; $m = 0;
            if (preg_match('/(\d+)\s*h/i', $raw, $hm)) $h = (int) $hm[1];
            if (preg_match('/(\d+)\s*min/i', $raw, $mm)) $m = (int) $mm[1];
            if ($h || $m) return $h * 60 + $m;
            if (preg_match('/(\d+)\s*day/i', $raw, $dm)) return (int) $dm[1] * 480;
        }
        return 120;
    }
}
