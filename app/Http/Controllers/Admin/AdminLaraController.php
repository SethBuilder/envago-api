<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        set_time_limit(120);

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
                default                => "Unknown tool: {$name}",
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
}
