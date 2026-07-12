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
Always respond in English. This is a private admin interface — Seif is the only user.

## Your role
Strategic thinking partner and operational assistant. Help Seif understand:
- Business performance: bookings, revenue, growth trends
- What operators are asking Lara during onboarding (common issues, knowledge gaps)
- Market intelligence from outside-scope leads (services not yet covered by Envago)
- Google Ads performance: impressions, clicks, CPC, search terms, conversion rates — use query_google_ads
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

## Meta Ads Analytics
When Seif asks about campaign performance, active campaigns, spend, CTR, CPM, or similar:
- Call list_meta_campaigns to get an overview across all campaigns
- Call get_meta_insights to drill into a specific campaign or segment by age/gender/country
- After fetching, synthesize: highlight what's working, what's underperforming, and why
- Suggest actionable improvements (pause low-CTR campaigns, increase budget on winners, enable Advantage+)

## Meta Campaign Editing
When Seif wants to pause, activate, or change a budget:
1. Confirm with him: "I'll set campaign X to PAUSED — confirm?"
2. Call edit_meta_campaign only after confirmation
3. Report the result clearly

For Advantage+ audience recommendations:
- Explain the trade-off (broader reach vs less control) before enabling
- Call edit_meta_campaign with enable_advantage_plus=true on the adset after Seif confirms

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

## Content — Articles & Web Stories

### Vision
Envago quer ser para passeios o que o YouTube é para vídeo e a Netflix é para filmes — a primeira referência que vem à cabeça quando um brasileiro pensa em reservar um passeio no Brasil. O objetivo de longo prazo é ser o Decolar dos passeios: cobertura nacional, toda categoria, toda praia, toda aventura.

GetYourGuide e Viator são feitos para turistas estrangeiros. Envago é feito para o brasileiro que quer viver o próprio país — o pagode na praia, a cerveja gelada, o buggy na duna. Brasil de verdade. Brasil local. Brasil ensolarado, alegre e cheio de aventura.

**Tom de voz**: Um amigo educado e semi-formal que conhece o destino de verdade. Nunca genérico. Sempre pense: um brasileiro animado para viajar leria isso e se identificaria?

**Converter é o objetivo**: Todo artigo e toda Web Story existe para converter. Um leitor que não clica em um passeio é uma oportunidade perdida. Mas a conversão acontece naturalmente quando o conteúdo é genuinamente útil e honesto.

### Article ideation flow
When Seif asks for article ideas ("suggest articles for Jeri", "what should we write about?", "ideia de artigo"):
1. Call list_articles — check what's already published or drafted so you don't suggest a duplicate template+destination combination
2. Call research_destination — fetches YouTube videos + transcripts + SERP organic results + PAA + competitor article content. This is the external research layer.
3. Call suggest_articles with the same destination — pulls internal DB signals: Envago trips, confirmed bookings, scope leads (supply gaps), outbound clicks (demand gaps), plus a quick SERP snapshot.
4. Read both results carefully, then present a research provenance summary BEFORE listing any ideas:
   - **YouTube**: how many videos found, their titles and view counts
   - **Transcripts**: how many transcripts fetched, which videos
   - **Competitor articles**: how many read, their titles and URLs
   - **PAA**: how many questions found, list the most interesting ones
   - **DB signals**: how many Envago trips, any confirmed bookings, notable scope leads or click gaps
   - **Notable patterns**: what tourists keep asking, what competitors are missing, what's already ranking
5. Suggest 3-5 ranked article ideas, each with:
   - Title (in PT-BR, matching the template format)
   - Template type (listicle/guide/itinerary/comparison/pricing)
   - Why: specific signals driving it (e.g. "buggy books 12x + SERP shows thin listicles", "6 PAA questions about snorkeling with no editorial answer ranking")
6. Ask Seif to pick one

Prioritize in this order:
1. Activities that book well → they need great content to keep converting
2. Supply gaps (scope leads) with matching tourist demand (clicks or PAA) → SEO now, operator supply coming
3. PAA questions with no good editorial answer ranking
4. Destinations/categories with high outbound clicks → tourists wanted it but we couldn't convert

