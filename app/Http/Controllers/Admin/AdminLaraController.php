<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ResearchData;
use App\Services\ApifyService;
use App\Services\LaraService;
use App\Services\YouTubeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminLaraController extends Controller
{
    private function sessionId(): string
    {
        return 'admin_' . auth()->id();
    }

    public function show()
    {
        $sessionId = $this->sessionId();

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['role', 'message', 'created_at']);

        return view('admin.lara-chat', compact('history'));
    }

    public function message(Request $request, LaraService $lara)
    {
        $request->validate(['message' => 'required|string|max:4000']);

        $sessionId   = $this->sessionId();
        $userMessage = $request->input('message');

        set_time_limit(400);

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at')
            ->get();

        $reply = $lara->respond(
            $history->all(),
            $userMessage,
            'admin',
            null,
            null,
            $this->makeAdminToolExecutor()
        );

        DB::table('lara_conversations')->insert([
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'user',      'message' => $userMessage, 'created_at' => now(), 'updated_at' => now()],
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'assistant', 'message' => $reply,       'created_at' => now(), 'updated_at' => now()],
        ]);

        return response()->json(['reply' => $reply]);
    }

    public function reset()
    {
        DB::table('lara_conversations')
            ->where('session_id', $this->sessionId())
            ->delete();

        return response()->json(['ok' => true]);
    }

    private function makeAdminToolExecutor(): callable
    {
        return function (string $name, array $input): mixed {
            return match ($name) {
                'query_providers'      => $this->toolQueryProviders($input),
                'query_bookings'       => $this->toolQueryBookings($input),
                'query_scope_leads'    => $this->toolQueryScopeLeads($input),
                'search_conversations' => $this->toolSearchConversations($input),
                'query_activities'     => $this->toolQueryActivities($input),
                'save_instruction'     => $this->toolSaveInstruction($input),
                'list_instructions'    => $this->toolListInstructions($input),
                'delete_instruction'   => $this->toolDeleteInstruction($input),
                'query_google_ads'     => $this->toolQueryGoogleAds($input),
                'generate_creative'    => $this->toolGenerateCreative($input),
                'create_meta_ad'       => $this->toolCreateMetaAd($input),
                'list_meta_campaigns'  => $this->toolListMetaCampaigns($input),
                'get_meta_insights'    => $this->toolGetMetaInsights($input),
                'edit_meta_campaign'   => $this->toolEditMetaCampaign($input),
                'create_article'        => $this->toolCreateArticle($input),
                'update_article'        => $this->toolUpdateArticle($input),
                'get_article'           => $this->toolGetArticle($input),
                'list_articles'         => $this->toolListArticles($input),
                'research_destination'  => $this->toolResearchDestination($input),
                'query_leads'           => $this->toolQueryLeads($input),
                'suggest_articles'      => $this->toolSuggestArticles($input),
                default                 => "Unknown tool: {$name}",
            };
        };
    }

    private function toolQueryProviders(array $input): string
    {
        $query = DB::table('providers')
            ->join('users', 'users.id', '=', 'providers.user_id')
            ->select('providers.id', 'providers.business_name', 'providers.whatsapp_number',
                     'providers.status', 'providers.bank_details', 'providers.created_at', 'users.email');

        if (!empty($input['status'])) {
            $query->where('providers.status', $input['status']);
        }
        if (!empty($input['city'])) {
            $query->whereIn('providers.id', function ($sub) use ($input) {
                $sub->select('provider_id')->from('activities')
                    ->where('city', 'like', '%' . $input['city'] . '%');
            });
        }

        $providers = $query->orderByDesc('providers.created_at')->limit($input['limit'] ?? 20)->get();

        if ($providers->isEmpty()) return 'No providers found matching those filters.';

        $lines = $providers->map(fn($p) => implode(' | ', array_filter([
            "ID:{$p->id}",
            $p->business_name,
            $p->email,
            $p->whatsapp_number,
            'status:' . $p->status,
            $p->bank_details ? 'has-bank' : 'NO-bank',
            date('Y-m-d', strtotime($p->created_at)),
        ])))->join("\n");

        return "Found {$providers->count()} providers:\n\n{$lines}";
    }

    private function toolQueryBookings(array $input): string
    {
        $period = $input['period'] ?? 'all';

        $query = DB::table('bookings');

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subDays(7));
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if (!empty($input['provider_id'])) {
            $query->whereIn('schedule_id', function ($sub) use ($input) {
                $sub->select('schedules.id')
                    ->from('schedules')
                    ->join('activities', 'activities.id', '=', 'schedules.activity_id')
                    ->where('activities.provider_id', $input['provider_id']);
            });
        }

        $total     = (clone $query)->count();
        $confirmed = (clone $query)->where('status', 'confirmed')->count();
        $revenue   = (clone $query)->where('status', 'confirmed')->sum('total_price');

        return "Bookings ({$period}): {$total} total, {$confirmed} confirmed, R$ " . number_format($revenue, 2, ',', '.') . " revenue.";
    }

    private function toolQueryScopeLeads(array $input): string
    {
        $query = DB::table('scope_leads')->orderByDesc('created_at');

        if (!empty($input['offering'])) {
            $query->where('offering', 'like', '%' . $input['offering'] . '%');
        }

        $leads = $query->limit($input['limit'] ?? 20)->get();

        if ($leads->isEmpty()) return 'No scope leads found.';

        $lines = $leads->map(fn($l) => implode(' | ', array_filter([
            $l->business_name,
            $l->location,
            'offers:' . $l->offering,
            $l->price_range ? 'price:' . $l->price_range : null,
            $l->whatsapp ? 'wa:' . $l->whatsapp : null,
            date('Y-m-d', strtotime($l->created_at)),
        ])))->join("\n");

        $summaries = $leads->map(fn($l) => '- (' . date('Y-m-d', strtotime($l->created_at)) . ') ' . $l->summary)->join("\n");

        return "Found {$leads->count()} scope leads:\n\n{$lines}\n\nSummaries:\n{$summaries}";
    }

    private function toolSearchConversations(array $input): string
    {
        $keyword = $input['query'] ?? '';
        $role    = $input['role'] ?? null;

        $query = DB::table('lara_conversations')
            ->where('message', 'like', '%' . $keyword . '%');

        if ($role && in_array($role, ['user', 'assistant'])) {
            $query->where('role', $role);
        }

        $results = $query->orderByDesc('created_at')->limit($input['limit'] ?? 10)->get();

        if ($results->isEmpty()) return "No conversations found containing '{$keyword}'.";

        $lines = $results->map(fn($r) =>
            "[{$r->created_at}] [{$r->role}] [session:{$r->session_id}]\n" .
            substr($r->message, 0, 400) . (strlen($r->message) > 400 ? '...' : '')
        )->join("\n\n---\n\n");

        return "Found {$results->count()} messages containing '{$keyword}':\n\n{$lines}";
    }

    private function toolQueryActivities(array $input): string
    {
        $query = DB::table('activities')
            ->join('providers', 'providers.id', '=', 'activities.provider_id')
            ->leftJoin('categories', 'categories.id', '=', 'activities.category_id')
            ->select('activities.name', 'activities.price', 'activities.city', 'activities.state',
                     'providers.business_name', 'categories.name as category_name')
            ->where('activities.is_active', true);

        if (!empty($input['city'])) {
            $query->where('activities.city', 'like', '%' . $input['city'] . '%');
        }
        if (!empty($input['category'])) {
            $query->where('categories.slug', $input['category']);
        }
        if (!empty($input['provider_id'])) {
            $query->where('activities.provider_id', $input['provider_id']);
        }

        $activities = $query->limit($input['limit'] ?? 20)->get();

        if ($activities->isEmpty()) return 'No activities found.';

        $lines = $activities->map(fn($a) => implode(' | ', array_filter([
            $a->name,
            $a->business_name,
            ($a->city ?? '') . ($a->state ? ', ' . $a->state : ''),
            'R$' . number_format($a->price, 0),
            $a->category_name ?? 'no category',
        ])))->join("\n");

        return "Found {$activities->count()} activities:\n\n{$lines}";
    }

    private function toolSaveInstruction(array $input): string
    {
        $context     = $input['context'] ?? '';
        $instruction = trim($input['instruction'] ?? '');

        if (!in_array($context, ['provider', 'tourist'])) {
            return 'Invalid context. Must be "provider" or "tourist".';
        }
        if (empty($instruction)) {
            return 'Instruction text cannot be empty.';
        }

        $id = DB::table('lara_instructions')->insertGetId([
            'context'     => $context,
            'instruction' => $instruction,
            'active'      => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return "Instruction #{$id} saved for context '{$context}'.";
    }

    private function toolListInstructions(array $input): string
    {
        $query = DB::table('lara_instructions');

        if (!empty($input['context'])) {
            $query->where('context', $input['context']);
        }
        if (isset($input['active'])) {
            $query->where('active', $input['active']);
        }

        $instructions = $query->orderByDesc('created_at')->get();

        if ($instructions->isEmpty()) return 'No instructions found.';

        $lines = $instructions->map(fn($i) =>
            "#{$i->id} [{$i->context}] [" . ($i->active ? 'ACTIVE' : 'INACTIVE') . "]\n{$i->instruction}"
        )->join("\n\n");

        return "Found {$instructions->count()} instructions:\n\n{$lines}";
    }

    private function toolDeleteInstruction(array $input): string
    {
        $id        = $input['id'] ?? null;
        $permanent = $input['permanent'] ?? false;

        if (!$id) return 'Instruction ID is required.';

        $instruction = DB::table('lara_instructions')->find($id);
        if (!$instruction) return "Instruction #{$id} not found.";

        if ($permanent) {
            DB::table('lara_instructions')->where('id', $id)->delete();
            return "Instruction #{$id} permanently deleted.";
        }

        DB::table('lara_instructions')->where('id', $id)->update(['active' => false, 'updated_at' => now()]);
        return "Instruction #{$id} deactivated.";
    }

    private function googleAdsAccessToken(): ?string
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.google.ads.client_id'),
            'client_secret' => config('services.google.ads.client_secret'),
            'refresh_token' => config('services.google.ads.refresh_token'),
            'grant_type'    => 'refresh_token',
        ]);

        return $response->json('access_token');
    }

    private function toolQueryGoogleAds(array $input): string
    {
        $reportType  = $input['report_type'] ?? 'campaigns';
        $period      = $input['period'] ?? 'LAST_7_DAYS';
        $customerId  = config('services.google.ads.customer_id');
        $devToken    = config('services.google.ads.developer_token');

        $accessToken = $this->googleAdsAccessToken();
        if (!$accessToken) return 'Error: could not get Google Ads access token.';

        $queries = [
            'campaigns'    => "SELECT campaign.name, metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, metrics.ctr, metrics.average_cpc FROM campaign WHERE segments.date DURING {$period} AND campaign.status != 'REMOVED' ORDER BY metrics.clicks DESC",
            'search_terms' => "SELECT search_term_view.search_term, metrics.impressions, metrics.clicks, metrics.conversions, metrics.cost_micros FROM search_term_view WHERE segments.date DURING {$period} ORDER BY metrics.clicks DESC LIMIT 25",
            'keywords'     => "SELECT ad_group_criterion.keyword.text, metrics.impressions, metrics.clicks, metrics.cost_micros, metrics.conversions, metrics.average_cpc FROM keyword_view WHERE segments.date DURING {$period} AND campaign.status != 'REMOVED' ORDER BY metrics.clicks DESC LIMIT 25",
        ];

        $gaql = $queries[$reportType] ?? $queries['campaigns'];

        $managerId = config('services.google.ads.manager_id');

        $response = Http::timeout(30)->withHeaders([
            'Authorization'      => 'Bearer ' . $accessToken,
            'developer-token'    => $devToken,
            'login-customer-id'  => $managerId,
        ])->post("https://googleads.googleapis.com/v17/customers/{$customerId}/googleAds:search", [
            'query' => $gaql,
        ]);

        if (!$response->successful()) {
            Log::error('admin_lara: google ads api error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
            return 'Google Ads API error: ' . $response->status() . ' — ' . substr($response->body(), 0, 300);
        }

        $results = $response->json('results') ?? [];
        if (empty($results)) return "No data found for {$reportType} in {$period}.";

        $lines = match ($reportType) {
            'campaigns' => collect($results)->map(function ($r) {
                $m    = $r['metrics'];
                $cost = isset($m['costMicros']) ? 'R$' . number_format($m['costMicros'] / 1_000_000, 2) : 'R$0';
                $cpc  = isset($m['averageCpc']) ? 'R$' . number_format($m['averageCpc'] / 1_000_000, 2) : '—';
                $ctr  = isset($m['ctr']) ? round($m['ctr'] * 100, 1) . '%' : '—';
                return implode(' | ', [
                    $r['campaign']['name'],
                    ($m['impressions'] ?? 0) . ' impressions',
                    ($m['clicks'] ?? 0) . ' clicks',
                    'CTR ' . $ctr,
                    'CPC ' . $cpc,
                    'cost ' . $cost,
                    round($m['conversions'] ?? 0, 1) . ' conversions',
                ]);
            })->join("\n"),

            'search_terms' => collect($results)->map(function ($r) {
                $m    = $r['metrics'];
                $cost = isset($m['costMicros']) ? 'R$' . number_format($m['costMicros'] / 1_000_000, 2) : 'R$0';
                return implode(' | ', [
                    '"' . $r['searchTermView']['searchTerm'] . '"',
                    ($m['impressions'] ?? 0) . ' impressions',
                    ($m['clicks'] ?? 0) . ' clicks',
                    round($m['conversions'] ?? 0, 1) . ' conversions',
                    'cost ' . $cost,
                ]);
            })->join("\n"),

            'keywords' => collect($results)->map(function ($r) {
                $m    = $r['metrics'];
                $cpc  = isset($m['averageCpc']) ? 'R$' . number_format($m['averageCpc'] / 1_000_000, 2) : '—';
                $cost = isset($m['costMicros']) ? 'R$' . number_format($m['costMicros'] / 1_000_000, 2) : 'R$0';
                return implode(' | ', [
                    '"' . ($r['adGroupCriterion']['keyword']['text'] ?? '?') . '"',
                    ($m['impressions'] ?? 0) . ' impressions',
                    ($m['clicks'] ?? 0) . ' clicks',
                    'CPC ' . $cpc,
                    'cost ' . $cost,
                    round($m['conversions'] ?? 0, 1) . ' conversions',
                ]);
            })->join("\n"),

            default => 'Unknown report type.',
        };

        return "Google Ads — {$reportType} — {$period}:\n\n{$lines}";
    }

    private function toolGenerateCreative(array $input): string
    {
        $prompt = trim($input['prompt'] ?? '');
        if (!$prompt) return 'Error: prompt is required.';

        $response = Http::timeout(60)
            ->withHeaders(['Authorization' => 'Key ' . env('FAL_API_KEY')])
            ->post('https://fal.run/fal-ai/flux/schnell', [
                'prompt'               => $prompt,
                'image_size'           => 'landscape_16_9',
                'num_inference_steps'  => 4,
                'num_images'           => 1,
            ]);

        if (!$response->successful()) {
            Log::error('admin_lara: fal.ai error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
            return 'Error generating image: ' . $response->status();
        }

        $imageUrl = $response->json('images.0.url');
        if (!$imageUrl) return 'Error: no image URL in fal.ai response.';

        return "Creative generated.\nImage URL: {$imageUrl}";
    }

    private function toolCreateMetaAd(array $input): string
    {
        $token     = env('FACEBOOK_ACCESS_TOKEN');
        $accountId = env('FACEBOOK_AD_ACCOUNT_ID'); // already act_xxx
        $pageId    = env('FACEBOOK_PAGE_ID');
        $base      = 'https://graph.facebook.com/v21.0';

        if (!$pageId) return 'Error: FACEBOOK_PAGE_ID not configured.';

        // Create campaign (PAUSED)
        $campRes = Http::timeout(15)->asForm()->post("{$base}/{$accountId}/campaigns", [
            'name'                           => $input['campaign_name'],
            'objective'                      => $input['objective'],
            'status'                         => 'PAUSED',
            'special_ad_categories'          => 'NONE',
            'is_adset_budget_sharing_enabled' => 'false',
            'access_token'                   => $token,
        ]);
        $campaignId = $campRes->json('id');
        if (!$campaignId) return 'Error creating campaign: ' . $campRes->body();

        // Create ad set (PAUSED)
        $budgetCents  = (int) round(($input['daily_budget_brl'] ?? 50) * 100);
        $optimGoal    = ($input['objective'] === 'OUTCOME_LEADS') ? 'LEAD_GENERATION' : 'LINK_CLICKS';
        $adSetRes = Http::timeout(15)->asForm()->post("{$base}/{$accountId}/adsets", [
            'name'              => $input['campaign_name'] . ' — Ad Set',
            'campaign_id'       => $campaignId,
            'daily_budget'      => $budgetCents,
            'billing_event'     => 'IMPRESSIONS',
            'optimization_goal' => $optimGoal,
            'bid_strategy'      => 'LOWEST_COST_WITHOUT_CAP',
            'targeting'         => json_encode([
                'geo_locations' => [
                    'countries'      => ['BR'],
                    'location_types' => ['home', 'recent'],
                ],
                'age_min'              => $input['targeting_age_min'] ?? 25,
                'age_max'              => $input['targeting_age_max'] ?? 55,
                'targeting_automation' => ['advantage_audience' => 0],
            ]),
            'status'            => 'PAUSED',
            'access_token'      => $token,
        ]);
        $adSetId = $adSetRes->json('id');
        if (!$adSetId) {
            Log::error('admin_lara: adset creation failed', ['body' => substr($adSetRes->body(), 0, 500)]);
            return 'Error creating ad set: ' . $adSetRes->body();
        }

        // Create ad creative (use image_url directly, no pre-upload needed)
        $creativeRes = Http::timeout(15)->asForm()->post("{$base}/{$accountId}/adcreatives", [
            'name'              => $input['campaign_name'] . ' — Creative',
            'object_story_spec' => json_encode([
                'page_id'   => $pageId,
                'link_data' => [
                    'link'           => $input['destination_url'],
                    'message'        => $input['body'],
                    'name'           => $input['headline'],
                    'picture'        => $input['image_url'],
                    'call_to_action' => ['type' => 'LEARN_MORE'],
                ],
            ]),
            'access_token'      => $token,
        ]);
        $creativeId = $creativeRes->json('id');
        if (!$creativeId) {
            Log::error('admin_lara: creative creation failed', ['body' => substr($creativeRes->body(), 0, 500)]);
            return 'Error creating creative: ' . $creativeRes->body();
        }

        // Create ad (PAUSED)
        $adRes = Http::timeout(15)->asForm()->post("{$base}/{$accountId}/ads", [
            'name'         => $input['campaign_name'] . ' — Ad',
            'adset_id'     => $adSetId,
            'creative'     => json_encode(['creative_id' => $creativeId]),
            'status'       => 'PAUSED',
            'access_token' => $token,
        ]);
        $adId = $adRes->json('id');
        if (!$adId) return 'Error creating ad: ' . $adRes->body();

        $cleanAccount = str_replace('act_', '', $accountId);
        return "Ad created (PAUSED).\nCampaign ID: {$campaignId}\nAd Set ID: {$adSetId}\nAd ID: {$adId}\nReview at: https://www.facebook.com/adsmanager/manage/campaigns?act={$cleanAccount}";
    }

    private function toolListMetaCampaigns(array $input): string
    {
        $token      = env('FACEBOOK_ACCESS_TOKEN');
        $accountId  = env('FACEBOOK_AD_ACCOUNT_ID');
        $base       = 'https://graph.facebook.com/v21.0';
        $preset     = $input['date_preset'] ?? 'last_7_days';

        $res = Http::timeout(20)->get("{$base}/{$accountId}/campaigns", [
            'fields'      => 'id,name,status,effective_status,daily_budget,insights.date_preset(' . $preset . '){impressions,clicks,spend,reach,ctr,cpm,cpc,frequency}',
            'limit'       => 25,
            'access_token' => $token,
        ]);

        if (!$res->successful()) {
            return 'Meta API error: ' . substr($res->body(), 0, 400);
        }

        $campaigns = $res->json('data') ?? [];
        if (empty($campaigns)) return 'No campaigns found in this ad account.';

        $lines = collect($campaigns)->map(function ($c) {
            $ins   = $c['insights']['data'][0] ?? null;
            $spend = $ins ? 'R$' . number_format((float)$ins['spend'], 2) : 'R$0';
            $impr  = $ins ? number_format((int)$ins['impressions']) : '0';
            $clicks= $ins ? $ins['clicks'] : '0';
            $ctr   = $ins ? round((float)$ins['ctr'] * 100, 2) . '%' : '—';
            $cpm   = $ins ? 'R$' . number_format((float)$ins['cpm'], 2) : '—';
            $cpc   = $ins ? 'R$' . number_format((float)($ins['cpc'] ?? 0), 2) : '—';
            $freq  = $ins ? round((float)($ins['frequency'] ?? 0), 1) : '—';
            $budget= isset($c['daily_budget']) ? 'R$' . number_format($c['daily_budget'] / 100, 0) . '/day' : '—';

            return implode(' | ', [
                "ID:{$c['id']}",
                $c['name'],
                $c['effective_status'],
                $budget,
                "spend:{$spend}",
                "impr:{$impr}",
                "clicks:{$clicks}",
                "CTR:{$ctr}",
                "CPM:{$cpm}",
                "CPC:{$cpc}",
                "freq:{$freq}",
            ]);
        })->join("\n");

        $count = count($campaigns);
        return "Meta campaigns — {$preset} ({$count} found):\n\n{$lines}";
    }

    private function toolGetMetaInsights(array $input): string
    {
        $token     = env('FACEBOOK_ACCESS_TOKEN');
        $accountId = env('FACEBOOK_AD_ACCOUNT_ID');
        $base      = 'https://graph.facebook.com/v21.0';
        $preset    = $input['date_preset'] ?? 'last_30_days';
        $level     = $input['level'] ?? 'campaign';
        $entityId  = $input['entity_id'] ?? $accountId;
        $breakdown = $input['breakdown'] ?? null;

        $params = [
            'fields'      => 'campaign_name,adset_name,impressions,clicks,spend,reach,ctr,cpm,cpc,frequency,actions',
            'date_preset' => $preset,
            'level'       => $level,
            'access_token' => $token,
        ];

        if ($breakdown && in_array($breakdown, ['age', 'gender', 'country', 'age,gender'])) {
            $params['breakdowns'] = $breakdown;
        }

        $res = Http::timeout(30)->get("{$base}/{$entityId}/insights", $params);

        if (!$res->successful()) {
            return 'Meta Insights API error: ' . substr($res->body(), 0, 400);
        }

        $rows = $res->json('data') ?? [];
        if (empty($rows)) return "No insight data for {$preset} at {$level} level.";

        $lines = collect($rows)->map(function ($r) use ($level, $breakdown) {
            $label = match($level) {
                'campaign' => $r['campaign_name'] ?? '?',
                'adset'    => ($r['adset_name'] ?? '?'),
                default    => 'Account',
            };
            if ($breakdown) {
                $label .= ' [' . implode('/', array_filter([$r['age'] ?? null, $r['gender'] ?? null, $r['country'] ?? null])) . ']';
            }

            $spend  = 'R$' . number_format((float)($r['spend'] ?? 0), 2);
            $impr   = number_format((int)($r['impressions'] ?? 0));
            $clicks = $r['clicks'] ?? 0;
            $ctr    = round((float)($r['ctr'] ?? 0) * 100, 2) . '%';
            $cpm    = 'R$' . number_format((float)($r['cpm'] ?? 0), 2);
            $cpc    = 'R$' . number_format((float)($r['cpc'] ?? 0), 2);
            $freq   = round((float)($r['frequency'] ?? 0), 1);

            // Extract link clicks from actions
            $linkClicks = collect($r['actions'] ?? [])->firstWhere('action_type', 'link_click');
            $lcCount    = $linkClicks ? $linkClicks['value'] : '—';

            return "{$label}\n  spend:{$spend} | impr:{$impr} | clicks:{$clicks} | link_clicks:{$lcCount} | CTR:{$ctr} | CPM:{$cpm} | CPC:{$cpc} | freq:{$freq}";
        })->join("\n\n");

        return "Meta Insights — {$level} level — {$preset}" . ($breakdown ? " — by {$breakdown}" : '') . ":\n\n{$lines}";
    }

    private function toolEditMetaCampaign(array $input): string
    {
        $token    = env('FACEBOOK_ACCESS_TOKEN');
        $base     = 'https://graph.facebook.com/v21.0';
        $id       = $input['entity_id'] ?? null;
        $type     = $input['entity_type'] ?? 'campaign'; // campaign | adset

        if (!$id) return 'Error: entity_id is required.';

        $payload = ['access_token' => $token];
        $changes = [];

        if (!empty($input['status']) && in_array($input['status'], ['ACTIVE', 'PAUSED'])) {
            $payload['status'] = $input['status'];
            $changes[] = 'status → ' . $input['status'];
        }

        if (!empty($input['daily_budget_brl'])) {
            $payload['daily_budget'] = (int) round($input['daily_budget_brl'] * 100);
            $changes[] = 'daily_budget → R$' . $input['daily_budget_brl'];
        }

        if ($type === 'adset' && isset($input['enable_advantage_plus'])) {
            // Need to fetch current targeting first, then patch advantage_audience
            $current = Http::timeout(15)->get("{$base}/{$id}", [
                'fields'       => 'targeting',
                'access_token' => $token,
            ]);
            $targeting = $current->json('targeting') ?? [];
            $targeting['targeting_automation'] = ['advantage_audience' => $input['enable_advantage_plus'] ? 1 : 0];
            $payload['targeting'] = json_encode($targeting);
            $changes[] = 'advantage_audience → ' . ($input['enable_advantage_plus'] ? 'ON' : 'OFF');
        }

        if (count($payload) === 1) return 'Error: no changes specified (status, daily_budget_brl, or enable_advantage_plus required).';

        $res = Http::timeout(15)->asForm()->post("{$base}/{$id}", $payload);

        if (!$res->successful() || !$res->json('success')) {
            Log::error('admin_lara: edit_meta_campaign failed', ['id' => $id, 'body' => substr($res->body(), 0, 400)]);
            return 'Meta API error: ' . substr($res->body(), 0, 300);
        }

        return ucfirst($type) . " {$id} updated: " . implode(', ', $changes) . '.';
    }

    private function toolCreateArticle(array $input): string
    {
        $required = ['slug', 'title', 'content', 'slides'];
        foreach ($required as $field) {
            if (empty($input[$field])) return "Error: {$field} is required.";
        }

        if (\App\Models\Article::where('slug', $input['slug'])->exists()) {
            return "Error: article with slug '{$input['slug']}' already exists. Use update_article instead.";
        }

        $article = \App\Models\Article::create([
            'slug'             => $input['slug'],
            'title'            => $input['title'],
            'meta_title'       => $input['meta_title'] ?? $input['title'],
            'meta_description' => $input['meta_description'] ?? null,
            'destination'      => $input['destination'] ?? null,
            'template_type'    => $input['template_type'] ?? null,
            'content'          => $input['content'],
            'cover_image'      => null,
            'status'           => 'draft',
        ]);

        $article->story()->create([
            'slides' => $input['slides'],
            'status' => 'draft',
        ]);

        $editUrl = rtrim(env('APP_URL'), '/') . '/admin/articles/' . $article->id . '/edit';
        return "Draft saved: '{$article->title}' — " . count($input['slides']) . " story slides. Review and publish at {$editUrl}";
    }

    private function toolUpdateArticle(array $input): string
    {
        if (empty($input['slug'])) return 'Error: slug is required.';

        $article = \App\Models\Article::where('slug', $input['slug'])->first();
        if (!$article) return "Error: article '{$input['slug']}' not found.";

        $fields = ['title', 'meta_title', 'meta_description', 'destination', 'template_type', 'content', 'cover_image'];
        $updates = array_filter(array_intersect_key($input, array_flip($fields)), fn($v) => $v !== null);

        if ($updates) $article->update($updates);

        if (!empty($input['slides']) && $article->story) {
            $article->story->update(['slides' => $input['slides']]);
        }

        $changed = array_keys($updates);
        if (!empty($input['slides'])) $changed[] = 'slides';

        return "Article '{$article->slug}' updated: " . implode(', ', $changed) . '.';
    }

    private function toolGetArticle(array $input): string
    {
        if (empty($input['slug'])) return 'Error: slug is required.';

        $article = \App\Models\Article::where('slug', $input['slug'])->with('story')->first();
        if (!$article) return "Error: article '{$input['slug']}' not found.";

        return json_encode([
            'slug'             => $article->slug,
            'title'            => $article->title,
            'status'           => $article->status,
            'destination'      => $article->destination,
            'template_type'    => $article->template_type,
            'meta_description' => $article->meta_description,
            'cover_image'      => $article->cover_image,
            'content'          => $article->content,
            'slides'           => $article->story?->slides ?? [],
        ]);
    }

    private function toolListArticles(array $input): string
    {
        $articles = \App\Models\Article::orderByDesc('published_at')
            ->get(['slug', 'title', 'status', 'destination', 'template_type', 'published_at']);

        if ($articles->isEmpty()) return 'No articles yet.';

        return $articles->map(fn($a) =>
            "[{$a->status}] {$a->slug} — {$a->title}" . ($a->destination ? " ({$a->destination})" : '')
        )->join("\n");
    }

    private function toolResearchDestination(array $input): string
    {
        $destination = trim($input['destination'] ?? '');
        if (!$destination) return 'Error: destination is required.';

        $skipDomains = [
            'booking.com', 'tripadvisor.com', 'airbnb.com', 'youtube.com',
            'instagram.com', 'facebook.com', 'reddit.com', 'twitter.com',
            'x.com', 'maps.google.com', 'expedia.com', 'viator.com',
            'getyourguide.com', 'decolar.com', 'kayak.com',
        ];

        $summary = "# Research: {$destination}\n\n";

        // --- Surface existing research ---
        $existing = ResearchData::where('destination', $destination)
            ->orderByDesc('created_at')
            ->get();

        if ($existing->isNotEmpty()) {
            $summary .= "## Previously collected data ({$existing->count()} records)\n";
            foreach ($existing as $record) {
                $summary .= "- [{$record->source}] collected " . $record->created_at->diffForHumans() . "\n";
            }
            $summary .= "\n";
        }

        // --- YouTube research ---
        try {
            $youtube     = new YouTubeService();
            $ytData      = $youtube->researchDestination($destination);
            $videoCount  = count($ytData['videos'] ?? []);
            $commentCount = collect($ytData['videos'] ?? [])->sum(fn($v) => count($v['top_comments'] ?? []));

            ResearchData::create([
                'destination' => $destination,
                'source'      => 'youtube',
                'raw_data'    => $this->sanitizeUtf8($ytData),
            ]);

            $summary .= "## YouTube ({$videoCount} videos, {$commentCount} comments)\n";
            foreach (($ytData['videos'] ?? []) as $i => $v) {
                $summary .= ($i + 1) . ". \"{$v['title']}\" ({$v['view_count']} views)\n";
                if ($v['description']) {
                    $summary .= "   " . substr($v['description'], 0, 200) . "\n";
                }
                if (!empty($v['tags'])) {
                    $summary .= "   Tags: " . implode(', ', array_slice($v['tags'], 0, 8)) . "\n";
                }
                foreach (array_slice($v['top_comments'] ?? [], 0, 3) as $c) {
                    $summary .= "   💬 " . substr($c['text'], 0, 150) . "\n";
                }
                $summary .= "\n";
            }
        } catch (\Throwable $e) {
            Log::error('research_destination: youtube failed', ['destination' => $destination, 'error' => $e->getMessage()]);
            $summary .= "## YouTube\nFailed: {$e->getMessage()}\n\n";
        }

        // --- YouTube Transcripts (progressive — fetch top 3 uncached, surface all cached) ---
        try {
            $allVideoIds = collect($ytData['videos'] ?? [])->pluck('id')->filter()->values()->all();

            $allTranscripts = ResearchData::where('destination', $destination)
                ->where('source', 'youtube_transcript')
                ->get();

            $cachedVideoIds = $allTranscripts
                ->map(fn($t) => $t->raw_data['video_id'] ?? null)
                ->filter()->values()->all();

            $toFetch = array_slice(array_values(array_diff($allVideoIds, $cachedVideoIds)), 0, 3);

            if (!empty($toFetch)) {
                $apify = app(ApifyService::class);
                foreach ($toFetch as $videoId) {
                    $transcript = $apify->fetchYouTubeTranscript($videoId);
                    if ($transcript) {
                        $title = collect($ytData['videos'] ?? [])->firstWhere('id', $videoId)['title'] ?? $videoId;
                        $record = ResearchData::create([
                            'destination' => $destination,
                            'source'      => 'youtube_transcript',
                            'raw_data'    => $this->sanitizeUtf8(['video_id' => $videoId, 'title' => $title, 'transcript' => $transcript]),
                        ]);
                        $allTranscripts->push($record);
                    }
                }
            }

            $matching = $allTranscripts->filter(fn($t) => in_array($t->raw_data['video_id'] ?? '', $allVideoIds));
            if ($matching->isNotEmpty()) {
                $summary .= "## YouTube Transcripts (" . $matching->count() . " videos)\n";
                foreach ($matching as $t) {
                    $summary .= "### \"{$t->raw_data['title']}\"\n" . substr($t->raw_data['transcript'], 0, 2000) . "\n\n";
                }
            }
        } catch (\Throwable $e) {
            Log::error('research_destination: transcripts failed', ['destination' => $destination, 'error' => $e->getMessage()]);
        }

        // --- Apify SERP research ---
        try {
            $apifyToken = config('services.apify.token');
            $serpQuery  = "{$destination} o que fazer passeios";

            $apifyRes = Http::timeout(30)->post(
                "https://api.apify.com/v2/acts/apify~google-search-scraper/run-sync-get-dataset-items?token={$apifyToken}",
                [
                    'queries'        => $serpQuery,
                    'maxPagesPerQuery' => 1,
                    'resultsPerPage' => 10,
                    'languageCode'   => 'pt-BR',
                    'countryCode'    => 'br',
                ]
            );

            if ($apifyRes->ok() || $apifyRes->status() === 201) {
                $serpItems = $apifyRes->json() ?? [];
                $serpItem  = $serpItems[0] ?? [];


                $organic = collect($serpItem['organicResults'] ?? [])->filter(function ($r) use ($skipDomains) {
                    $url = strtolower($r['url'] ?? '');
                    foreach ($skipDomains as $domain) {
                        if (str_contains($url, $domain)) return false;
                    }
                    return true;
                })->values()->all();

                $paa     = $serpItem['peopleAlsoAsk'] ?? [];
                $related = $serpItem['relatedQueries'] ?? [];

                ResearchData::create([
                    'destination' => $destination,
                    'source'      => 'apify_serp',
                    'raw_data'    => $this->sanitizeUtf8([
                        'query'   => $serpQuery,
                        'organic' => $organic,
                        'paa'     => $paa,
                        'related' => $related,
                    ]),
                ]);

                $summary .= "## SERP — \"{$serpQuery}\"\n";
                $summary .= "### Organic results (" . count($organic) . " editorial)\n";
                foreach ($organic as $i => $r) {
                    $summary .= ($i + 1) . ". [{$r['title']}]({$r['url']})\n";
                    if (!empty($r['description'])) {
                        $summary .= "   " . substr($r['description'], 0, 150) . "\n";
                    }
                }

                if (!empty($paa)) {
                    $summary .= "\n### People Also Ask\n";
                    foreach (array_slice($paa, 0, 8) as $q) {
                        $question = $q['question'] ?? ($q['title'] ?? '');
                        if ($question) $summary .= "- {$question}\n";
                    }
                }

                if (!empty($related)) {
                    $summary .= "\n### Related searches\n";
                    foreach (array_slice($related, 0, 8) as $r) {
                        $term = $r['title'] ?? ($r['query'] ?? '');
                        if ($term) $summary .= "- {$term}\n";
                    }
                }

                // Pre-fetch article content (top 3 uncached URLs)
                $allCachedArticles = ResearchData::where('destination', $destination)
                    ->where('source', 'serp_article')
                    ->get();

                $cachedUrls = $allCachedArticles
                    ->map(fn($a) => $a->raw_data['url'] ?? null)
                    ->filter()->values()->all();

                $toFetchArticles = collect($organic)
                    ->filter(fn($r) => !in_array($r['url'] ?? '', $cachedUrls))
                    ->take(3)
                    ->values()->all();

                foreach ($toFetchArticles as $r) {
                    $text = $this->fetchArticleText($r['url'] ?? '');
                    if ($text) {
                        $record = ResearchData::create([
                            'destination' => $destination,
                            'source'      => 'serp_article',
                            'raw_data'    => $this->sanitizeUtf8(['url' => $r['url'], 'title' => $r['title'] ?? '', 'content' => $text]),
                        ]);
                        $allCachedArticles->push($record);
                    }
                }

                $allCachedArticles = $allCachedArticles->take(6);
                if ($allCachedArticles->isNotEmpty()) {
                    $summary .= "\n### Competitor article content (" . $allCachedArticles->count() . " articles)\n";
                    foreach ($allCachedArticles as $a) {
                        $summary .= "#### [{$a->raw_data['title']}]({$a->raw_data['url']})\n" . substr($a->raw_data['content'] ?? '', 0, 2000) . "\n\n";
                    }
                }
            } else {
                Log::error('research_destination: apify serp 400', [
                    'status' => $apifyRes->status(),
                    'body'   => substr($apifyRes->body(), 0, 500),
                ]);
                $summary .= "## SERP\nApify returned status {$apifyRes->status()}.\n\n";
            }
        } catch (\Throwable $e) {
            Log::error('research_destination: apify serp failed', ['destination' => $destination, 'error' => $e->getMessage()]);
            $summary .= "## SERP\nFailed: {$e->getMessage()}\n\n";
        }

        return $summary;
    }

    private function toolQueryLeads(array $input): string
    {
        $destination = $input['destination'] ?? null;
        $type        = $input['type'] ?? null; // affiliate | lead | all
        $limit       = $input['limit'] ?? 50;

        $query = DB::table('outbound_clicks')
            ->join('activities', 'activities.id', '=', 'outbound_clicks.activity_id')
            ->select(
                'outbound_clicks.type',
                'outbound_clicks.destination',
                'outbound_clicks.category',
                'activities.name as activity_name',
                DB::raw('COUNT(*) as click_count'),
                DB::raw('MAX(outbound_clicks.created_at) as last_click')
            )
            ->groupBy('outbound_clicks.activity_id', 'outbound_clicks.type', 'outbound_clicks.destination', 'outbound_clicks.category', 'activities.name');

        if ($destination) {
            $query->where('outbound_clicks.destination', 'like', '%' . $destination . '%');
        }
        if ($type && in_array($type, ['affiliate', 'lead'])) {
            $query->where('outbound_clicks.type', $type);
        }

        $rows = $query->orderByDesc('click_count')->limit($limit)->get();

        if ($rows->isEmpty()) return 'No outbound clicks recorded yet.';

        $total = $rows->sum('click_count');
        $lines = $rows->map(fn($r) =>
            "[{$r->type}] {$r->activity_name} | {$r->destination} | {$r->category} | {$r->click_count} clicks | last: " . date('Y-m-d', strtotime($r->last_click))
        )->join("\n");

        return "Outbound clicks ({$total} total):\n\n{$lines}";
    }

    private function toolSuggestArticles(array $input): string
    {
        $destination = trim($input['destination'] ?? '');
        if (!$destination) return 'Error: destination is required.';

        $apifyToken = config('services.apify.token');
        $summary    = "# Article Ideas for {$destination}\n\n";

        // 1. Activities on Envago
        $activities = DB::table('activities')
            ->join('providers', 'providers.id', '=', 'activities.provider_id')
            ->leftJoin('categories', 'categories.id', '=', 'activities.category_id')
            ->select('activities.name', 'activities.price', 'categories.slug as category', 'activities.booking_method')
            ->where('activities.is_active', true)
            ->where('activities.city', 'like', '%' . $destination . '%')
            ->get();

        $summary .= "## Envago trips in {$destination} (" . $activities->count() . ")\n";
        foreach ($activities as $a) {
            $summary .= "- [{$a->booking_method}] {$a->name} ({$a->category}) R$" . number_format($a->price, 0) . "\n";
        }

        // 2. Bookings — what converts
        $bookings = DB::table('bookings')
            ->join('schedules', 'schedules.id', '=', 'bookings.schedule_id')
            ->join('activities', 'activities.id', '=', 'schedules.activity_id')
            ->leftJoin('categories', 'categories.id', '=', 'activities.category_id')
            ->select('activities.name', 'categories.slug as category', DB::raw('COUNT(*) as booking_count'))
            ->where('bookings.status', 'confirmed')
            ->where('activities.city', 'like', '%' . $destination . '%')
            ->groupBy('activities.id', 'activities.name', 'categories.slug')
            ->orderByDesc('booking_count')
            ->get();

        $summary .= "\n## What's booking well\n";
        $summary .= $bookings->isEmpty() ? "No confirmed bookings yet.\n" :
            $bookings->map(fn($b) => "- {$b->name} ({$b->category}): {$b->booking_count} bookings")->join("\n") . "\n";

        // 3. Scope leads — supply gaps
        $scopeLeads = DB::table('scope_leads')
            ->where('location', 'like', '%' . $destination . '%')
            ->select('offering', DB::raw('COUNT(*) as count'))
            ->groupBy('offering')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $summary .= "\n## Scope leads (supply not yet on Envago)\n";
        $summary .= $scopeLeads->isEmpty() ? "None.\n" :
            $scopeLeads->map(fn($l) => "- {$l->offering}: {$l->count} operators")->join("\n") . "\n";

        // 4. Outbound clicks — tourist demand gaps
        $clicks = DB::table('outbound_clicks')
            ->select('category', 'type', DB::raw('COUNT(*) as count'))
            ->where('destination', 'like', '%' . $destination . '%')
            ->groupBy('category', 'type')
            ->orderByDesc('count')
            ->get();

        $summary .= "\n## Tourist demand signals (outbound clicks)\n";
        $summary .= $clicks->isEmpty() ? "No clicks recorded yet.\n" :
            $clicks->map(fn($c) => "- [{$c->type}] {$c->category}: {$c->count} clicks")->join("\n") . "\n";

        // 5. SERP — what ranks + PAA
        try {
            $serpQuery = "o que fazer em {$destination}";
            $apifyRes  = Http::timeout(30)->post(
                "https://api.apify.com/v2/acts/apify~google-search-scraper/run-sync-get-dataset-items?token={$apifyToken}",
                ['queries' => $serpQuery, 'maxPagesPerQuery' => 1, 'resultsPerPage' => 5, 'languageCode' => 'pt-BR', 'countryCode' => 'br']
            );

            if ($apifyRes->ok() || $apifyRes->status() === 201) {
                $serpItem = ($apifyRes->json() ?? [])[0] ?? [];
                $paa      = array_slice($serpItem['peopleAlsoAsk'] ?? [], 0, 6);
                $organic  = array_slice($serpItem['organicResults'] ?? [], 0, 5);

                $summary .= "\n## SERP — \"{$serpQuery}\"\n";
                $summary .= "Top results: " . collect($organic)->pluck('title')->join(', ') . "\n";

                if (!empty($paa)) {
                    $summary .= "PAA: " . collect($paa)->map(fn($q) => $q['question'] ?? $q['title'] ?? '')->filter()->join(' | ') . "\n";
                }
            }
        } catch (\Throwable $e) {
            $summary .= "\n## SERP\nFailed: {$e->getMessage()}\n";
        }

        $summary .= "\n---\nBased on the above, suggest 3-5 ranked article ideas with template type and reasoning. Prioritize: (1) activities that book well need great guides, (2) supply gaps with tourist demand = SEO now, (3) PAA questions with no good answer ranking.";

        return $summary;
    }

    private function fetchArticleText(string $url): ?string
    {
        if (!$url) return null;
        try {
            $response = Http::timeout(15)->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Envago/1.0)'])->get($url);
            if (!$response->successful()) return null;

            $html = $response->body();
            $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
            $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
            $text = strip_tags($html);
            $text = preg_replace('/\s+/', ' ', $text);

            return substr(trim($text), 0, 3000);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function sanitizeUtf8(mixed $data): mixed
    {
        if (is_string($data)) {
            return mb_scrub($data, 'UTF-8');
        }
        if (is_array($data)) {
            return array_map([$this, 'sanitizeUtf8'], $data);
        }
        return $data;
    }
}
