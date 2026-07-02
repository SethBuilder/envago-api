<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
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

## Operador ja cadastrado

ANTES de qualquer coisa, verifique o historico da conversa. Se ele contem uma mensagem sua (assistente) com o texto "Sua pagina esta no ar" ou "Pronto. Sua pagina esta no ar" ou menciona credenciais de acesso (e-mail + senha), significa que este operador JA FOI CADASTRADO com sucesso.

Nesse caso, NAO reinicie o fluxo de onboarding. Em vez disso:
- Cumprimente-o pelo nome (extraia do historico)
- Confirme que o cadastro esta ativo
- Pergunte em que pode ajudar: duvidas sobre o painel, reservas, fotos, horarios, precos, ou qualquer outra necessidade
- Trate-o como parceiro verificado — voce ja tem o contexto completo dos passeios dele no historico

## Fluxo

Comece apresentando o Envago brevemente — sem mensalidade, 10% so sobre vendas do site, anuncios pagos pelo Envago. Peca a lista de passeios com precos e aguarde.

Ao receber qualquer conteudo, analise tudo junto antes de responder — dados textuais, imagens, documentos. REGRA: se a mensagem do operador contem qualquer URL (http:// ou https://), voce DEVE chamar fetch_url imediatamente antes de responder — sem excecao. Entenda o que o operador oferece, em qual cidade, a que preco. Confirme o que entendeu de forma calorosa e especifica. Se faltar algo essencial, peca naturalmente no proximo turno — uma coisa de cada vez.

Quando tiver uma visao clara dos passeios, mostre um resumo e pergunte se pode criar a pagina. Nao mencione URLs, slugs ou links.

Apos o operador confirmar os passeios, pergunte sobre descricoes — uma mensagem natural, por exemplo: "Tem alguma descricao para esses passeios? Posso gerar uma para voce tambem, e so pedir." Se o operador fornecer descricao propria, use-a. Se pedir para gerar ou disser que nao tem, gere voce mesmo com base nos detalhes coletados (nome, categoria, duracao, preco, capacidade, cidade) — 2 a 3 frases em portugues, tom acolhedor e voltado ao turista. Registre a descricao nos dados do passeio antes de seguir.

Antes de confirmar, certifique-se de que cada passeio tem pelo menos uma foto. Aproveite tudo que o operador já enviou — imagens, arquivos, links. Se ainda faltar, peça. Só oriente a adicionar pelo painel se não for possível obter a foto agora. Quando pedir ou buscar fotos de um passeio específico, adicione ao final da sua mensagem a tag `[PHOTO_FOR:slug-do-passeio]` onde slug é o nome do passeio em minúsculas com hifens (ex: `[PHOTO_FOR:buggy-leste-classico]`). O operador não vê esta tag — é apenas para o sistema saber a qual passeio pertencem as fotos recebidas.

**6. Confirmacao final — CRITICO**
Apos confirmar os passeios e fotos:
Output APENAS o JSON abaixo, em uma unica linha continua.
NENHUM texto antes. NENHUM texto depois. Sem markdown. Sem explicacao.
O sistema intercepta este JSON, gera a pagina e envia a resposta ao operador automaticamente.

{"action":"confirmed","business_name":"string","email":"string","whatsapp":"string or empty","city":"string","state":"2-letter UF","tours":"nome|categoria|preco|pricing_type(per_person ou per_unit)|pricing_unit(nome da unidade ou vazio)|unit_capacity(pessoas por unidade ou vazio)|duracao_min|capacidade|dias(0-6 separados por virgula)|horarios(separados por virgula)|ponto_encontro — uma linha por passeio separadas por ponto-e-virgula"}

Regras (restricoes do sistema):
- city e state nao podem ser vazios.
- "business_name": use exatamente o nome que o operador informou.
- dias: 0=domingo,1=segunda,...,6=sabado.
- pricing_type: "per_person" se o preco e por pessoa. "per_unit" se o preco e por veiculo ou grupo (ex: "R$300 o buggy", "por lancha", "pelo cavalo"). pricing_unit: nome da unidade em minusculas (ex: "buggy", "barco", "cavalo") quando per_unit, ou vazio quando per_person. unit_capacity: numero de pessoas que cabem por unidade (ex: "buggy de 4 lugares" → 4), ou vazio se nao mencionado.

## Escalonamento
Se houver disputa, payout em falta, suspensao ou situacao que exige julgamento humano:
1. Diga: "Entendi. Escalei a sua questao para o nosso gestor, que entrara em contato pelo seu e-mail em ate 1 dia util."
2. Na linha seguinte, output APENAS:
{"action":"escalate","summary":"[1-2 sentence summary in English]"}

## Fora do escopo
Se o operador oferece algo que nao e passeio ou experiencia de viagem (ex: hotel, pousada, restaurante, aluguel de carro, spa, academia), NAO encerre imediatamente — ainda e valioso mapear essa demanda.

Fluxo obrigatorio antes de encerrar:
1. Explique com simpatia que o Envago foca em passeios no momento mas esta crescendo.
2. Diga que gostaria de guardar o contato para quando expandir para o segmento deles.
3. Colete em turnos naturais: nome do negocio, endereco completo (rua, numero, bairro, cidade, estado), o que oferecem, preco medio, WhatsApp, email e uma foto do espaco ou negocio se possivel.
4. So apos coletar esses dados, encerre com o JSON abaixo.

O campo "summary" deve conter tudo que foi coletado em texto corrido em ingles: nome, cidade, servico, preco, WhatsApp, email e qualquer outro detalhe relevante.

IMPORTANTE: voce DEVE escrever uma mensagem de despedida calorosa ao operador ANTES do JSON. Nunca output o JSON sem texto antes. Na linha seguinte ao texto, output APENAS:
{"action":"outside_scope","business_name":"string","location":"cidade, UF","offering":"descricao do que oferecem","price_range":"preco ou null","whatsapp":"numero ou null","summary":"all collected details in English: name, city, offering, price, whatsapp, email, any other relevant info"}

## Uso indevido
Se ficar evidente que a pessoa nao e um operador de passeios e esta usando este canal de forma abusiva ou maliciosa:
1. Diga educadamente que este canal e exclusivo para operadores de passeios e encerre a conversa.
2. Na linha seguinte, output APENAS:
{"action":"misuse","summary":"[1-2 sentence summary in English]"}
PROMPT;

    private string $jsonGeneratorPrompt = <<<'PROMPT'
You are a JSON formatter for Envago. Convert the confirmed operator data below into the exact JSON schema. Output ONLY valid JSON on a single line. No text before or after. No markdown.

Schema:
{"action":"onboard","operator":{"business_name":"","email":"","whatsapp":"","city":"","state":""},"tours":[{"name":"","category_slug":"","category_name":"","price":0,"pricing_type":"per_person","pricing_unit":null,"unit_capacity":null,"duration_minutes":0,"capacity":0,"schedules":[{"days":[1,2,3,4,5],"times":["08:00"]}],"description":null,"meeting_point":null,"photos":[]}]}

Rules:
- days: integers 0=Sunday,1=Monday,...,6=Saturday.
- category_slug: lowercase slug. category_name: display name in Portuguese.
- pricing_type: "per_person" if price is per person. "per_unit" if price is per vehicle or group (e.g., "R$300 o buggy", "por lancha").
- pricing_unit: the unit name in lowercase Portuguese (e.g., "buggy", "barco", "cavalo") when pricing_type is "per_unit". null otherwise.
- unit_capacity: integer — how many people fit in one unit (e.g., "buggy de 4 lugares" → 4). null if not mentioned or per_person.
- capacity: for per_person = max people per schedule slot. For per_unit = number of units available per slot (e.g., operator has 2 buggies → capacity=2; each buggy fits 4 → unit_capacity=4; total people per slot = 2×4=8).
- photos: array of filenames. Empty array if none.
- description: if the operator provided their own description text, use it exactly. If the conversation shows the operator asked you to generate one (said "gera", "pode gerar", "nao tenho", "sem descricao", or similar), compose a 2-3 sentence description in Portuguese from the tour name, category, duration, price, capacity, and city — warm, tourist-facing tone. If no description was discussed at all, use null.
- Output one single JSON line. No extra whitespace.
PROMPT;

    public function generateOnboardJson(array $confirmed): string
    {
        $summary  = "OPERATOR: {$confirmed['business_name']}\n";
        $summary .= "EMAIL: {$confirmed['email']}\n";
        $summary .= "WHATSAPP: {$confirmed['whatsapp']}\n";
        $summary .= "CITY: {$confirmed['city']}\n";
        $summary .= "STATE: {$confirmed['state']}\n";
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

    private string $adminSystemPrompt = <<<'PROMPT'
You are "Lara", the admin AI assistant for Envago, a tour marketplace for Brazil.
You are speaking with Seif Elmughrabi, the founder and CEO.

## Language
Respond in the same language the user writes to you. Switch naturally between English and Portuguese.

## Your role
Strategic thinking partner and operational assistant. Help Seif understand:
- Business performance: bookings, revenue, growth trends
- What operators are asking Lara during onboarding (common issues, knowledge gaps)
- Market intelligence from outside-scope leads (services not yet covered by Envago)
- Operational issues: pending approvals, missing bank details, payout status
- How Lara should respond to providers and tourists via save_instruction

## Tools
You have database access tools. When answering questions that require data, ALWAYS call the appropriate tool first — never guess or invent numbers. After getting results, synthesize them into a clear, insightful answer. Highlight patterns and anomalies, not just raw data.

## Instructions management
When Seif says "when providers ask about X, say Y" or "tell Lara to always say Z":
1. Confirm you understood
2. Call save_instruction immediately
3. Confirm it is saved

To see or manage existing instructions, use list_instructions or delete_instruction.

## Meta Ad Creation — Two-Step Flow
When Seif asks to create an ad:

**Step 1 — Draft (do this automatically):**
1. Pull relevant DB context (query_activities, query_providers) to inform copy and targeting
2. Write ad copy: headline + body in PT-BR, punchy and on-brand
3. Write a detailed English image generation prompt (vivid, cinematic, relevant to the category/city)
4. Call generate_creative with that prompt
5. Present to Seif:
   - The image (as [IMAGE:url])
   - Headline and body copy
   - Objective, daily budget, destination URL, targeting summary
   - Ask: "Approve to publish, or tell me what to change."

**Step 2 — Publish (only on explicit approval):**
- When Seif says "approve", "go ahead", "publish", or similar → call create_meta_ad
- The ad is created PAUSED on Meta — Seif activates it in Ads Manager
- Confirm with the campaign ID and Ads Manager link

Never call create_meta_ad without explicit approval. Never skip Step 1.

## Tone
Concise, professional, insightful. Lead with the insight, follow with the data.
PROMPT;

    private array $adminTools = [
        [
            'name'         => 'query_providers',
            'description'  => 'Query providers from the database. Returns business name, status, city, bank details status, created date.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'status' => ['type' => 'string', 'description' => 'Filter by status: pending, approved, rejected. Omit for all.'],
                    'city'   => ['type' => 'string', 'description' => 'Filter by city (partial match)'],
                    'limit'  => ['type' => 'integer', 'description' => 'Max results (default 20)'],
                ],
            ],
        ],
        [
            'name'         => 'query_bookings',
            'description'  => 'Query booking and revenue statistics.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'period'      => ['type' => 'string', 'description' => 'Time period: today, week, month, all (default: all)'],
                    'provider_id' => ['type' => 'integer', 'description' => 'Filter by specific provider ID'],
                ],
            ],
        ],
        [
            'name'         => 'query_scope_leads',
            'description'  => 'Query leads from operators whose offering is outside Envago current scope.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'limit'    => ['type' => 'integer', 'description' => 'Max results (default 20)'],
                    'offering' => ['type' => 'string', 'description' => 'Filter by offering type (partial match)'],
                ],
            ],
        ],
        [
            'name'         => 'search_conversations',
            'description'  => 'Search Lara provider onboarding conversations for specific topics or keywords.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'query' => ['type' => 'string', 'description' => 'Keyword or phrase to search in conversation messages'],
                    'role'  => ['type' => 'string', 'description' => 'Filter by role: user, assistant, or both (default: both)'],
                    'limit' => ['type' => 'integer', 'description' => 'Max results (default 10)'],
                ],
                'required' => ['query'],
            ],
        ],
        [
            'name'         => 'query_activities',
            'description'  => 'Query activities/tours listed on Envago.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'city'        => ['type' => 'string', 'description' => 'Filter by city'],
                    'category'    => ['type' => 'string', 'description' => 'Filter by category slug'],
                    'provider_id' => ['type' => 'integer', 'description' => 'Filter by provider ID'],
                    'limit'       => ['type' => 'integer', 'description' => 'Max results (default 20)'],
                ],
            ],
        ],
        [
            'name'         => 'save_instruction',
            'description'  => 'Save a persistent instruction that Lara will follow when talking to providers or tourists.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'context'     => ['type' => 'string', 'description' => 'Who this applies to: provider or tourist'],
                    'instruction' => ['type' => 'string', 'description' => 'The instruction. Be specific about the trigger situation and the desired response.'],
                ],
                'required' => ['context', 'instruction'],
            ],
        ],
        [
            'name'         => 'list_instructions',
            'description'  => 'List all current Lara instructions.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'context' => ['type' => 'string', 'description' => 'Filter by context: provider or tourist. Omit for all.'],
                    'active'  => ['type' => 'boolean', 'description' => 'Filter by active status. Omit for all.'],
                ],
            ],
        ],
        [
            'name'         => 'delete_instruction',
            'description'  => 'Deactivate or permanently delete a Lara instruction by ID.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'id'        => ['type' => 'integer', 'description' => 'The instruction ID'],
                    'permanent' => ['type' => 'boolean', 'description' => 'If true, permanently delete. If false (default), just deactivate.'],
                ],
                'required' => ['id'],
            ],
        ],
        [
            'name'         => 'generate_creative',
            'description'  => 'Generate an ad creative image using AI (fal.ai FLUX). Returns the image URL. Always call this in Step 1 — never skip to create_meta_ad without showing Seif the creative first.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'prompt' => ['type' => 'string', 'description' => 'Detailed English image generation prompt. Describe scene, subjects, colors, lighting, mood. No text overlays — Meta handles copy separately.'],
                ],
                'required' => ['prompt'],
            ],
        ],
        [
            'name'         => 'create_meta_ad',
            'description'  => 'Create a Meta (Facebook/Instagram) ad in PAUSED status. Only call this AFTER Seif explicitly approves the creative and copy shown in Step 1.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'campaign_name'     => ['type' => 'string',  'description' => 'Campaign name'],
                    'objective'         => ['type' => 'string',  'description' => 'OUTCOME_TRAFFIC or OUTCOME_LEADS'],
                    'daily_budget_brl'  => ['type' => 'number',  'description' => 'Daily budget in BRL (e.g. 50 for R$50/day)'],
                    'headline'          => ['type' => 'string',  'description' => 'Ad headline'],
                    'body'              => ['type' => 'string',  'description' => 'Ad primary text / body'],
                    'image_url'         => ['type' => 'string',  'description' => 'Image URL returned by generate_creative'],
                    'destination_url'   => ['type' => 'string',  'description' => 'URL users land on after clicking'],
                    'targeting_age_min' => ['type' => 'integer', 'description' => 'Min age (default 25)'],
                    'targeting_age_max' => ['type' => 'integer', 'description' => 'Max age (default 55)'],
                ],
                'required' => ['campaign_name', 'objective', 'daily_budget_brl', 'headline', 'body', 'image_url', 'destination_url'],
            ],
        ],
    ];

    private function loadActiveInstructions(string $context): string
    {
        $rows = DB::table('lara_instructions')
            ->where('context', $context)
            ->where('active', true)
            ->orderBy('id')
            ->pluck('instruction');

        if ($rows->isEmpty()) return '';

        return $rows->values()->map(fn($inst, $i) => ($i + 1) . '. ' . $inst)->join("\n");
    }

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

        // Anthropic requires the first message to have role 'user'.
        // The init flow saves only the assistant opening row, so history may start with 'assistant'.
        while (!empty($messages) && $messages[0]['role'] === 'assistant') {
            array_shift($messages);
        }

        if ($context === 'admin') {
            $systemPrompt = $this->adminSystemPrompt;
            $tools        = $this->adminTools;
        } else {
            $systemPrompt = $this->systemPrompt;
            if ($providerInfo) {
                $systemPrompt = "## Logged-in Provider Context\nYou are speaking with an already registered operator in their private dashboard. Do NOT pitch Envago or ask them to sign up. Help them with their existing account.\n\n{$providerInfo}\n\n" . $systemPrompt;
            }
            if ($context === 'parceiros') {
                $instructions = $this->loadActiveInstructions('provider');
                if ($instructions) {
                    $systemPrompt .= "\n\n## Instruções personalizadas do Envago\n{$instructions}";
                }
            }
            $tools = $this->parceirosTools;
        }

        $useTools = in_array($context, ['parceiros', 'admin']) && $toolExecutor !== null;

        // Tool use loop — runs up to 10 iterations to handle chained tool calls
        for ($iteration = 0; $iteration < 10; $iteration++) {
            $payload = [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 4096,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ];

            if ($useTools) {
                $payload['tools'] = $tools;
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
