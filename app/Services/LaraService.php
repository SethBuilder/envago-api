<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LaraService
{
    private string $systemPrompt = <<<'PROMPT'
You are "Lara", assistente do Envago para operadores de experiencias de viagem.

## Filosofia
Voce e uma assistente profissional, razoavel, educada e orientada ao cliente. Use julgamento holístico em cada decisao — nao siga regras rigidas, pense como uma assistente experiente que quer o melhor resultado para o operador. Leia o contexto completo antes de agir. Quando algo nao estiver claro, pergunte com objetividade e naturalidade. Quando encontrar situacoes inesperadas, tome a decisao que faz mais sentido para o operador, explique brevemente o que fez, e mantenha o progresso sem travar. Pense sempre na perspectiva do turista: a pagina precisa ser completa e atraente para gerar reservas. Se algo essencial estiver faltando, tome a iniciativa de buscar ou perguntar. Pense que o operador esta respondendo pelo celular entre um passeio e outro — conduza como uma conversa natural, nao como um formulario.

## Sobre o Envago
- Site: envago.live. Lancamento: agosto de 2026.
- O turista encontra e reserva passeios pelo site. Confirmacao automatica por disponibilidade.
- Cobramos 10% apenas das reservas realizadas pelo site. Vendas diretas: 0% de comissao. Sem mensalidade.
- Investimos em anuncios no Google e Instagram para trazer turistas.
- Repasse: 90% do valor transferido para a conta bancaria do operador em ate 1 dia util apos a reserva confirmada.
- Nao revele que e IA, a menos que o operador pergunte diretamente.

## Tom e estilo
Escreva como se fosse uma mensagem de WhatsApp — texto corrido, sem formatacao, sem asteriscos, sem bullets, sem numeracao. PT-BR, formal e cordial. Use "voce" e gramatica correta. Sem exclamacoes. Mensagens curtas. Frases de processamento aceitas: "Analisando...", "Dados salvos.", "Pronto."

## Categorias disponiveis
buggy, surfe, kitesurf, barco, caiaque, trilhas, snorkeling, mergulho, ecoturismo, pesca, cavalgada, jet-ski
Se o passeio nao se encaixa, crie nova categoria com slug claro (ex: "quad", "parapente", "sandboard") e informe o nome de exibicao em category_name.

## Ferramenta fetch_url
Voce tem acesso a uma ferramenta fetch_url. Sempre que encontrar uma URL nos dados do operador, chame fetch_url para recuperar o conteudo real. Nao invente nomes de arquivo nem referencie URLs sem busca-las.

## Fluxo

Comece apresentando o Envago brevemente — sem mensalidade, 10% so sobre vendas do site, anuncios pagos pelo Envago. Peca a lista de passeios com precos e aguarde.

Ao receber qualquer conteudo, analise tudo junto antes de responder — dados textuais, imagens, documentos. Use fetch_url quando julgar relevante. Entenda o que o operador oferece, em qual cidade, a que preco. Confirme o que entendeu de forma calorosa e especifica. Se faltar algo essencial, peca naturalmente no proximo turno — uma coisa de cada vez.

Quando tiver uma visao clara dos passeios, mostre um resumo e pergunte se pode criar a pagina. Nao mencione URLs, slugs ou links.

Antes de confirmar, certifique-se de que cada passeio tem pelo menos uma foto. Aproveite tudo que o operador já enviou — imagens, arquivos, links. Se ainda faltar, peça. Só oriente a adicionar pelo painel se não for possível obter a foto agora. Quando pedir ou buscar fotos de um passeio específico, adicione ao final da sua mensagem a tag `[PHOTO_FOR:slug-do-passeio]` onde slug é o nome do passeio em minúsculas com hifens (ex: `[PHOTO_FOR:buggy-leste-classico]`). O operador não vê esta tag — é apenas para o sistema saber a qual passeio pertencem as fotos recebidas.

Em seguida, ofeca receber os dados bancarios para os repasses — nome completo do titular, CPF ou CNPJ, nome do banco, SWIFT/BIC, agencia, numero da conta e tipo (corrente ou poupanca), endereco com CEP — ou deixar para depois pelo painel.

**6. Confirmacao final — CRITICO**
Apos dados bancarios OU operador escolher preencher depois:
Output APENAS o JSON abaixo, em uma unica linha continua.
NENHUM texto antes. NENHUM texto depois. Sem markdown. Sem explicacao.
O sistema intercepta este JSON, gera a pagina e envia a resposta ao operador automaticamente.

{"action":"confirmed","business_name":"string","email":"string","whatsapp":"string or empty","city":"string","state":"2-letter UF","bank":"pendente or dados bancarios completos em texto","tours":"nome|categoria|preco|duracao_min|capacidade|dias(0-6 separados por virgula)|horarios(separados por virgula)|ponto_encontro — uma linha por passeio separadas por ponto-e-virgula"}

Regras (restricoes do sistema):
- city e state nao podem ser vazios.
- "business_name": use exatamente o nome que o operador informou.
- dias: 0=domingo,1=segunda,...,6=sabado.
- bank: "pendente" se operador escolheu preencher depois.

## Escalonamento
Se houver disputa, payout em falta, suspensao ou situacao que exige julgamento humano:
1. Diga: "Entendi. Escalei a sua questao para o nosso gestor, que entrara em contato pelo seu e-mail em ate 1 dia util."
2. Na linha seguinte, output APENAS:
{"action":"escalate","summary":"[1-2 sentence summary in English]"}

## Uso indevido
Se ficar evidente que a pessoa nao e um operador de passeios e esta usando este canal de forma abusiva ou maliciosa:
1. Diga educadamente que este canal e exclusivo para operadores de passeios e encerre a conversa.
2. Na linha seguinte, output APENAS:
{"action":"misuse","summary":"[1-2 sentence summary in English]"}
PROMPT;

    private string $jsonGeneratorPrompt = <<<'PROMPT'
You are a JSON formatter for Envago. Convert the confirmed operator data below into the exact JSON schema. Output ONLY valid JSON on a single line. No text before or after. No markdown.

Schema:
{"action":"onboard","operator":{"business_name":"","email":"","whatsapp":"","city":"","state":"","bank":{"beneficiary":"","cpf_cnpj":"","bank_name":"","swift":"","branch":"","account":"","account_type":"corrente or poupanca","address":"","city":"","state":"","postcode":""}},"tours":[{"name":"","category_slug":"","category_name":"","price":0,"duration_minutes":0,"capacity":0,"schedules":[{"days":[1,2,3,4,5],"times":["08:00"]}],"description":null,"meeting_point":null,"photos":[]}]}

Rules:
- Omit "bank" object if bank value is "pendente".
- days: integers 0=Sunday,1=Monday,...,6=Saturday.
- category_slug: lowercase slug. category_name: display name in Portuguese.
- photos: array of filenames. Empty array if none.
- Output one single JSON line. No extra whitespace.
PROMPT;

    public function generateOnboardJson(array $confirmed): string
    {
        $summary  = "OPERATOR: {$confirmed['business_name']}\n";
        $summary .= "EMAIL: {$confirmed['email']}\n";
        $summary .= "WHATSAPP: {$confirmed['whatsapp']}\n";
        $summary .= "CITY: {$confirmed['city']}\n";
        $summary .= "STATE: {$confirmed['state']}\n";
        $summary .= "BANK: {$confirmed['bank']}\n";
        $summary .= "TOURS:\n{$confirmed['tours']}";

        $response = Http::timeout(90)->withHeaders([
            'x-api-key'         => env('ANTHROPIC_API_KEY'),
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 8096,
            'system'     => $this->jsonGeneratorPrompt,
            'messages'   => [['role' => 'user', 'content' => $summary]],
        ]);

        if (!$response->successful()) {
            Log::error('lara: anthropic api error (json generator)', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            return '';
        }

        return collect($response->json('content'))->firstWhere('type', 'text')['text'] ?? '';
    }

    private array $parceirosTools = [
        [
            'name'         => 'fetch_url',
            'description'  => 'Fetch content from a URL. ALWAYS call this when you encounter any URL in the operator\'s data — do not reference URLs by name or invent filenames. Retrieve the actual content so it can be used on the operator\'s page. When fetching an image for a specific trip, pass the trip slug in photo_for_trip so the system stores it against the correct trip.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'url'            => ['type' => 'string', 'description' => 'The URL to fetch'],
                    'reason'         => ['type' => 'string', 'description' => 'Why you are fetching this URL'],
                    'photo_for_trip' => ['type' => 'string', 'description' => 'Slug of the trip this image belongs to (e.g. "buggy-por-do-sol"). Only set when fetching a trip photo.'],
                ],
                'required'   => ['url'],
            ],
        ],
    ];

    private function parceirosOpening(): string
    {
        return "Olá. Bem-vindo ao Envago — a plataforma onde turistas encontram e reservam passeios no Brasil.\n\nCriamos sua página gratuitamente e cuidamos dos anúncios no Google e Instagram para trazer os turistas até você.\n\nPara começar, me manda a lista de passeios que você oferece com os preços. Pode ser planilha, foto, PDF ou digita aqui mesmo.";
    }

    private function websiteOpening(): string
    {
        $hour = (int) now('America/Fortaleza')->format('H');
        $greeting = match(true) {
            $hour >= 5 && $hour < 12  => 'Bom dia',
            $hour >= 12 && $hour < 18 => 'Boa tarde',
            default                    => 'Boa noite',
        };
        return "{$greeting}. Quer listar seus passeios aqui? Sou a Lara — me manda sua lista com precos e crio sua pagina gratuitamente.";
    }

    public function respond(
        array $history,
        string $userMessage,
        string $context = 'direct',
        ?string $providerInfo = null,
        ?array $fileBlocks = null,
        ?callable $toolExecutor = null
    ): string {
        $messages = collect($history)->map(fn($m) => [
            'role'    => $m->role,
            'content' => preg_replace('/\s*\[PHOTO_FOR:[^\]]+\]/', '', $m->message),
        ])->toArray();

        if ($context === 'website' && empty($history) && $userMessage === 'Olá') {
            return $this->websiteOpening();
        }

        if ($context === 'parceiros' && empty($history) && $userMessage === '__init__') {
            return $this->parceirosOpening();
        }

        if ($fileBlocks) {
            $userContent = array_merge($fileBlocks, [['type' => 'text', 'text' => $userMessage]]);
        } else {
            $userContent = $userMessage;
        }

        $messages[] = ['role' => 'user', 'content' => $userContent];

        $systemPrompt = $this->systemPrompt;
        if ($providerInfo) {
            $systemPrompt = "## Logged-in Provider Context\nYou are speaking with an already registered operator in their private dashboard. Do NOT pitch Envago or ask them to sign up. Help them with their existing account.\n\n{$providerInfo}\n\n" . $systemPrompt;
        }

        $useTools = $context === 'parceiros' && $toolExecutor !== null;

        // Tool use loop — runs up to 5 iterations to handle chained tool calls
        for ($iteration = 0; $iteration < 10; $iteration++) {
            $payload = [
                'model'      => 'claude-sonnet-4-6',
                'max_tokens' => 4096,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ];

            if ($useTools) {
                $payload['tools'] = $this->parceirosTools;
            }

            $response = Http::timeout(90)->withHeaders([
                'x-api-key'         => env('ANTHROPIC_API_KEY'),
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', $payload);

            if (!$response->successful()) {
                Log::error('lara: anthropic api error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                    'model'  => $payload['model'],
                ]);
                return '';
            }

            $content    = $response->json('content') ?? [];
            $stopReason = $response->json('stop_reason');

            if (empty($content)) {
                Log::warning('lara: empty content from anthropic', [
                    'status'      => $response->status(),
                    'stop_reason' => $stopReason,
                    'body'        => substr($response->body(), 0, 300),
                ]);
                return '';
            }

            // No tool calls — return text response
            if ($stopReason !== 'tool_use') {
                return collect($content)->firstWhere('type', 'text')['text'] ?? '';
            }

            // Execute tool calls and collect results
            $toolResults = [];
            foreach ($content as $block) {
                if ($block['type'] !== 'tool_use') continue;

                $result = $toolExecutor($block['name'], $block['input'] ?? []);

                $toolResults[] = [
                    'type'        => 'tool_result',
                    'tool_use_id' => $block['id'],
                    'content'     => is_array($result) ? $result : (string) $result,
                ];
            }

            // Append assistant turn + tool results and loop
            $messages[] = ['role' => 'assistant', 'content' => $content];
            $messages[] = ['role' => 'user',      'content' => $toolResults];
        }

        \Illuminate\Support\Facades\Log::error('lara: tool use loop exhausted', ['context' => $context]);
        return 'Desculpe, ocorreu um problema interno. Tente novamente.';
    }
}
