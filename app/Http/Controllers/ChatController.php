<?php

namespace App\Http\Controllers;

use App\Services\LaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function show()
    {
        $sessionId = session()->getId();

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get(['role', 'message']);

        return view('lara', ['history' => $history]);
    }

    public function message(Request $request, LaraService $lara)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $sessionId   = session()->getId();
        $userMessage = $request->input('message');
        $isInit      = $userMessage === '__init__';

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        $triggerMessage = $isInit ? 'Olá' : $userMessage;
        $reply = $lara->respond($history->all(), $triggerMessage);

        $rows = [];
        if (! $isInit) {
            $rows[] = ['session_id' => $sessionId, 'role' => 'user', 'message' => $userMessage, 'created_at' => now(), 'updated_at' => now()];
        }
        $rows[] = ['session_id' => $sessionId, 'role' => 'assistant', 'message' => $reply, 'created_at' => now(), 'updated_at' => now()];
        DB::table('lara_conversations')->insert($rows);

        return response()->json(['reply' => $reply]);
    }

    public function reset()
    {
        DB::table('lara_conversations')->where('session_id', session()->getId())->delete();
        return response()->json(['ok' => true]);
    }
}
