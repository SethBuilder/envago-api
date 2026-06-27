<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Media;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ActivityController extends Controller
{
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
            'photos.min'                => 'Adicione pelo menos uma foto para continuar.',
            'photos.*.image'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.mimes'            => 'Arquivo inválido. Use apenas fotos (JPG, PNG ou WebP).',
            'photos.*.max'              => 'Uma das fotos passou de 5 MB. Reduza o tamanho e tente novamente.',
        ]);

        $provider = auth()->user()->provider;

        $slug = Str::slug($request->name);
        $base = $slug;
        $i    = 2;
        while (Activity::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        $activity = Activity::create([
            'provider_id'      => $provider->id,
            'category_id'      => $request->category_id,
            'name'             => $request->name,
            'slug'             => $slug,
            'description'      => $request->description,
            'price'            => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'capacity'         => $request->capacity,
            'city'             => $request->city,
            'state'            => strtoupper($request->state),
            'meeting_point'    => $request->meeting_point,
            'meeting_point_lat'=> $request->meeting_point_lat ?: null,
            'meeting_point_lng'=> $request->meeting_point_lng ?: null,
            'is_active'        => true,
        ]);

        // Schedules from groups
        $today     = Carbon::today();
        $schedules = [];

        foreach ($request->groups as $group) {
            $days  = array_map('intval', $group['days']);
            $times = $group['times'];

            for ($d = 0; $d < 60; $d++) {
                $date = $today->copy()->addDays($d);
                if (!in_array($date->dayOfWeek, $days)) continue;
                foreach ($times as $time) {
                    $schedules[] = [
                        'activity_id'  => $activity->id,
                        'date'         => $date->toDateString(),
                        'time'         => $time . ':00',
                        'seats_total'  => $activity->capacity,
                        'seats_booked' => 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
        }

        if ($schedules) {
            Schedule::insert($schedules);
        }

        // Photos — store path only; URL is resolved at runtime by Media::getUrlAttribute()
        if ($request->hasFile('photos')) {
            $disk  = config('filesystems.default') === 's3' ? 's3' : 'public';
            $order = 0;
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('activities/' . $activity->id, ['disk' => $disk, 'visibility' => 'public']);
                Media::create([
                    'activity_id' => $activity->id,
                    'url'         => $path,
                    'type'        => $order === 0 ? 'cover' : 'gallery',
                    'order'       => $order++,
                ]);
            }
        }

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio "' . $activity->name . '" criado com ' . count($schedules) . ' horarios.',
        ]);
    }

    public function edit(Activity $activity)
    {
        $provider = auth()->user()->provider;
        abort_if($activity->provider_id !== $provider->id, 403);

        $categories = Category::orderBy('name')->get();
        $activity->load('media');

        // Build schedule groups: group by time → collect distinct days of week
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

        $activity->update([
            'category_id'      => $request->category_id,
            'name'             => $request->name,
            'description'      => $request->description,
            'price'            => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'capacity'         => $request->capacity,
            'city'             => $request->city,
            'state'            => strtoupper($request->state),
            'meeting_point'    => $request->meeting_point,
            'meeting_point_lat'=> $request->meeting_point_lat ?: null,
            'meeting_point_lng'=> $request->meeting_point_lng ?: null,
        ]);

        // Delete selected photos
        if ($request->filled('delete_media')) {
            $toDelete = Media::where('activity_id', $activity->id)
                ->whereIn('id', $request->delete_media)
                ->get();
            foreach ($toDelete as $m) {
                Storage::disk('public')->delete($m->url);
                $m->delete();
            }
        }

        // Upload new photos
        if ($request->hasFile('photos')) {
            $disk      = config('filesystems.default') === 's3' ? 's3' : 'public';
            $lastOrder = $activity->media()->max('order') ?? -1;
            $order     = $lastOrder + 1;
            $hascover  = $activity->media()->where('type', 'cover')->exists();
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('activities/' . $activity->id, ['disk' => $disk, 'visibility' => 'public']);
                Media::create([
                    'activity_id' => $activity->id,
                    'url'         => $path,
                    'type'        => (!$hascover && $order === ($lastOrder + 1)) ? 'cover' : 'gallery',
                    'order'       => $order++,
                ]);
                $hascover = true;
            }
        }

        // Regenerate schedules (keep those with bookings)
        $today = Carbon::today();
        $activity->schedules()
            ->where('date', '>=', $today)
            ->where('seats_booked', 0)
            ->delete();

        $schedules = [];
        foreach ($request->groups as $group) {
            $days  = array_map('intval', $group['days']);
            $times = $group['times'];
            for ($d = 0; $d < 60; $d++) {
                $date = $today->copy()->addDays($d);
                if (!in_array($date->dayOfWeek, $days)) continue;
                foreach ($times as $time) {
                    $schedules[] = [
                        'activity_id'  => $activity->id,
                        'date'         => $date->toDateString(),
                        'time'         => $time . ':00',
                        'seats_total'  => $activity->capacity,
                        'seats_booked' => 0,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ];
                }
            }
        }

        if ($schedules) {
            foreach ($schedules as $s) {
                Schedule::firstOrCreate(
                    ['activity_id' => $s['activity_id'], 'date' => $s['date'], 'time' => $s['time']],
                    ['seats_total' => $s['seats_total'], 'seats_booked' => 0]
                );
            }
        }

        return response()->json([
            'redirect' => route('provider.activities'),
            'message'  => 'Passeio atualizado com sucesso.',
        ]);
    }
}
