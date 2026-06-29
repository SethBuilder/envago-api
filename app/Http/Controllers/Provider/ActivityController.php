<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            'photos'            => 'required',
            'photos.*'          => 'image|mimes:jpeg,png,jpg,webp|max:5120',
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
            'photos.*.max'              => 'Uma das fotos passou de 5 MB. Reduza o tamanho e tente novamente.',
        ]);

        $provider = auth()->user()->provider;
        $photos   = $request->hasFile('photos') ? $request->file('photos') : [];

        $activity = $this->activityService->createActivity($provider, $request->all(), $photos);

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
            'photos.*'          => 'image|mimes:jpeg,png,jpg,webp|max:5120',
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
            'photos.*.max'              => 'Uma das fotos passou de 5 MB. Reduza o tamanho e tente novamente.',
        ]);

        $newPhotos      = $request->hasFile('photos') ? $request->file('photos') : [];
        $deleteMediaIds = $request->input('delete_media', []);

        $this->activityService->updateActivity($activity, $request->all(), $newPhotos, $deleteMediaIds);

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio atualizado com sucesso.',
        ]);
    }
}
