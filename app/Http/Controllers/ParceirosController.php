<?php

namespace App\Http\Controllers;

use App\Services\ApifyService;
use App\Services\LaraService;
use App\Services\OnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParceirosController extends Controller
{
    public function show()
    {
        return view('parceiros');
    }

    public function message(Request $request, LaraService $lara, OnboardingService $onboarding)
    {
        $request->validate([
            'session_id' => 'required|string|max:100',
            'message'    => 'nullable|string|max:4000',
            'files.*'    => 'nullable|file|max:20480',
        ]);

        // Honeypot — bots fill this field, humans don't
        if ($request->filled('website')) {
            $this->blockIp($request->ip(), 'honeypot');
            return response()->json(['reply' => ''], 200);
        }

        set_time_limit(120);

        $sessionId   = $request->input('session_id');
        $userMessage = $request->input('message', '');
        $isInit      = $userMessage === '__init__';

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        // Process uploaded files — images go to temp storage + multimodal blocks; docs extracted as text
        $fileBlocks  = [];
        $uploadedMsg = '';

        // Extract media context from last assistant message (Lara's [PHOTO_FOR:slug] / [VIDEO_FOR:slug] tags)
        $photoContext = $this->getPhotoContext($history);
        $videoContext = $this->getVideoContext($history);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $ext      = strtolower($file->getClientOriginalExtension());
                $mime     = $file->getMimeType();

                if (in_array($ext, ['xlsx', 'xls', 'csv', 'ods'])) {
                    $text         = $this->extractSpreadsheet($file->getRealPath(), $ext);
                    $uploadedMsg .= "[Planilha: {$filename}]\n{$text}\n";
                } elseif ($ext === 'docx') {
                    $text         = $this->extractDocx($file->getRealPath());
                    $uploadedMsg .= "[Documento: {$filename}]\n{$text}\n";
                } elseif ($ext === 'pdf') {
                    $fileBlocks[] = [
                        'type'   => 'document',
                        'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => base64_encode(file_get_contents($file->getRealPath()))],
                        'title'  => $filename,
                    ];
                } elseif (str_starts_with($mime ?? '', 'image/')) {
                    // Save to temp storage with slug prefix if photo context is known
                    $savedName = $photoContext ? "{$photoContext}__{$filename}" : $filename;
                    $this->saveTempPhoto($sessionId, $savedName, $file->getRealPath());

                    $fileBlocks[] = [
                        'type'   => 'image',
                        'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => base64_encode(file_get_contents($file->getRealPath()))],
                    ];
                } else {
                    $uploadedMsg .= "[Arquivo: {$filename}]\n";
                }
            }
        }

        $userMessage = trim($uploadedMsg . $userMessage);

        // Detect Instagram URLs and scrape videos via Apify
        $igVideoNotes = '';
        if (!$isInit && $userMessage) {
            preg_match_all('~https?://(?:www\.)?instagram\.com/(?:reel|p)/[A-Za-z0-9_\-]+/?~', $userMessage, $igMatches);
            if (!empty($igMatches[0])) {
                $apify = app(ApifyService::class);
                foreach ($igMatches[0] as $igUrl) {
                    $scraped = $apify->scrapeInstagramVideo($igUrl);
                    if ($scraped) {
                        $prefix   = $videoContext ?? $photoContext ?? '';
                        $filename = $apify->downloadToTemp($scraped['video_url'], $sessionId, $prefix, $scraped['audio_url'] ?? null);
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
        }

        $triggerMessage = $isInit ? '__init__' : (trim($userMessage . $igVideoNotes) ?: 'Olá');

        $rawReply = $lara->respond(
            $history->all(),
            $triggerMessage,
            'parceiros',
            null,
            empty($fileBlocks) ? null : $fileBlocks,
            $this->makeFetchUrlExecutor($sessionId)
        );

        // Intercept confirmed/onboard JSON
        // Strip internal tags and any action JSON from what gets stored/displayed
        $cleanReply = preg_replace('/\s*\[(?:PHOTO|VIDEO)_FOR:[^\]]+\]/', '', $rawReply);
        $cleanReply = preg_replace('/\s*\{"action":.*$/s', '', $cleanReply);
        $confirmedData    = $this->extractConfirmedJson($rawReply);
        $onboardData      = $confirmedData ? null : $this->extractOnboardJson($rawReply);
        $outsideScopeData = (!$confirmedData && !$onboardData) ? $this->extractOutsideScopeJson($rawReply) : null;

        if ($confirmedData) {
            try {
                $jsonStr     = $lara->generateOnboardJson($confirmedData);
                $onboardData = $this->extractOnboardJson($jsonStr);
                if (!$onboardData) throw new \RuntimeException("JSON generator returned invalid JSON: " . substr($jsonStr, 0, 200));
            } catch (\Throwable $e) {
                Log::error('parceiros.json_generation failed', ['error' => $e->getMessage()]);
                $cleanReply = 'Ocorreu um erro ao finalizar o cadastro. Nossa equipe foi notificada — tente novamente.';
            }
        }

        // Post-process: assign temp photos and videos to tours by slug prefix — no LLM involved
        if ($onboardData) {
            $tempFiles = $this->listTempFiles($sessionId);
            foreach ($onboardData['tours'] as &$tour) {
                $slug = \Illuminate\Support\Str::slug($tour['name']);

                // Photos
                if (isset($tempFiles['photos'][$slug])) {
                    $tour['photos'] = $tempFiles['photos'][$slug];
                } elseif (!empty($tempFiles['photos']['__unassigned']) && empty($tour['photos'])) {
                    $tour['photos']                           = $tempFiles['photos']['__unassigned'];
                    $tempFiles['photos']['__unassigned']       = [];
                }

                // Videos
                if (isset($tempFiles['videos'][$slug])) {
                    $tour['videos'] = $tempFiles['videos'][$slug];
                } elseif (!empty($tempFiles['videos']['__unassigned']) && empty($tour['videos'])) {
                    $tour['videos']                           = $tempFiles['videos']['__unassigned'];
                    $tempFiles['videos']['__unassigned']       = [];
                }
            }
            unset($tour);
        }

        if ($onboardData) {
            try {
                $pageUrl    = $onboarding->onboard($onboardData, $sessionId);
                $email      = $onboardData['operator']['email'] ?? '';
                $cleanReply = "Página criada. Você pode visualizá-la em:\n{$pageUrl}\n\nEnviamos um e-mail para {$email} com o link para ativar sua conta e acessar o painel. Qualquer dúvida, estou aqui.";
            } catch (\Throwable $e) {
                Log::error('parceiros.onboard failed', ['error' => $e->getMessage()]);
                $cleanReply = 'Ocorreu um erro ao criar sua página. Nossa equipe foi notificada — tente novamente em alguns minutos.';
            }
        } elseif ($outsideScopeData) {
            // Strip the JSON from cleanReply — show only Lara's conversational text
            $jsonPos = strpos($cleanReply, '{"action":"outside_scope"');
            if ($jsonPos !== false) {
                $cleanReply = trim(substr($cleanReply, 0, $jsonPos));
            }
            if (empty($cleanReply)) {
                $cleanReply = 'Obrigada pelas informações. Guardei seus dados e entraremos em contato quando o Envago expandir para esse segmento.';
            }
            $leadId = DB::table('scope_leads')->insertGetId([
                'session_id'    => $sessionId,
                'business_name' => $outsideScopeData['business_name'] ?? null,
                'location'      => $outsideScopeData['location'] ?? null,
                'offering'      => $outsideScopeData['offering'] ?? '',
                'price_range'   => $outsideScopeData['price_range'] ?? null,
                'whatsapp'      => $outsideScopeData['whatsapp'] ?? null,
                'summary'       => $outsideScopeData['summary'] ?? '',
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $photos = $this->moveScopeLeadPhotos($sessionId, $leadId);
            if ($photos) {
                DB::table('scope_leads')->where('id', $leadId)->update(['photos' => json_encode($photos)]);
            }
        } elseif (!$confirmedData && str_contains($rawReply, '"action":"misuse"')) {
            $this->notifyAdminsMisuse($rawReply, $sessionId, $request->ip());
        } elseif (!$confirmedData && str_contains($rawReply, '"action":')) {
            Log::error('parceiros: action JSON unparseable', ['reply' => $rawReply]);
            $cleanReply = 'Estou finalizando o cadastro. Pode um momento e responde "ok" para eu tentar novamente.';
        }

        $rows = [];
        if (!$isInit && $userMessage) {
            $rows[] = [
                'session_id' => $sessionId,
                'uuid'       => $sessionId,
                'role'       => 'user',
                'message'    => $userMessage,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // Save rawReply so [PHOTO_FOR:xxx] tags persist for future photo context.
        // When an action JSON was processed, save $cleanReply instead to avoid leaking raw JSON into history display.
        $savedReply = ($confirmedData || $onboardData || $outsideScopeData) ? $cleanReply : $rawReply;
        $rows[] = [
            'session_id' => $sessionId,
            'uuid'       => $sessionId,
            'role'       => 'assistant',
            'message'    => $savedReply,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('lara_conversations')->insert($rows);

        return response()->json(['reply' => $cleanReply]);
    }

    private function blockIp(string $ip, string $reason): void
    {
        DB::table('blocked_ips')->updateOrInsert(['ip' => $ip], ['reason' => $reason, 'updated_at' => now(), 'created_at' => now()]);
        \Illuminate\Support\Facades\Cache::forget("blocked_ip:{$ip}");
        Log::warning('parceiros: IP blocked', ['ip' => $ip, 'reason' => $reason]);
    }

    private function notifyAdminsMisuse(string $reply, string $sessionId, string $ip): void
    {
        preg_match('/"summary"\s*:\s*"([^"]+)"/', $reply, $m);
        $summary = $m[1] ?? 'No summary.';

        $this->blockIp($ip, "misuse: {$summary}");

        $admins = \App\Models\User::where('role', 'admin')->pluck('email');
        foreach ($admins as $email) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Misuse detected on /parceiros\n\nSession: {$sessionId}\nIP: {$ip}\n\nSummary: {$summary}",
                    fn($msg) => $msg->to($email)->subject('Envago — /parceiros misuse alert')
                );
            } catch (\Throwable $e) {
                Log::error('parceiros: misuse email failed', ['error' => $e->getMessage()]);
            }
        }
    }

    private function makeFetchUrlExecutor(string $sessionId): callable
    {
        return function (string $name, array $input) use ($sessionId): mixed {
            if ($name !== 'fetch_url') return 'Unknown tool.';

            $url = $input['url'] ?? '';
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return 'Invalid URL.';
            }

            try {
                $response = Http::timeout(10)->get($url);
                if (!$response->successful()) {
                    return "Could not fetch URL (HTTP {$response->status()}).";
                }

                $contentType = strtolower($response->header('Content-Type') ?? '');
                $mime        = explode(';', $contentType)[0];
                $body        = $response->body();

                if (str_starts_with($mime, 'image/')) {
                    $filename   = basename(parse_url($url, PHP_URL_PATH)) ?: uniqid('img_') . '.jpg';
                    $tripSlug   = $input['photo_for_trip'] ?? null;
                    $savedName  = $tripSlug ? "{$tripSlug}__{$filename}" : $filename;
                    $this->saveTempPhotoContent($sessionId, $savedName, $body);
                    return [
                        ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => $mime ?: 'image/jpeg', 'data' => base64_encode($body)]],
                    ];
                }

                if (str_contains($mime, 'pdf')) {
                    return [
                        ['type' => 'text',     'text' => "PDF fetched from {$url}"],
                        ['type' => 'document', 'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => base64_encode($body)]],
                    ];
                }

                // HTML or plain text — strip tags, truncate
                $text = strip_tags($body);
                $text = preg_replace('/\s+/', ' ', trim($text));
                return substr($text, 0, 4000);
            } catch (\Throwable $e) {
                Log::warning('parceiros: fetch_url failed', ['url' => $url, 'error' => $e->getMessage()]);
                return "Could not fetch URL: {$e->getMessage()}";
            }
        };
    }

    private function listTempFiles(string $sessionId): array
    {
        $videoExts = ['mp4', 'mov', 'webm', 'avi'];
        $files     = Storage::disk('local')->files("temp/{$sessionId}");
        $result    = ['photos' => [], 'videos' => []];

        foreach ($files as $file) {
            $basename = basename($file);
            // Skip sidecar files
            if (str_ends_with($basename, '.poster')) continue;

            $ext    = strtolower(pathinfo($basename, PATHINFO_EXTENSION));
            $bucket = in_array($ext, $videoExts) ? 'videos' : 'photos';

            if (str_contains($basename, '__')) {
                [$slug, ] = explode('__', $basename, 2);
                $result[$bucket][$slug][] = $basename;
            } else {
                $result[$bucket]['__unassigned'][] = $basename;
            }
        }
        return $result;
    }

    private function getPhotoContext(\Illuminate\Support\Collection $history): ?string
    {
        foreach ($history->where('role', 'assistant')->reverse() as $msg) {
            if (preg_match('/\[PHOTO_FOR:([^\]]+)\]/', $msg->message, $m)) {
                return $m[1];
            }
        }
        return null;
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

    private function saveTempPhotoContent(string $sessionId, string $filename, string $content): void
    {
        $dest = "temp/{$sessionId}/" . basename($filename);
        Storage::disk('local')->put($dest, $content);
    }

    private function saveTempPhoto(string $sessionId, string $filename, string $realPath): void
    {
        $dest = "temp/{$sessionId}/" . basename($filename);
        Storage::disk('local')->put($dest, file_get_contents($realPath));
    }

    private function extractDocx(string $path): string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) return '[Não foi possível abrir o documento.]';
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if (!$xml) return '[Documento vazio ou formato inválido.]';
            $text = preg_replace('/<w:p[ >]/', "\n<w:p>", $xml);
            $text = strip_tags($text);
            return preg_replace('/\n{3,}/', "\n\n", trim($text));
        } catch (\Throwable $e) {
            return '[Não foi possível ler o documento.]';
        }
    }

    private function extractSpreadsheet(string $path, string $ext): string
    {
        try {
            $spreadsheet = IOFactory::load($path);
            $lines = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $data = $sheet->toArray(null, true, true, false);
                foreach ($data as $row) {
                    $cells = array_map(fn($v) => trim((string)($v ?? '')), $row);
                    $line  = implode(' | ', array_filter($cells, fn($c) => $c !== ''));
                    if ($line) $lines[] = $line;
                }
            }
            return implode("\n", $lines);
        } catch (\Throwable $e) {
            Log::warning('parceiros: spreadsheet parse failed', ['error' => $e->getMessage()]);
            return '[Não foi possível ler o arquivo. Solicite ao operador que cole os dados manualmente.]';
        }
    }

    private function moveScopeLeadPhotos(string $sessionId, int $leadId): array
    {
        $files = Storage::disk('local')->files("temp/{$sessionId}");
        $urls  = [];

        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) continue;

            $dest    = "scope_leads/{$leadId}/" . basename($file);
            $content = Storage::disk('local')->get($file);
            Storage::disk('public')->put($dest, $content);
            $urls[] = Storage::disk('public')->url($dest);
        }

        return $urls;
    }

    private function extractOutsideScopeJson(string $reply): ?array
    {
        $stripped = preg_replace('/```json\s*|\s*```/', '', $reply);
        $pos      = strpos($stripped, '{"action":"outside_scope"');
        if ($pos === false) return null;

        $candidate = substr($stripped, $pos);
        for ($i = strlen($candidate); $i > 0; $i--) {
            if ($candidate[$i - 1] !== '}') continue;
            $data = json_decode(substr($candidate, 0, $i), true);
            if (is_array($data) && ($data['action'] ?? '') === 'outside_scope') {
                return $data;
            }
        }
        return null;
    }

    private function extractConfirmedJson(string $reply): ?array
    {
        $stripped = preg_replace('/```json\s*|\s*```/', '', $reply);
        $pos      = strpos($stripped, '{"action":"confirmed"');
        if ($pos === false) return null;

        $candidate = substr($stripped, $pos);
        for ($i = strlen($candidate); $i > 0; $i--) {
            if ($candidate[$i - 1] !== '}') continue;
            $data = json_decode(substr($candidate, 0, $i), true);
            if (is_array($data) && ($data['action'] ?? '') === 'confirmed') {
                return $data;
            }
        }
        return null;
    }

    private function extractOnboardJson(string $reply): ?array
    {
        $stripped = preg_replace('/```json\s*|\s*```/', '', $reply);
        $pos      = strpos($stripped, '{"action":');
        if ($pos === false) return null;

        $candidate = substr($stripped, $pos);
        for ($i = strlen($candidate); $i > 0; $i--) {
            if ($candidate[$i - 1] !== '}') continue;
            $data = json_decode(substr($candidate, 0, $i), true);
            if (is_array($data) && ($data['action'] ?? '') === 'onboard') {
                return $data;
            }
        }
        return null;
    }
}
