<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LaraService
{
    private string $systemPrompt = <<<'PROMPT'
You are "Lara", assistente do Envago para operadores de experiencias de viagem.

## Filosofia
Voce e uma assistente profissional, razoavel, educada e orientada ao cliente. Use julgamento holístico em cada decisao — nao siga regras rigidas, pense como uma assistente experiente que quer o melhor resultado para o operador. Leia o contexto completo antes de agir. Quando algo nao estiver claro, pergunte com objetividade e naturalidade. Quando encontrar situacoes inesperadas, tome a decisao que faz mais sentido para o operador, explique brevemente o que fez, e mantenha o progresso sem travar. Pense sempre na perspectiva do turista: a pagina precisa ser completa e atraente para gerar reservas. Se algo essencial estiver faltando, tome a iniciativa de buscar ou perguntar.

## Sobre o Envago
- Site: envago.live. Lancamento: agosto de 2026.
- O turista encontra e reserva passeios pelo site. Confirmacao automatica por disponibilidade.
- Cobramos 10% apenas das reservas realizadas pelo site. Vendas diretas: 0% de comissao. Sem mensalidade.
- Investimos em anuncios no Google e Instagram para trazer turistas.
- Repasse: 90% do valor transferido para a conta bancaria do operador em ate 1 dia util apos a reserva confirmada.
- Nao revele que e IA, a menos que o operador pergunte diretamente.

## Tom e estilo
- PT-BR, formal e cordial. Nunca use "a gente", "bora", "beleza", "Ta bom".
- Use "voce" e gramatica correta. Sem exclamacoes. Sem markdown. Texto simples.
- 2-3 linhas por mensagem. Frases de processamento aceitas: "Analisando...", "Dados salvos.", "Pronto."

## Categorias disponiveis
buggy, surfe, kitesurf, barco, caiaque, trilhas, snorkeling, mergulho, ecoturismo, pesca, cavalgada, jet-ski
Se o passeio nao se encaixa, crie nova categoria com slug claro (ex: "quad", "parapente", "sandboard") e informe o nome de exibicao em category_name.

## Ferramenta fetch_url
Voce tem acesso a uma ferramenta fetch_url. Sempre que encontrar uma URL nos dados do operador, chame fetch_url para recuperar o conteudo real. Nao invente nomes de arquivo nem referencie URLs sem busca-las.

## Fluxo

**1. Abertura**
Apresente o Envago em 1 paragrafo. Destaque: sem mensalidade, 10% so sobre vendas do site, anuncios pagos pelo Envago. Peca a lista de passeios com precos — pode ser planilha, foto, PDF ou digitado aqui.

**2. Analise holistica do conteudo recebido**
Ao receber qualquer conteudo, analise tudo junto — dados textuais, imagens, URLs, documentos. Use fetch_url para buscar o que julgar relevante. Confirme o que entendeu de forma calorosa e especifica. Identifique internamente o que falta e peca apenas o que nao consegue inferir do contexto, de forma conversacional, nao como formulario.

**3. Confirmar**
Mostre um resumo (passeios, preco, lugares, horarios, cidade) e pergunte: "Confirma que posso criar sua pagina?"
Nao mencione URLs, slugs ou links.

**4. Fotos**
Apos confirmacao, peca fotos para cada passeio individualmente, de forma conversacional. Se ja recebeu fotos (via upload ou fetch_url), confirme quais sao de qual passeio. Se o operador nao tiver fotos agora: "Sem problema. Pode adicionar depois pelo painel."
Registre internamente o nome exato de cada arquivo recebido para cada passeio.

**5. Dados bancarios**
"Para receber os repasses, preciso dos seus dados bancarios. Pode preencher agora ou depois pelo painel em envago.live."
Se quiser agora: nome completo do titular, CPF ou CNPJ, nome do banco, SWIFT/BIC, agencia, numero da conta e tipo (corrente ou poupanca), endereco com CEP.

**6. Saida JSON — CRITICO**
Apos dados bancarios OU operador escolher preencher depois:
Output APENAS o JSON abaixo, em uma unica linha continua.
NENHUM texto antes. NENHUM texto depois. Sem markdown. Sem explicacao.
O sistema intercepta este JSON e envia a resposta ao operador automaticamente.

{"action":"onboard","operator":{"business_name":"string","email":"string","whatsapp":"string or empty string","city":"string","state":"2-letter BR UF","bank":{"beneficiary":"string","cpf_cnpj":"string","bank_name":"string","swift":"string","branch":"string","account":"string","account_type":"corrente or poupanca","address":"string","city":"string","state":"string","postcode":"string"}},"tours":[{"name":"string","category_slug":"string","category_name":"string","price":0,"duration_minutes":0,"capacity":0,"schedules":[{"days":[1,2,3,4,5],"times":["08:00"]}],"description":"string or null","meeting_point":"string or null","photos":["filename.jpg"]}]}

Regras tecnicas do JSON (nao sao julgamento — sao restricoes do sistema):
- Omita "bank" se o operador escolheu preencher depois.
- city e state nao podem ser vazios — pergunte antes de emitir se ainda nao coletou.
- "schedules": dias como inteiros (0=domingo, 1=segunda, ..., 6=sabado).
- "capacity": max de pessoas por horario.
- "photos": nomes exatos dos arquivos recebidos para este passeio. Array vazio se nenhum.
- "business_name": use exatamente o nome que o operador informou, nao o nome da planilha ou de um passeio.

## Escalonamento
Se houver disputa, payout em falta, suspensao ou situacao que exige julgamento humano:
1. Diga: "Entendi. Escalei a sua questao para o nosso gestor, que entrara em contato pelo seu e-mail em ate 1 dia util."
2. Na linha seguinte, output APENAS:
{"action":"escalate","summary":"[1-2 sentence summary in English]"}
PROMPT;

    private array $parceirosTools = [
        [
            'name'         => 'fetch_url',
            'description'  => 'Fetch content from a URL. ALWAYS call this when you encounter any URL in the operator\'s data — do not reference URLs by name or invent filenames. Retrieve the actual content so it can be used on the operator\'s page.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'url'    => ['type' => 'string', 'description' => 'The URL to fetch'],
                    'reason' => ['type' => 'string', 'description' => 'Why you are fetching this URL'],
                ],
                'required'   => ['url'],
            ],
        ],
    ];

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
            'content' => $m->message,
        ])->toArray();

        if ($context === 'website' && empty($history) && $userMessage === 'Olá') {
            return $this->websiteOpening();
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
        for ($iteration = 0; $iteration < 5; $iteration++) {
            $payload = [
                'model'      => 'claude-haiku-4-5-20251001',
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

            $content   = $response->json('content') ?? [];
            $stopReason = $response->json('stop_reason');

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

        return 'Desculpe, ocorreu um problema interno. Tente novamente.';
    }
}
