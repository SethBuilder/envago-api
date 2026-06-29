<?php

namespace App\Http\Controllers;

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
                    // Save to temp storage so OnboardingService can link to activities later
                    $this->saveTempPhoto($sessionId, $filename, $file->getRealPath());

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

        $triggerMessage = $isInit
            ? '[SYSTEM: This is the very first message. Start immediately with the onboarding opening from step 1 of your flow. Do not say "Como posso ajudá-lo" or any generic greeting.]'
            : ($userMessage ?: 'Olá');

        $rawReply = $lara->respond(
            $history->all(),
            $triggerMessage,
            'parceiros',
            null,
            empty($fileBlocks) ? null : $fileBlocks,
            $this->makeFetchUrlExecutor($sessionId)
        );

        // Intercept confirmed/onboard JSON
        Log::info('parceiros.raw_reply', ['session' => $sessionId, 'reply' => substr($rawReply, 0, 500)]);
        $cleanReply    = $rawReply;
        $confirmedData = $this->extractConfirmedJson($rawReply);
        $onboardData   = $confirmedData ? null : $this->extractOnboardJson($rawReply);

        if ($confirmedData) {
            try {
                $jsonStr     = $lara->generateOnboardJson($confirmedData);
                Log::info('parceiros.generated_json', ['session' => $sessionId, 'json' => substr($jsonStr, 0, 500)]);
                $onboardData = $this->extractOnboardJson($jsonStr);
                if (!$onboardData) throw new \RuntimeException("JSON generator returned invalid JSON: " . substr($jsonStr, 0, 200));
            } catch (\Throwable $e) {
                Log::error('parceiros.json_generation failed', ['error' => $e->getMessage()]);
                $cleanReply = 'Ocorreu um erro ao finalizar o cadastro. Nossa equipe foi notificada — tente novamente.';
            }
        }

        if ($onboardData) {
            try {
                $previewUrl = $onboarding->onboard($onboardData, $sessionId);
                $email      = $onboardData['operator']['email'] ?? '';
                $cleanReply = "Página criada. Você pode visualizá-la em:\n{$previewUrl}\n\nEnviamos um e-mail para {$email} com o link para ativar sua conta e acessar o painel. Qualquer dúvida, estou aqui.";
            } catch (\Throwable $e) {
                Log::error('parceiros.onboard failed', ['error' => $e->getMessage()]);
                $cleanReply = 'Ocorreu um erro ao criar sua página. Nossa equipe foi notificada — tente novamente em alguns minutos.';
            }
        } elseif (!$confirmedData && str_contains($rawReply, '"action":')) {
            Log::warning('parceiros: JSON detected but unparseable', ['reply' => substr($rawReply, 0, 300)]);
            $cleanReply = 'Estou finalizando o cadastro. Pode levar alguns segundos — tente novamente em instantes.';
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
        $rows[] = [
            'session_id' => $sessionId,
            'uuid'       => $sessionId,
            'role'       => 'assistant',
            'message'    => $cleanReply,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        DB::table('lara_conversations')->insert($rows);

        return response()->json(['reply' => $cleanReply]);
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
                    $filename = basename(parse_url($url, PHP_URL_PATH)) ?: uniqid('img_') . '.jpg';
                    $this->saveTempPhotoContent($sessionId, $filename, $body);
                    return [
                        ['type' => 'text', 'text' => "Image fetched. Filename for reference: {$filename}"],
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

    private function saveTempPhotoContent(string $sessionId, string $filename, string $content): void
    {
        $dest = "temp/{$sessionId}/" . basename($filename);
        Storage::put($dest, $content);
    }

    private function saveTempPhoto(string $sessionId, string $filename, string $realPath): void
    {
        $dest = "temp/{$sessionId}/" . basename($filename);
        Storage::put($dest, file_get_contents($realPath));
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