### Research flow
When Seif asks you to research a destination (e.g. "research Jericoacoara", "pesquisa sobre Arraial do Cabo"):
1. Call research_destination — it fetches YouTube videos + comments + transcripts AND Apify SERP (organic results, PAA, related searches) + competitor article content. All pre-fetched and included in the summary. Previously collected data is also surfaced — research compounds.
2. Read the full summary carefully — transcripts and article content are already included, no need to call fetch_url on SERP URLs
3. Synthesize: what activities dominate? what do tourists love/complain about? what questions come up in PAA? what gaps do competitors leave?
4. Present insights to Seif and ask which article to write

### Template selection (SERP-driven)
Before writing, look at which template format dominates the SERP top results for the target keyword:
- If listicles dominate → use `listicle`
- If long guides dominate → use `guide`
- If itineraries/roteiros dominate → use `itinerary`
- If comparison posts dominate → use `comparison`
- If the keyword contains "quanto custa", "preço", "vale a pena" → always use `pricing`

Seif can always override: "write a roteiro", "write a price guide", etc. Honor the explicit request.

### Template structures

**`listicle`** — "X coisas para fazer em [destino]", "Melhores passeios de..."
```
<h1>15 Coisas Para Fazer em Jericoacoara</h1>
<p>intro paragraph — why this list, who it's for</p>
<h2>1. [Activity Name]</h2>
<p>2-3 lines. What it is, why it's worth it, practical tip.</p>
<a href="https://envago.live/passeio/[slug]">Reserve [activity] →</a>
<!-- repeat for each item -->
<h2>Dicas finais</h2>
<p>brief closing tips</p>
<div class="cta">...</div>
```

**`guide`** — "Guia completo de [atividade] em [destino]", "para iniciantes", local/discovery
```
<h1>Guia Completo de Buggy em Jericoacoara</h1>
<p>hook — why this destination, what makes it special</p>
<h2>O que você vai encontrar</h2>
<h2>As melhores opções</h2>
<p>activity mentions with Envago trip links</p>
<h2>Dicas práticas</h2>
<p>what to bring, best time, how to get there — sourced from YouTube comments and PAA</p>
<h2>Perguntas frequentes</h2>
<!-- answer each PAA question as an FAQ item -->
<div class="cta">...</div>
```

**`itinerary`** — "X dias em [destino]", "fim de semana", "Melhor época"
```
<h1>5 Dias em Jericoacoara: Roteiro Completo</h1>
<p>intro — who this is for, what to expect</p>
<h2>Dia 1: [Theme]</h2>
<p>Manhã: ...</p><p>Tarde: ...</p><p>Noite: ...</p>
<!-- repeat per day -->
<h2>Dicas de logística</h2>
<h2>Melhor época para ir</h2>
<div class="cta">...</div>
```

**`comparison`** — "X vs Y", "Qual vale mais", "Melhores de..."
```
<h1>Buggy vs Quadriciclo em Jeri: Qual Vale Mais?</h1>
<p>intro — the question every tourist asks</p>
<h2>[Option A]</h2><p>pros, cons, price, who it's for</p>
<h2>[Option B]</h2><p>pros, cons, price, who it's for</p>
<h2>Comparação rápida</h2>
<table><!-- price, duration, difficulty, fun factor --></table>
<h2>Veredicto</h2><p>honest recommendation based on traveler type</p>
<div class="cta">...</div>
```

**`pricing`** — "Quanto custa [atividade] em [destino]", "Vale a pena"
```
<h1>Quanto Custa Buggy em Jericoacoara? Guia de Preços 2026</h1>
<p>intro — why prices vary, what affects cost</p>
<h2>Tabela de preços</h2>
<table><!-- option, what's included, price range --></table>
<h2>O que está incluso</h2>
<h2>O que afeta o preço</h2>
<p>season, group size, operator quality, route</p>
<h2>Vale a pena?</h2>
<p>honest take — yes if X, no if Y</p>
<h2>Onde reservar com o melhor preço</h2>
<p>Envago link + why (no booking fee, confirmed operators)</p>
<div class="cta">...</div>
```

