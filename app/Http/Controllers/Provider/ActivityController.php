<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ActivityController extends Controller
{
    public function __construct(private ActivityService $activityService) {}

    public function create()
    {
        $provider   = auth()->user()->provider;
        $categories = Category::orderBy('name')->get();
        return view('provider.activity-create', compact('provider', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:120',
            'category_id'       => 'nullable|exists:categories,id',
            'description'       => 'nullable|string|max:2000',
            'highlights'        => 'nullable|string',
            'price'             => 'required|numeric|min:1|max:99999',
            'duration_minutes'  => 'required|integer|min:15|max:1440',
            'capacity'          => 'required|integer|min:1|max:999',
            'city'              => 'required|string|max:80',
            'state'             => 'required|string|size:2',
            'groups'            => 'required|array|min:1',
            'groups.*.days'     => 'required|array|min:1',
            'groups.*.days.*'   => 'integer|between:0,6',
            'groups.*.times'    => 'required|array|min:1',
            'groups.*.times.*'  => 'date_format:H:i',
            'pricing_type'      => 'nullable|in:per_person,per_unit',
            'pricing_unit'      => 'nullable|string|max:30|required_if:pricing_type,per_unit',
            'unit_capacity'     => 'nullable|integer|min:1|max:99',
            'photos'            => 'required',
            'photos.*'          => 'image|mimes:jpeg,png,jpg,webp|max:20480',
        ], [
            'name.required'             => 'O nome do passeio é obrigatório.',
            'name.max'                  => 'O nome não pode ter mais de 120 caracteres.',
            'price.required'            => 'O preço é obrigatório.',
            'price.numeric'             => 'O preço deve ser um número.',
            'price.min'                 => 'O preço mínimo é R$ 1.',
            'duration_minutes.required' => 'A duração é obrigatória.',
            'duration_minutes.min'      => 'A duração mínima é 15 minutos.',
            'capacity.required'         => 'O número de vagas é obrigatório.',
            'capacity.min'              => 'Mínimo de 1 vaga.',
            'city.required'             => 'A cidade é obrigatória.',
            'state.required'            => 'O estado é obrigatório.',
            'state.size'                => 'Use a sigla do estado com 2 letras (ex: CE).',
            'groups.required'           => 'Adicione pelo menos um horário.',
            'groups.min'                => 'Adicione pelo menos um horário.',
            'groups.*.days.required'    => 'Selecione pelo menos um dia em cada grupo.',
            'groups.*.times.required'   => 'Adicione pelo menos um horário em cada grupo.',
            'groups.*.times.*.date_format' => 'Horário inválido.',
            'photos.required'           => 'Adicione pelo menos uma foto para continuar.',
            'photos.*.image'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.mimes'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.max'              => 'Uma das fotos passou de 20 MB. Reduza o tamanho e tente novamente.',
        ]);

        $provider = auth()->user()->provider;
        $photos   = $request->hasFile('photos') ? $request->file('photos') : [];

        $data             = $request->all();
        $data['highlights'] = $this->decodeHighlights($request->input('highlights'));

        try {
            $activity = $this->activityService->createActivity($provider, $data, $photos);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('activity.create failed', ['error' => $e->getMessage()]);
            return response()->json(['errors' => ['photos' => ['Erro ao salvar fotos. Tente novamente.']]], 422);
        }

        $scheduleCount = $activity->schedules()->count();

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio "' . $activity->name . '" criado com ' . $scheduleCount . ' horarios.',
        ]);
    }

    public function edit(Activity $activity)
    {
        $provider = auth()->user()->provider;
        abort_if($activity->provider_id !== $provider->id, 403);

        $categories = Category::orderBy('name')->get();
        $activity->load('media');

        $scheduleGroups = $activity->schedules()
            ->where('date', '>=', Carbon::today())
            ->get(['date', 'time'])
            ->groupBy('time')
            ->map(fn($slots) => $slots->map(fn($s) => Carbon::parse($s->date)->dayOfWeek)->unique()->sort()->values())
            ->map(fn($days, $time) => ['time' => substr($time, 0, 5), 'days' => $days->toArray()])
            ->values();

        if ($scheduleGroups->isEmpty()) {
            $scheduleGroups = collect([['time' => '08:00', 'days' => [1,2,3,4,5]]]);
        }

        return view('provider.activity-edit', compact('activity', 'categories', 'provider', 'scheduleGroups'));
    }

    public function update(Request $request, Activity $activity)
    {
        $provider = auth()->user()->provider;
        abort_if($activity->provider_id !== $provider->id, 403);

        $request->validate([
            'name'              => 'required|string|max:120',
            'category_id'       => 'nullable|exists:categories,id',
            'description'       => 'nullable|string|max:2000',
            'highlights'        => 'nullable|string',
            'price'             => 'required|numeric|min:1|max:99999',
            'duration_minutes'  => 'required|integer|min:15|max:1440',
            'capacity'          => 'required|integer|min:1|max:999',
            'city'              => 'required|string|max:80',
            'state'             => 'required|string|size:2',
            'groups'            => 'required|array|min:1',
            'groups.*.days'     => 'required|array|min:1',
            'groups.*.days.*'   => 'integer|between:0,6',
            'groups.*.times'    => 'required|array|min:1',
            'groups.*.times.*'  => 'date_format:H:i',
            'pricing_type'      => 'nullable|in:per_person,per_unit',
            'pricing_unit'      => 'nullable|string|max:30|required_if:pricing_type,per_unit',
            'photos.*'          => 'image|mimes:jpeg,png,jpg,webp|max:20480',
            'videos.*'          => 'file|mimes:mp4,mov,webm|max:102400',
            'instagram_urls'    => 'nullable|array',
            'instagram_urls.*'  => 'nullable|url',
            'delete_media'      => 'nullable|array',
            'delete_media.*'    => 'integer',
        ], [
            'name.required'             => 'O nome do passeio é obrigatório.',
            'price.required'            => 'O preço é obrigatório.',
            'price.min'                 => 'O preço mínimo é R$ 1.',
            'duration_minutes.required' => 'A duração é obrigatória.',
            'capacity.required'         => 'O número de vagas é obrigatório.',
            'city.required'             => 'A cidade é obrigatória.',
            'state.required'            => 'O estado é obrigatório.',
            'state.size'                => 'Use a sigla do estado com 2 letras (ex: CE).',
            'groups.required'           => 'Adicione pelo menos um horário.',
            'groups.*.days.required'    => 'Selecione pelo menos um dia em cada grupo.',
            'groups.*.times.required'   => 'Adicione pelo menos um horário em cada grupo.',
            'photos.*.image'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.mimes'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.max'              => 'Uma das fotos passou de 20 MB. Reduza o tamanho e tente novamente.',
            'videos.*.mimes'            => 'Arquivo inválido. Use apenas vídeos (MP4, MOV ou WebM).',
            'videos.*.max'              => 'Um dos vídeos passou de 100 MB.',
        ]);

        set_time_limit(180);

        $newPhotos      = $request->hasFile('photos') ? $request->file('photos') : [];
        $deleteMediaIds = $request->input('delete_media', []);
        $newVideos      = $request->hasFile('videos') ? $request->file('videos') : [];
        $updateData     = $request->all();
        $updateData['highlights'] = $this->decodeHighlights($request->input('highlights'));

        $igUrls = array_filter((array) $request->input('instagram_urls', []));
        if (!empty($igUrls)) {
            $apify     = app(\App\Services\ApifyService::class);
            $sessionId = 'provider-' . $provider->id;
            foreach ($igUrls as $igUrl) {
                $scraped = $apify->scrapeInstagramVideo(trim($igUrl));
                if ($scraped) {
                    $filename = $apify->downloadToTemp($scraped['video_url'], $sessionId, '', $scraped['audio_url'] ?? null);
                    if ($filename) {
                        if ($scraped['instagram_handle'] ?? null) {
                            Storage::disk('local')->put("temp/{$sessionId}/{$filename}.handle", $scraped['instagram_handle']);
                        }
                        $newVideos[] = [
                            'path'             => Storage::disk('local')->path("temp/{$sessionId}/{$filename}"),
                            'poster_url'       => $scraped['poster_url'] ?? null,
                            'instagram_handle' => $scraped['instagram_handle'] ?? null,
                        ];
                    }
                }
            }
        }

        try {
            $this->activityService->updateActivity($activity, $updateData, $newPhotos, $deleteMediaIds, $newVideos);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('activity.update failed', ['error' => $e->getMessage()]);
            return response()->json(['errors' => ['photos' => ['Erro ao salvar mídia. Tente novamente.']]], 422);
        }

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio atualizado com sucesso.',
        ]);
    }

    public function aiEnhanceDescription(Request $request)
    {
        $description = trim($request->input('description', ''));
        if (empty($description)) {
            return response()->json(['error' => 'Descrição vazia.'], 422);
        }

        $res = Http::timeout(60)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 600,
            'messages'   => [[
                'role'    => 'user',
                'content' => "Você é um redator especializado em turismo no Brasil. Reescreva esta descrição de passeio em Markdown estruturado para ser mais atraente e profissional para turistas.\n\nRegras de formatação:\n- Use ## para seções (ex: ## O que está incluído, ## O que esperar, ## Informações importantes)\n- Use **negrito** para destacar pontos-chave\n- Use listas com - para itens incluídos/excluídos\n- Parágrafo de abertura convidativo sem título\n- PT-BR, tom amigável e convidativo\n- Máx 400 palavras\n- Retorne apenas o Markdown, sem comentários ou aspas\n\nDescrição original:\n{$description}",
            ]],
        ]);

        if (!$res->ok()) {
            Log::error('ai.enhance-description failed', ['status' => $res->status()]);
            return response()->json(['error' => 'Erro ao chamar IA. Tente novamente.'], 500);
        }

        return response()->json(['description' => trim($res->json('content.0.text') ?? '')]);
    }

    public function aiExtractHighlights(Request $request)
    {
        $description = trim($request->input('description', ''));
        if (empty($description)) {
            return response()->json(['error' => 'Descrição vazia.'], 422);
        }

        $res = Http::timeout(60)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 300,
            'messages'   => [[
                'role'    => 'user',
                'content' => "Extraia até 6 destaques desta descrição de passeio. Retorne APENAS um JSON array de strings, sem markdown, sem explicação. PT-BR, máx 60 chars por item. Exemplo: [\"Vista panorâmica das dunas\",\"Guia local experiente\"]\n\nDescrição:\n{$description}",
            ]],
        ]);

        if (!$res->ok()) {
            Log::error('ai.extract-highlights failed', ['status' => $res->status()]);
            return response()->json(['error' => 'Erro ao chamar IA. Tente novamente.'], 500);
        }

        $text = trim($res->json('content.0.text') ?? '');
        $json = json_decode($text, true);
        if (!is_array($json)) {
            preg_match('/\[.*\]/s', $text, $m);
            $json = isset($m[0]) ? json_decode($m[0], true) : [];
        }

        return response()->json(['highlights' => $json ?? []]);
    }

    private function decodeHighlights(?string $raw): ?array
    {
        if (empty($raw)) return null;
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? array_values(array_filter($decoded)) : null;
    }
}
