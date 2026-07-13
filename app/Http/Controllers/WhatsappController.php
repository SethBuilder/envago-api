<?php

namespace App\Http\Controllers;

use App\Services\ApifyService;
use App\Services\LaraService;
use App\Services\UrlFetcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    public function webhook(Request $request, LaraService $lara, UrlFetcher $fetcher): JsonResponse
    {
        $message = $request->input('entry.0.changes.0.value.messages.0');

        $type = $message['type'] ?? '';

        if (!$message) {
            return response()->json(['status' => 'ok']);
        }

        $from = $message['from'];

        if (!in_array($type, ['text', 'interactive', 'image', 'document'])) {
            return response()->json(['status' => 'ok']);
        }

        $fileBlocks = [];
        if (in_array($type, ['image', 'document'])) {
            $caption  = trim($message[$type]['caption'] ?? '');
            $body     = $caption ?: '[operador enviou uma ' . ($type === 'image' ? 'foto' : 'arquivo') . ']';
            $mediaId  = $message[$type]['id'] ?? null;
            if ($mediaId) {
                $fileBlocks = $this->downloadMetaMedia($mediaId, $fetcher);
            }
        } elseif ($type === 'interactive') {
            $body = $message['interactive']['button_reply']['title'] ?? '';
        } else {
            $body = trim($message['text']['body'] ?? '');
        }

        if (!$from || !$body) {
            return response()->json(['status' => 'ok']);
        }

        $digits = preg_replace('/\D/', '', $from);

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

        $igVideoNotes = '';
        preg_match_all('~https?://(?:www\.)?instagram\.com/(?:reel|p)/[A-Za-z0-9_\-]+/?~', $body, $igMatches);
        if (!empty($igMatches[0])) {
            $apify        = app(ApifyService::class);
            $videoContext = $this->getVideoContext($history);
            foreach ($igMatches[0] as $igUrl) {
                $scraped = $apify->scrapeInstagramVideo($igUrl);
                if ($scraped) {
                    $filename = $apify->downloadToTemp($scraped['video_url'], $sessionId, $videoContext ?? '');
                    if ($filename) {
                        if ($scraped['poster_url']) {
                            Storage::disk('local')->put("temp/{$sessionId}/{$filename}.poster", $scraped['poster_url']);
                        }
                        if ($scraped['instagram_handle'] ?? null) {
                            Storage::disk('local')->put("temp/{$sessionId}/{$filename}.handle", $scraped['instagram_handle']);
                        }
                        $igVideoNotes .= "\n[Vídeo do Instagram salvo: {$igUrl}]";
                    } else {
                        $igVideoNotes .= "\n[Não foi possível baixar o vídeo do Instagram: {$igUrl}]";
                    }
                } else {
                    $igVideoNotes .= "\n[Vídeo do Instagram não encontrado ou privado: {$igUrl}]";
                }
            }
        }

        $messageToLara = trim($body . $igVideoNotes) ?: $body;

        $toolExecutor = fn(string $name, array $input) => match ($name) {
            'fetch_url' => $fetcher->fetch($input['url'] ?? '', $input),
            default     => 'Unknown tool.',
        };

        try {
            $reply = $lara->respond(
                $history->all(),
                $messageToLara,
                $context,
                $providerInfo,
                empty($fileBlocks) ? null : $fileBlocks,
                $toolExecutor
            );
        } catch (\Throwable $e) {
            Log::error('whatsapp.lara_error', ['phone' => $from, 'error' => $e->getMessage()]);
            self::sendMessage($from, 'Algo deu errado. Tente novamente em instantes.');
            return response()->json(['status' => 'ok']);
        }

        $displayReply = $reply;
        foreach (['{"action":"confirmed"', '{"action":"onboard"', '{"action":"outside_scope"', '{"action":"misuse"'] as $marker) {
            $pos = strpos($displayReply, $marker);
            if ($pos !== false) {
                $displayReply = trim(substr($displayReply, 0, $pos));
                break;
            }
        }
        if ($displayReply === '') {
            $displayReply = 'Entendido! Acesse painel.envago.live/parceiros para finalizar o cadastro da sua página.';
        }

        DB::table('lara_conversations')->insert([
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'user',      'message' => $body,         'created_at' => now(), 'updated_at' => now()],
            ['session_id' => $sessionId, 'uuid' => $sessionId, 'role' => 'assistant', 'message' => $displayReply, 'created_at' => now(), 'updated_at' => now()],
        ]);

        self::sendMessage($from, $displayReply);

        return response()->json(['status' => 'ok']);
    }

    private function downloadMetaMedia(string $mediaId, UrlFetcher $fetcher): array
    {
        $token = config('services.whatsapp.token');
        if (!$token) return [];

        try {
            $meta = Http::withToken($token)
                ->get("https://graph.facebook.com/v20.0/{$mediaId}");

            if (!$meta->successful()) return [];

            $mediaUrl = $meta->json('url');
            $mime     = $meta->json('mime_type') ?? 'image/jpeg';

            if (!$mediaUrl) return [];

            $bytes = Http::withToken($token)->timeout(15)->get($mediaUrl)->body();

            return [$fetcher->buildImageBlock($bytes, $mime)];
        } catch (\Throwable $e) {
            Log::warning('whatsapp: media download failed', ['media_id' => $mediaId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    public static function sendMessage(string $to, string $body): void
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token         = config('services.whatsapp.token');

        if (!$phoneNumberId || !$token) {
            return;
        }

        try {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'                => $to,
                    'type'              => 'text',
                    'text'              => ['body' => $body],
                ]);

            if (!$response->successful()) {
                Log::warning('whatsapp.send_failed', ['to' => $to, 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::error('whatsapp.send_error', ['to' => $to, 'error' => $e->getMessage()]);
        }
    }

    private function getVideoContext(\Illuminate\Support\Collection $history): ?string
    {
        foreach ($history->where('role', 'assistant')->reverse() as $msg) {
            if (preg_match('/\[VIDEO_FOR:([^\]]+)\]/', $msg->message, $m)) {
                return $m[1];
            }
        }
        return null;
    }
}