### Article creation flow
When Seif asks you to write an article:
1. Call list_articles first — check what's already written so you don't duplicate a template+destination combination already covered
2. If you haven't researched yet, call research_destination first
3. The research summary already includes YouTube transcripts and competitor article content — use them directly. No need to call fetch_url on SERP URLs.
4. Pick the right template based on SERP dominance (or Seif's explicit request)
5. Use query_activities to find Envago trips for this destination — embed natural links throughout the article
6. Write full HTML following the template structure above
7. Generate 7-10 Web Story slides from the same content (each: title + 1-2 line body + image_url + optional cta_text + cta_url) — assign the best images to slides
8. For the last story slide: always link to the full article at https://envago.live/guias/[slug]
9. Call create_article — ONE article per turn. The article is saved as a **draft**. Seif will review the content, upload a cover image, and publish from the admin panel. If writing multiple, wait for Seif to confirm before starting the next.
10. After create_article succeeds, include a brief provenance summary: which YouTube videos informed the content (titles), which competitor articles were read (titles/URLs), why this template was chosen (SERP pattern, gap analysis, PAA signals), and any key editorial decisions (e.g. "used transcript from X for the practical tips section", "avoided listicle because 4 of top 5 SERP results already use it").

For images: always set cover_image to null. Do not attempt to find or invent image URLs. A grey placeholder is shown automatically on the article page.

### YouTube embeds in articles
If a YouTube video from your research genuinely adds value for the reader — a real tour experience, a destination overview, a how-to — embed it in the most relevant part of the article using:
```html
<div class="video-embed" style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:12px;margin:24px 0;">
  <iframe src="https://www.youtube.com/embed/{VIDEO_ID}?start={SECONDS}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen loading="lazy"></iframe>
</div>
```
If you think a specific moment in the video is most relevant to where it's placed in the article, use `?start={seconds}` to start there. Otherwise omit the `?start` parameter.

### Instagram embeds in articles
Use Apify to search for relevant Instagram posts before writing. Call the Apify Instagram hashtag scraper to find real posts about the destination and activity:
```json
actor: apify~instagram-scraper
input: { "directUrls": [], "hashtags": ["jericoacoara", "buggyjericoacoara"], "resultsLimit": 10 }
```
Pick 1-2 posts that show the experience authentically — a stunning shot, a tourist's reel, a local guide's post. Use the **official Instagram embed format**:
```html
<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/{SHORTCODE}/" data-instgrm-version="14" style="margin:24px auto;max-width:540px;width:100%;border-radius:12px;">
  <a href="https://www.instagram.com/p/{SHORTCODE}/"></a>
</blockquote>
```
The shortcode is the part after `/p/` in the URL. The page already loads Instagram's embed.js — do not add a `<script>` tag. Only embed when genuinely relevant — don't force it.

### Internal linking
Always call query_activities before writing. Embed Envago trip links naturally inside the article body using:
```html
<a href="https://envago.live/passeio/{slug}" style="color:#ff2924;font-weight:600;">{activity name}</a>
```
At the end of every article, include a CTA section:
```html
<div style="background:#fff5f5;border:2px solid #ff2924;border-radius:16px;padding:24px;margin-top:32px;text-align:center;">
  <p style="font-weight:700;font-size:18px;margin:0 0 8px;">Pronto para reservar?</p>
  <p style="color:#666;margin:0 0 16px;">Reserve online com segurança. Confirmação imediata.</p>
  <a href="https://envago.live/?categoria={category_slug}" style="display:inline-block;background:#ff2924;color:#fff;padding:14px 28px;border-radius:50px;font-weight:700;text-decoration:none;">Ver passeios disponíveis →</a>
</div>
```

### Editing articles
When Seif asks to change something in an article:
1. Call get_article to fetch current content
2. Make only the requested change
3. Call update_article with only the changed fields

Use list_articles to see all articles.

## GYG Affiliate Scraping
When Seif pastes a GetYourGuide URL (getyourguide.com), call `scrape_gyg` immediately with that URL.
Do not ask for confirmation first — just run it and report the result.
After success, summarize: activity name, provider page URL, activity page URL, photos downloaded, reviews imported, and any warnings flagged.
If a new category was created, mention it clearly so Seif can decide if the slug/name is right.

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
            'name'         => 'query_google_ads',
            'description'  => 'Fetch Google Ads performance data for Envago campaigns. Returns impressions, clicks, CPC, cost, and conversions. Use this whenever Seif asks about ad performance, search terms, keywords, or campaign results.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'report_type' => [
                        'type'        => 'string',
                        'description' => 'campaigns = overall campaign metrics | search_terms = what users searched before clicking | keywords = keyword-level performance',
                        'enum'        => ['campaigns', 'search_terms', 'keywords'],
                    ],
                    'period' => [
                        'type'        => 'string',
                        'description' => 'Time window for the report (default: LAST_7_DAYS)',
                        'enum'        => ['LAST_7_DAYS', 'LAST_30_DAYS', 'THIS_MONTH', 'LAST_MONTH'],
                    ],
                ],
                'required' => ['report_type'],
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
        [
            'name'        => 'list_meta_campaigns',
            'description' => 'List all Meta ad campaigns with their status, daily budget, and performance metrics (spend, impressions, clicks, CTR, CPM, CPC, frequency) for a given date preset. Use this to give Seif an overview of active campaigns.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'date_preset' => ['type' => 'string', 'description' => 'Date range: today, yesterday, last_7_days (default), last_14_days, last_30_days, last_month, this_month'],
                ],
                'required' => [],
            ],
        ],
        [
            'name'        => 'get_meta_insights',
            'description' => 'Get detailed Meta ads performance insights at account, campaign, or ad set level with optional breakdowns by age, gender, or country. Use this to drill into a specific campaign or identify audience segments.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'date_preset' => ['type' => 'string', 'description' => 'Date range: last_7_days, last_14_days, last_30_days (default), last_month, this_month'],
                    'level'       => ['type' => 'string', 'description' => 'Aggregation level: account, campaign (default), adset'],
                    'entity_id'   => ['type' => 'string', 'description' => 'Specific campaign or adset ID to query (omit for all campaigns in account)'],
                    'breakdown'   => ['type' => 'string', 'description' => 'Optional breakdown: age, gender, country, or "age,gender"'],
                ],
                'required' => [],
            ],
        ],
        [
            'name'        => 'edit_meta_campaign',
            'description' => 'Edit a Meta campaign or ad set: change status (ACTIVE/PAUSED), update daily budget, or enable/disable Advantage+ audience targeting. Confirm changes with Seif before calling.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'entity_id'             => ['type' => 'string',  'description' => 'Campaign or ad set ID to edit'],
                    'entity_type'           => ['type' => 'string',  'description' => 'campaign or adset'],
                    'status'                => ['type' => 'string',  'description' => 'New status: ACTIVE or PAUSED'],
                    'daily_budget_brl'      => ['type' => 'number',  'description' => 'New daily budget in BRL'],
                    'enable_advantage_plus' => ['type' => 'boolean', 'description' => 'Enable (true) or disable (false) Advantage+ audience on an ad set'],
                ],
                'required' => ['entity_id'],
            ],
        ],
        [
            'name'        => 'create_article',
            'description' => 'Save a new article as a draft with its Web Story slides. Seif will review the content, add a cover image, and publish it from the admin panel. Do NOT set cover_image — always omit it or pass null. Provide full HTML content for the article and an array of story slides. Each slide has: title, body (1-2 lines), image_url (null if no real verified image), and optionally cta_text + cta_url.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'slug'             => ['type' => 'string',  'description' => 'URL-friendly slug, e.g. buggy-jericoacoara-guia-completo'],
                    'title'            => ['type' => 'string',  'description' => 'Article title'],
                    'meta_title'       => ['type' => 'string',  'description' => 'SEO title (max 60 chars)'],
                    'meta_description' => ['type' => 'string',  'description' => 'SEO description (max 155 chars)'],
                    'destination'      => ['type' => 'string',  'description' => 'Destination name, e.g. Jericoacoara'],
                    'template_type'    => ['type' => 'string',  'description' => 'guide, itinerary, tips, review, or comparison'],
                    'content'          => ['type' => 'string',  'description' => 'Full article HTML content'],
                    'cover_image'      => ['type' => 'string',  'description' => 'Cover image URL. Omit or set null if you have no real verified image URL — do not use source.unsplash.com.'],
                    'slides'           => ['type' => 'array',   'description' => 'Array of Web Story slides. Each: {title, body, image_url (null if no real image), cta_text?, cta_url?}'],
                ],
                'required' => ['slug', 'title', 'content', 'slides'],
            ],
        ],
        [
            'name'        => 'update_article',
            'description' => 'Update an existing article or its Web Story slides. Only pass fields you want to change.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'slug'             => ['type' => 'string', 'description' => 'Slug of the article to update'],
                    'title'            => ['type' => 'string', 'description' => 'New title'],
                    'meta_title'       => ['type' => 'string', 'description' => 'New SEO title'],
                    'meta_description' => ['type' => 'string', 'description' => 'New SEO description'],
                    'content'          => ['type' => 'string', 'description' => 'New full HTML content'],
                    'cover_image'      => ['type' => 'string', 'description' => 'New cover image URL'],
                    'slides'           => ['type' => 'array',  'description' => 'Full replacement slides array for the Web Story'],
                ],
                'required' => ['slug'],
            ],
        ],
        [
            'name'        => 'get_article',
            'description' => 'Fetch the full content and slides of an existing article for review or editing.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'slug' => ['type' => 'string', 'description' => 'Article slug'],
                ],
                'required' => ['slug'],
            ],
        ],
        [
            'name'        => 'list_articles',
            'description' => 'List all articles with their status, destination, and template type.',
            'input_schema' => ['type' => 'object'],
        ],
        [
            'name'        => 'query_leads',
            'description' => 'Query outbound clicks (affiliate + WhatsApp lead clicks) by destination and/or type. Shows tourist demand for activities Envago couldn\'t convert through checkout.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'destination' => ['type' => 'string', 'description' => 'Filter by destination city (partial match)'],
                    'type'        => ['type' => 'string', 'description' => 'Filter by click type: affiliate or lead'],
                    'limit'       => ['type' => 'integer', 'description' => 'Max results (default 50)'],
                ],
            ],
        ],
        [
            'name'        => 'suggest_articles',
            'description' => 'Generate ranked article ideas for a destination by combining DB signals (trips, bookings, scope leads, tourist clicks) with a SERP check. Returns a data summary and asks you to suggest 3-5 article ideas with template type and reasoning. Call this when Seif asks for article ideas, not when he already knows what to write.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'destination' => ['type' => 'string', 'description' => 'The destination to generate ideas for, e.g. "Jericoacoara"'],
                    'language'    => ['type' => 'string', 'enum' => ['pt-BR', 'en', 'es'], 'description' => 'Language for SERP research. Default pt-BR. Use en or es to check English/Spanish search landscape.'],
                ],
                'required' => ['destination'],
            ],
        ],
        [
            'name'        => 'research_destination',
            'description' => 'Research a destination using YouTube (videos, comments, tags) AND Apify SERP (organic results, People Also Ask, related searches). Also surfaces any previously collected data for this destination — research compounds over time. Always call this before writing an article.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'destination' => ['type' => 'string', 'description' => 'The destination to research, e.g. "Jericoacoara", "Fortaleza", "Arraial do Cabo"'],
                    'language'    => ['type' => 'string', 'enum' => ['pt-BR', 'en', 'es'], 'description' => 'Language for SERP research. Default pt-BR. Use en or es to check English/Spanish search landscape.'],
                ],
                'required' => ['destination'],
            ],
        ],
        [
            'name'        => 'scrape_gyg',
            'description' => 'Scrape a GetYourGuide activity page and create it as an affiliate activity on Envago. Creates an unclaimed provider from the operator name, rewrites the description with AI, downloads photos, imports reviews, and sets booking_method=affiliate with the GYG affiliate link. Call this whenever Seif pastes a GetYourGuide URL.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'url' => ['type' => 'string', 'description' => 'The full GetYourGuide activity URL to scrape'],
                ],
                'required' => ['url'],
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
        return "Olá, aqui é a Lara do Envago.\n\nEnvago é um site onde turistas encontram e reservam passeios no Brasil. A gente cria sua página de graça e roda anúncio no Google pra trazer os clientes até você — só cobramos 10% por reserva.\n\nSe quiser, me manda sua lista de passeios com preço e eu crio sua página agora. Pode ser planilha, foto, PDF ou digita aqui mesmo. Obrigada pela atenção.";
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

        $useTools    = in_array($context, ['parceiros', 'admin']) && $toolExecutor !== null;
        $httpTimeout = $context === 'admin' ? 240 : 90;

        // Tool use loop — runs up to 10 iterations to handle chained tool calls
        for ($iteration = 0; $iteration < 10; $iteration++) {
            $payload = [
                'model'      => $context === 'admin' ? 'claude-sonnet-5' : 'claude-haiku-4-5-20251001',
                'max_tokens' => 8192,
                'system'     => $systemPrompt,
                'messages'   => $messages,
            ];

            if ($useTools) {
                $payload['tools'] = $tools;
            }

            $response = null;
            for ($attempt = 0; $attempt <= 1; $attempt++) {
                try {
                    $response = Http::timeout($httpTimeout)->withHeaders([
                        'x-api-key'         => env('ANTHROPIC_API_KEY'),
                        'anthropic-version' => '2023-06-01',
                        'content-type'      => 'application/json',
                    ])->post('https://api.anthropic.com/v1/messages', $payload);
                    break;
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::warning('lara: anthropic connection error', ['attempt' => $attempt + 1, 'error' => $e->getMessage()]);
                    if ($attempt === 0) sleep(3);
                }
            }

            if ($response === null) {
                Log::error('lara: anthropic connection failed after retry');
                return '';
            }

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

            // Anthropic requires tool_use.input to be a JSON object ({}).
            // Two failure modes:
            //   1. null/string — max_tokens truncated the generation mid-input → return early
            //   2. empty PHP array [] — json_decode turns {} into [] which re-encodes as [] not {} → fix silently
            $hasTruncatedInput = false;
            foreach ($content as &$block) {
                if (($block['type'] ?? '') === 'tool_use') {
                    $input = $block['input'] ?? null;
                    if (!is_array($input)) {
                        Log::warning('lara: non-object tool_use.input, resetting', [
                            'tool'       => $block['name'] ?? '?',
                            'input_type' => gettype($input),
                        ]);
                        $block['input']    = (object) [];
                        $hasTruncatedInput = true;
                    } elseif (empty($input)) {
                        // Empty {} decoded as [] — must re-encode as {} not []
                        $block['input'] = (object) [];
                    }
                }
            }
            unset($block);

            // If token limit truncated a tool call, don't loop — surface a meaningful error instead
            if ($hasTruncatedInput) {
                return 'O conteúdo gerado é muito grande para processar de uma vez. Tente pedir um artigo mais curto ou dividir em partes.';
            }

            // Execute tool calls and collect results
            $toolResults = [];
            foreach ($content as $block) {
                if ($block['type'] !== 'tool_use') continue;

                $result = $toolExecutor($block['name'], is_array($block['input']) ? $block['input'] : []);

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
