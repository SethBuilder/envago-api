<?php

namespace App\Http\Controllers;

use App\Services\LaraService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function verify(Request $request): Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if ($mode === 'subscribe' && $token === env('META_WEBHOOK_VERIFY_TOKEN')) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function webhook(Request $request, LaraService $lara): Response
    {
        $from = $request->input('From', '');
        $body = trim($request->input('Body', ''));

        $phone = str_replace('whatsapp:', '', $from);

        if (!$phone || !$body) {
            return $this->twiml('');
        }

        $digits = preg_replace('/\D/', '', $phone);

        // Look up provider by last 10 digits (tolerant of different storage formats)
        $provider = DB::table('providers')
            ->join('users', 'users.id', '=', 'providers.user_id')
            ->select('providers.*', 'users.email')
            ->whereRaw(
                "REPLACE(REPLACE(REPLACE(whatsapp_number, '+', ''), '-', ''), ' ', '') LIKE ?",
                ['%' . substr($digits, -10) . '%']
            )
            ->first();

        $sessionId    = 'wa_' . $digits;
        $context      = $provider ? 'provider' : 'parceiros';
        $providerInfo = null;

        if ($provider) {
            $providerInfo = implode("\n", [
                "Business: {$provider->business_name}",
                "Status: {$provider->status}",
                "WhatsApp: {$provider->whatsapp_number}",
                "Email: {$provider->email}",
            ]);
        }

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at')
            ->get();

        set_time_limit(120);

        try {
            $reply = $lara->respond(
                $history->all(),
                $body,
                $context,
                $providerInfo
            );
        } catch (\Throwable $e) {
            Log::error('whatsapp.lara_error', ['phone' => $phone, 'error' => $e->getMessage()]);
            return $this->twiml('Algo deu errado. Tente novamente em instantes.');
        }

        DB::table('lara_conversations')->insert([
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'user',      'message' => $body,  'created_at' => now(), 'updated_at' => now()],
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'assistant', 'message' => $reply, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $this->twiml($reply);
    }

    private function twiml(string $message): Response
    {
        $escaped = htmlspecialchars($message, ENT_XML1, 'UTF-8');
        $xml     = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><Response><Message>{$escaped}</Message></Response>";

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
