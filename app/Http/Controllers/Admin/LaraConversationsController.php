<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LaraConversationsController extends Controller
{
    public function index()
    {
        $sessions = DB::table('lara_conversations as lc')
            ->select([
                'lc.session_id',
                DB::raw('MAX(lc.created_at) as last_message_at'),
                DB::raw('COUNT(*) as message_count'),
                DB::raw('(SELECT lc2.message FROM lara_conversations lc2 WHERE lc2.session_id = lc.session_id AND lc2.role = "user" ORDER BY lc2.created_at ASC LIMIT 1) as first_user_message'),
            ])
            ->where('lc.session_id', 'not like', 'admin_%')
            ->groupBy('lc.session_id')
            ->orderByDesc(DB::raw('MAX(lc.created_at)'))
            ->paginate(30);

        return view('admin.lara-conversations', compact('sessions'));
    }

    public function show(string $sessionId)
    {
        abort_if(str_starts_with($sessionId, 'admin_'), 404);

        $messages = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        abort_if($messages->isEmpty(), 404);

        return view('admin.lara-conversation', compact('sessionId', 'messages'));
    }
}
