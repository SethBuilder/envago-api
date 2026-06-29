<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivationMail;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Provider;
use App\Models\User;
use App\Services\LaraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ParceirosController extends Controller
{
    public function show()
    {
        return view('parceiros');
    }

    public function message(Request $request, LaraService $lara)
    {
        $request->validate([
            'session_id' => 'required|string|max:100',
            'message'    => 'nullable|string|max:4000',
            'file'       => 'nullable|file|max:20480',
        ]);

        $sessionId   = $request->input('session_id');
        $userMessage = $request->input('message', '');
        $isInit      = $userMessage === '__init__';

        $history = DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->get();

        $fileBlocks = null;
        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $filename = $file->getClientOriginalName();
            $ext      = strtolower($file->getClientOriginalExtension());
            $mime     = $file->getMimeType();

            if (in_array($ext, ['xlsx', 'xls', 'csv', 'ods'])) {
                $text        = $this->extractSpreadsheet($file->getRealPath(), $ext);
                $userMessage = trim("[Planilha: {$filename}]\n{$text}\n{$userMessage}");
            } elseif ($ext === 'docx') {
                $text        = $this->extractDocx($file->getRealPath());
                $userMessage = trim("[Documento: {$filename}]\n{$text}\n{$userMessage}");
            } elseif ($ext === 'pdf') {
                $fileBlocks  = [[
                    'type'   => 'document',
                    'source' => ['type' => 'base64', 'media_type' => 'application/pdf', 'data' => base64_encode(file_get_contents($file->getRealPath()))],
                    'title'  => $filename,
                ]];
                $userMessage = $userMessage ?: 'Analise este PDF e extraia informações sobre os passeios.';
            } elseif (str_starts_with($mime ?? '', 'image/')) {
                $fileBlocks  = [[
                    'type'   => 'image',
                    'source' => ['type' => 'base64', 'media_type' => $mime, 'data' => base64_encode(file_get_contents($file->getRealPath()))],
                ]];
                $userMessage = $userMessage ?: 'Analise esta imagem e extraia informações sobre os passeios.';
            } else {
                $userMessage = trim("[Arquivo enviado: {$filename}]\n{$userMessage}");
            }
        }

        $triggerMessage = $isInit
            ? '[SYSTEM: This is the very first message. Start immediately with the onboarding opening from step 1 of your flow. Do not say "Como posso ajudá-lo" or any generic greeting.]'
            : $userMessage;
        $rawReply = $lara->respond($history->all(), $triggerMessage, 'parceiros', null, $fileBlocks);

        // Intercept onboard JSON — must happen before saving to DB
        $cleanReply = $rawReply;
        $onboardData = $this->extractOnboardJson($rawReply);
        if ($onboardData) {
            try {
                $cleanReply = $this->processOnboard($onboardData, $sessionId);
            } catch (\Throwable $e) {
                Log::error('parceiros.onboard: processOnboard failed', ['error' => $e->getMessage()]);
                $cleanReply = 'Ocorreu um erro ao criar sua página. Nossa equipe foi notificada — tente novamente em alguns minutos.';
            }
        } elseif (str_contains($rawReply, '"action":')) {
            // Reply contains JSON fragment we couldn't parse — never show raw JSON to operator
            Log::warning('parceiros.onboard: JSON detected but unparseable', ['reply' => substr($rawReply, 0, 300)]);
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

    private function extractDocx(string $path): string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) return '[Não foi possível abrir o documento.]';
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if (!$xml) return '[Documento vazio ou formato inválido.]';
            // Strip XML tags, preserve paragraph breaks
            $text = preg_replace('/<w:p[ >]/', "\n<w:p>", $xml);
            $text = strip_tags($text);
            $text = preg_replace('/\n{3,}/', "\n\n", trim($text));
            return $text;
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

    private function extractOnboardJson(string $reply): ?array
    {
        $stripped = preg_replace('/```json\s*|\s*```/', '', $reply);

        $pos = strpos($stripped, '{"action":');
        if ($pos === false) return null;

        // Walk backwards from end finding the last } to get complete JSON
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

    private function processOnboard(array $data, string $sessionId): string
    {
        $op = $data['operator'] ?? [];
        $email    = trim($op['email'] ?? '');
        $bizName  = trim($op['business_name'] ?? '');
        $whatsapp = trim($op['whatsapp'] ?? '');
        $city     = trim($op['city'] ?? '');
        $state    = trim($op['state'] ?? '');

        if (!$email || !$bizName) {
            Log::warning('parceiros.onboard: missing email or business_name', $op);
            return 'Preciso do seu e-mail para finalizar o cadastro. Pode me informar?';
        }

        $token = Str::random(64);

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name'                        => $bizName,
                'email'                       => $email,
                'password'                    => Hash::make(Str::random(32)),
                'role'                        => 'provider',
                'activation_token'            => $token,
                'activation_token_expires_at' => now()->addHours(72),
            ]);
        } else {
            // Refresh token so the activation email always has a valid link
            $user->update([
                'activation_token'            => $token,
                'activation_token_expires_at' => now()->addHours(72),
            ]);
        }

        $bankDetails = isset($op['bank']) ? $op['bank'] : null;
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) {
            $provider = Provider::create([
                'user_id'         => $user->id,
                'business_name'   => $bizName,
                'whatsapp_number' => $whatsapp ?: null,
                'bank_details'    => $bankDetails,
                'status'          => 'pending',
            ]);
        } else {
            // Update whatsapp/bank if newly provided
            $provider->update(array_filter([
                'whatsapp_number' => $whatsapp ?: null,
                'bank_details'    => $bankDetails ?? $provider->bank_details,
            ]));
        }

        // Link conversation rows to provider
        DB::table('lara_conversations')
            ->where('session_id', $sessionId)
            ->update(['provider_id' => $provider->id, 'email' => $email, 'whatsapp' => $whatsapp ?: null]);

        // Create activities
        foreach ($data['tours'] ?? [] as $tour) {
            $catSlug = $tour['category_slug'] ?? null;
            $catName = $tour['category_name'] ?? $catSlug;

            $category = null;
            if ($catSlug) {
                $category = Category::where('slug', $catSlug)->first()
                    ?? Category::create(['name' => $catName, 'slug' => $catSlug]);
            }

            $actSlug = Str::slug($tour['name'] ?? $bizName);
            $suffix  = 2;
            $base    = $actSlug;
            while (Activity::where('slug', $actSlug)->exists()) {
                $actSlug = $base . '-' . $suffix++;
            }

            Activity::create([
                'provider_id'      => $provider->id,
                'category_id'      => $category?->id,
                'name'             => $tour['name'] ?? 'Passeio',
                'slug'             => $actSlug,
                'description'      => $tour['description'] ?? null,
                'price'            => $tour['price'] ?? 0,
                'duration_minutes' => $tour['duration_minutes'] ?? 60,
                'capacity'         => $tour['max_seats'] ?? $tour['daily_seats'] ?? 4,
                'city'             => $city,
                'state'            => $state,
                'meeting_point'    => $tour['meeting_point'] ?? null,
                'is_active'        => true,
            ]);
        }

        // Send activation email
        try {
            Mail::to($email)->send(new AccountActivationMail($user));
        } catch (\Throwable $e) {
            Log::error('parceiros.onboard: email failed', ['error' => $e->getMessage()]);
        }

        $previewUrl = rtrim(env('FRONTEND_URL', 'https://envago.live'), '/') . "/operadores/{$provider->slug}?preview=true";

        return "Página criada. Você pode visualizá-la em:\n{$previewUrl}\n\nEnviamos um e-mail para {$email} com o link para ativar sua conta e acessar o painel. Qualquer dúvida, estou aqui.";
    }
}
