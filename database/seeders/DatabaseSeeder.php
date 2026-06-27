<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Provider;
use App\Models\Category;
use App\Models\Activity;
use App\Models\Media;
use App\Models\Review;
use App\Models\Schedule;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    private array $reviewPool = [
        ['name' => 'Ana Lima',        'rating' => 5, 'body' => 'Experiência incrível! Valeu cada centavo. O guia foi super atencioso e profissional.'],
        ['name' => 'Carlos Mendes',   'rating' => 5, 'body' => 'Melhor passeio que fiz em Jeri. Recomendo muito para toda a família.'],
        ['name' => 'Fernanda Costa',  'rating' => 4, 'body' => 'Muito bom! A organização foi impecável. Voltarei com certeza.'],
        ['name' => 'João Silva',      'rating' => 5, 'body' => 'Simplesmente sensacional. O pôr do sol nas dunas foi inesquecível.'],
        ['name' => 'Mariana Santos',  'rating' => 4, 'body' => 'Ótimo passeio, guia muito experiente e simpático. Recomendo!'],
        ['name' => 'Pedro Oliveira',  'rating' => 5, 'body' => 'Uma das melhores experiências da minha vida. Natureza deslumbrante.'],
        ['name' => 'Juliana Rocha',   'rating' => 5, 'body' => 'Tudo perfeito — equipamento, guia, pontualidade. 10/10.'],
        ['name' => 'Rafael Torres',   'rating' => 4, 'body' => 'Muito profissional e seguro. Adorei cada momento do passeio.'],
        ['name' => 'Beatriz Alves',   'rating' => 5, 'body' => 'Passeio maravilhoso! As paisagens são de tirar o fôlego.'],
        ['name' => 'Lucas Ferreira',  'rating' => 4, 'body' => 'Ótimo custo-benefício. Vale muito a pena. Voltarei!'],
        ['name' => 'Camila Souza',    'rating' => 5, 'body' => 'Experiência única. O guia conhece cada detalhe da região.'],
        ['name' => 'Thiago Nunes',    'rating' => 3, 'body' => 'Bom passeio, mas o horário atrasou um pouco. No geral, gostei.'],
        ['name' => 'Larissa Gomes',   'rating' => 5, 'body' => 'Superou todas as expectativas. Jeri é mágica e o passeio também.'],
        ['name' => 'Rodrigo Pinto',   'rating' => 4, 'body' => 'Muito bem organizado. Guia com ótimo conhecimento da fauna local.'],
        ['name' => 'Isabela Ramos',   'rating' => 5, 'body' => 'Voltei para casa apaixonada por Jeri. Passeio imperdível!'],
    ];

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'moghrabi@gmail.com'],
            ['name' => 'Seif', 'password' => Hash::make('admin123'), 'role' => 'admin', 'email_verified_at' => now()]
        );

        $this->operator('João Buggy', 'joao@buggy.com', 'Buggy Jeri do João', 'buggy-jeri-do-joao', '+55 88 99901-0001', [
            ['category' => 'buggy',        'name' => 'Buggy nas Dunas',        'slug' => 'buggy-nas-dunas',        'meeting_point' => 'Estacionamento da Duna do Pôr do Sol, Jericoacoara', 'description' => 'Passeio pelas dunas da Pedra Furada e pôr do sol.', 'price' => 180.00, 'duration' => 180, 'capacity' => 4, 'time' => '08:00:00'],
            ['category' => 'buggy',        'name' => 'Buggy Lagoa Azul',       'slug' => 'buggy-lagoa-azul',       'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Roteiro completo pela Lagoa Azul e Lagoa do Paraíso.', 'price' => 220.00, 'duration' => 240, 'capacity' => 4, 'time' => '14:00:00'],
            ['category' => 'sand-boarding','name' => 'Sandboard nas Dunas',    'slug' => 'sandboard-dunas',        'meeting_point' => 'Base da Duna do Pôr do Sol, Jericoacoara', 'description' => 'Adrenalina pura descendo as dunas de sandboard.', 'price' => 60.00, 'duration' => 60, 'capacity' => 6, 'time' => '10:00:00'],
        ]);

        $this->operator('Marina Kite', 'marina@kite.com', 'Jeri Kite School', 'jeri-kite-school', '+55 88 99902-0002', [
            ['category' => 'kitesurf',     'name' => 'Kitesurf Iniciante',     'slug' => 'kitesurf-iniciante-jeri','meeting_point' => 'Quiosque 7 — Praia de Jericoacoara', 'description' => 'Aula individual na melhor escola de kite do Brasil.', 'price' => 350.00, 'duration' => 120, 'capacity' => 2, 'time' => '09:00:00'],
            ['category' => 'surfe',        'name' => 'Aula de Surfe',          'slug' => 'aula-surfe-jeri',        'meeting_point' => 'Quiosque 7 — Praia de Jericoacoara', 'description' => 'Aprenda a surfar nas ondas perfeitas de Jericoacoara.', 'price' => 200.00, 'duration' => 90, 'capacity' => 4, 'time' => '07:00:00'],
        ]);

        $this->operator('Carlos Barco', 'carlos@barco.com', 'Passeios de Barco Jeri', 'passeios-de-barco-jeri', '+55 88 99903-0003', [
            ['category' => 'barco',        'name' => 'Barco Pedra Furada',     'slug' => 'barco-pedra-furada-jeri','meeting_point' => 'Pier Central de Jericoacoara', 'description' => 'Passeio pela costa de Jeri até a Pedra Furada.', 'price' => 120.00, 'duration' => 150, 'capacity' => 8, 'time' => '08:30:00'],
            ['category' => 'snorkeling',   'name' => 'Snorkeling Lagoa Azul',  'slug' => 'snorkeling-lagoa-azul',  'meeting_point' => 'Lagoa Azul — entrada principal, Jericoacoara', 'description' => 'Mergulho com snorkel na cristalina Lagoa Azul.', 'price' => 80.00, 'duration' => 90, 'capacity' => 8, 'time' => '10:00:00'],
            ['category' => 'mergulho',     'name' => 'Mergulho com Cilindro',  'slug' => 'mergulho-cilindro-jeri', 'meeting_point' => 'Pier Central de Jericoacoara', 'description' => 'Mergulho autônomo para iniciantes e avançados.', 'price' => 320.00, 'duration' => 180, 'capacity' => 4, 'time' => '09:00:00'],
        ]);

        $this->operator('Ana Eco', 'ana@eco.com', 'Eco Jeri Tours', 'eco-jeri-tours', '+55 88 99904-0004', [
            ['category' => 'ecoturismo',   'name' => 'Tour Ecoturismo Jeri',   'slug' => 'ecoturismo-jeri',        'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Conheça a fauna e flora da APA de Jericoacoara.', 'price' => 90.00, 'duration' => 180, 'capacity' => 8, 'time' => '07:30:00'],
            ['category' => 'trilhas',      'name' => 'Trilha Pedra Furada',    'slug' => 'trilha-pedra-furada',    'meeting_point' => 'Pedra Furada — base da trilha, Jericoacoara', 'description' => 'Trilha guiada até a icônica Pedra Furada.', 'price' => 70.00, 'duration' => 240, 'capacity' => 10, 'time' => '06:00:00'],
            ['category' => 'bird-watching','name' => 'Observação de Aves',     'slug' => 'bird-watching-jeri',     'meeting_point' => 'Lagoa do Paraíso — entrada principal, Jericoacoara', 'description' => 'Birding nas lagoas e mangues de Jericoacoara.', 'price' => 110.00, 'duration' => 180, 'capacity' => 6, 'time' => '05:30:00'],
            ['category' => 'camping',      'name' => 'Camping nas Dunas',      'slug' => 'camping-dunas-jeri',     'meeting_point' => 'Base da Duna do Pôr do Sol, Jericoacoara', 'description' => 'Noite nas dunas sob o céu estrelado.', 'price' => 150.00, 'duration' => 720, 'capacity' => 8, 'time' => '17:00:00'],
        ]);

        $this->operator('Pedro Cavalo', 'pedro@cavalo.com', 'Cavalos de Jeri', 'cavalos-de-jeri', '+55 88 99905-0005', [
            ['category' => 'cavalgada',    'name' => 'Cavalgada Pôr do Sol',   'slug' => 'cavalgada-por-do-sol',   'meeting_point' => 'Recepção da Pousada Vila Kalango, Jericoacoara', 'description' => 'Cavalgada na praia ao pôr do sol, inesquecível.', 'price' => 130.00, 'duration' => 90, 'capacity' => 6, 'time' => '16:30:00'],
            ['category' => 'pesca',        'name' => 'Pesca Esportiva',        'slug' => 'pesca-esportiva-jeri',   'meeting_point' => 'Pier Central de Jericoacoara', 'description' => 'Pesca em alto mar com barco equipado.', 'price' => 200.00, 'duration' => 300, 'capacity' => 5, 'time' => '05:00:00'],
            ['category' => 'caiaque',      'name' => 'Caiaque Lagoa do Paraíso','slug' => 'caiaque-lagoa-paraiso', 'meeting_point' => 'Lagoa do Paraíso — entrada principal, Jericoacoara', 'description' => 'Remada tranquila nas águas cristalinas.', 'price' => 85.00, 'duration' => 120, 'capacity' => 6, 'time' => '09:00:00'],
        ]);

        $this->operator('Rafael Adrenalina', 'rafael@adrenalina.com', 'Adrenalina Jeri', 'adrenalina-jeri', '+55 88 99906-0006', [
            ['category' => 'jet-ski',       'name' => 'Jet Ski Jericoacoara',  'slug' => 'jet-ski-jeri',           'meeting_point' => 'Quiosque 7 — Praia de Jericoacoara', 'description' => 'Alta velocidade nas águas de Jeri.', 'price' => 160.00, 'duration' => 30, 'capacity' => 2, 'time' => '10:00:00'],
            ['category' => 'parasailing',   'name' => 'Parasail sobre o Mar',  'slug' => 'parasail-jeri',          'meeting_point' => 'Estacionamento da Duna do Pôr do Sol, Jericoacoara', 'description' => 'Voar sobre o mar preso a um paraquedas.', 'price' => 250.00, 'duration' => 30, 'capacity' => 2, 'time' => '11:00:00'],
            ['category' => 'bungee-jumping','name' => 'Bungee Jump',           'slug' => 'bungee-jump-jeri',       'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'O salto de bungee mais emocionante do Ceará.', 'price' => 190.00, 'duration' => 60, 'capacity' => 4, 'time' => '14:00:00'],
            ['category' => 'sky-diving',    'name' => 'Paraquedismo',          'slug' => 'paraquedismo-jeri',      'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Salto duplo de paraquedas com vista para Jeri.', 'price' => 750.00, 'duration' => 120, 'capacity' => 4, 'time' => '09:00:00'],
        ]);

        $this->operator('Lucas Escalada', 'lucas@escalada.com', 'Vertical Jeri', 'vertical-jeri', '+55 88 99907-0007', [
            ['category' => 'rock-climbing', 'name' => 'Escalada nas Falésias', 'slug' => 'escalada-falesias',      'meeting_point' => 'Pedra Furada — base da trilha, Jericoacoara', 'description' => 'Escalada nas falésias da costa cearense.', 'price' => 180.00, 'duration' => 180, 'capacity' => 4, 'time' => '07:00:00'],
            ['category' => 'abseiling',     'name' => 'Rapel nas Falésias',    'slug' => 'rapel-falesias',         'meeting_point' => 'Pedra Furada — base da trilha, Jericoacoara', 'description' => 'Descida de rapel pelas falésias com vista para o mar.', 'price' => 150.00, 'duration' => 120, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'zip-lining',    'name' => 'Tirolesa na Floresta',  'slug' => 'tirolesa-floresta',      'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Tirolesa entre as árvores da mata nativa.', 'price' => 120.00, 'duration' => 90, 'capacity' => 6, 'time' => '10:00:00'],
            ['category' => 'caving',        'name' => 'Espeleologia — Grutas', 'slug' => 'espeleologia-grutas',    'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Exploração de cavernas e grutas da região.', 'price' => 200.00, 'duration' => 240, 'capacity' => 6, 'time' => '08:00:00'],
        ]);

        $this->operator('Sofia Cultura', 'sofia@cultura.com', 'Cultura Jeri', 'cultura-jeri', '+55 88 99908-0008', [
            ['category' => 'cultural-activities','name' => 'Tour Cultural Jeri','slug' => 'tour-cultural-jeri',   'meeting_point' => 'Praça Principal de Jericoacoara', 'description' => 'Conheça a história e cultura de Jericoacoara.', 'price' => 80.00, 'duration' => 180, 'capacity' => 12, 'time' => '15:00:00'],
            ['category' => 'festivals-fairs',    'name' => 'Festa da Lua Cheia','slug' => 'festa-lua-cheia',      'meeting_point' => 'Estacionamento da Duna do Pôr do Sol, Jericoacoara', 'description' => 'A famosa festa mensal na Duna do Pôr do Sol.', 'price' => 50.00, 'duration' => 300, 'capacity' => 20, 'time' => '18:00:00'],
            ['category' => 'backpacking',        'name' => 'Mochilão pela Costa','slug' => 'mochilao-costa-ce',   'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Rota de mochileiro pela costa do Ceará.', 'price' => 320.00, 'duration' => 1440, 'capacity' => 8, 'time' => '08:00:00'],
            ['category' => 'volunteering',       'name' => 'Voluntariado na Praia','slug' => 'voluntariado-praia','meeting_point' => 'Quiosque 7 — Praia de Jericoacoara', 'description' => 'Mutirão de limpeza e conservação das praias.', 'price' => 0.00, 'duration' => 180, 'capacity' => 20, 'time' => '07:00:00'],
        ]);

        $this->operator('Felipe Ar', 'felipe@ar.com', 'Esportes Aéreos Jeri', 'esportes-aereos-jeri', '+55 88 99909-0009', [
            ['category' => 'air-balloon',     'name' => 'Passeio de Balão',      'slug' => 'balao-jeri',           'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Voo de balão ao amanhecer sobre as dunas.', 'price' => 480.00, 'duration' => 60, 'capacity' => 4, 'time' => '05:30:00'],
            ['category' => 'flying',          'name' => 'Voo de Asa Delta',      'slug' => 'asa-delta-jeri',       'meeting_point' => 'Estacionamento da Duna do Pôr do Sol, Jericoacoara', 'description' => 'Voo livre sobre as dunas e o mar de Jeri.', 'price' => 350.00, 'duration' => 30, 'capacity' => 2, 'time' => '07:00:00'],
            ['category' => 'gliding',         'name' => 'Planador Panorâmico',   'slug' => 'planador-jeri',        'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Voo de planador com vista 360° da costa.', 'price' => 400.00, 'duration' => 45, 'capacity' => 2, 'time' => '08:00:00'],
            ['category' => 'wing-suit-flying','name' => 'Wingsuit Experience',   'slug' => 'wingsuit-jeri',        'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'A experiência de voar como um pássaro.', 'price' => 900.00, 'duration' => 60, 'capacity' => 2, 'time' => '09:00:00'],
        ]);

        $this->operator('Bianca Motor', 'bianca@motor.com', 'Off-Road Jeri', 'off-road-jeri', '+55 88 99910-0010', [
            ['category' => 'motor-biking', 'name' => 'Moto Off-Road nas Dunas','slug' => 'moto-off-road-jeri',    'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Trilha de moto pelas dunas e caminhos de terra.', 'price' => 220.00, 'duration' => 180, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'cycling',      'name' => 'Ciclismo na Costa',      'slug' => 'ciclismo-costa-jeri',   'meeting_point' => 'Praça Principal de Jericoacoara', 'description' => 'Pedalada pela orla e caminhos de Jericoacoara.', 'price' => 70.00, 'duration' => 120, 'capacity' => 8, 'time' => '07:00:00'],
            ['category' => 'running',      'name' => 'Corrida na Praia',       'slug' => 'corrida-praia-jeri',    'meeting_point' => 'Quiosque 7 — Praia de Jericoacoara', 'description' => 'Run guiado pelas praias ao nascer do sol.', 'price' => 40.00, 'duration' => 60, 'capacity' => 10, 'time' => '06:00:00'],
            ['category' => 'rafting',      'name' => 'Rafting no Rio Coreaú',  'slug' => 'rafting-coreau',        'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Rafting nas corredeiras do Rio Coreaú.', 'price' => 180.00, 'duration' => 180, 'capacity' => 8, 'time' => '08:00:00'],
        ]);

        // Pending providers (waiting for admin approval, not visible in feed)
        $this->pending('Marcos Buggy Novo',    'marcos@buggy.com',    'Dunas Vivas Jeri',         'dunas-vivas-jeri',        '+55 88 99920-0001');
        $this->pending('Fernanda Stand Up',    'fernanda@sup.com',    'SUP Jeri Escola',           'sup-jeri-escola',         '+55 88 99920-0002');
        $this->pending('Ricardo Mergulho',     'ricardo@mergulho.com','Profundezas Jericoacoara',  'profundezas-jericoacoara','+55 88 99920-0003');
        $this->pending('Tatiana Cavalgada',    'tatiana@cavalos.com', 'Cavalos da Dunas',          'cavalos-das-dunas',       '+55 88 99920-0004');

        $this->operator('Gabriel Nature', 'gabriel@nature.com', 'Wild Jeri', 'wild-jeri', '+55 88 99911-0011', [
            ['category' => 'wildlife-safari',    'name' => 'Safari de Vida Selvagem','slug' => 'safari-jeri',     'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Observação de animais nativos da caatinga e mata.', 'price' => 140.00, 'duration' => 240, 'capacity' => 6, 'time' => '06:00:00'],
            ['category' => 'expeditions',        'name' => 'Expedição Jericoacoara', 'slug' => 'expedicao-jeri',  'meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Expedição completa por Jeri e arredores em 2 dias.', 'price' => 450.00, 'duration' => 480, 'capacity' => 8, 'time' => '07:00:00'],
            ['category' => 'forest-expeditions', 'name' => 'Expedição na Floresta',  'slug' => 'exp-floresta-jeri','meeting_point' => 'Portão de entrada do Parque Nacional, Jijoca de Jericoacoara', 'description' => 'Exploração guiada da mata nativa da APA.', 'price' => 130.00, 'duration' => 300, 'capacity' => 6, 'time' => '07:00:00'],
            ['category' => 'hunting',            'name' => 'Caça Fotográfica',       'slug' => 'caca-fotografica', 'meeting_point' => 'Lagoa do Paraíso — entrada principal, Jericoacoara', 'description' => 'Fotografia de fauna silvestre na natureza.', 'price' => 160.00, 'duration' => 240, 'capacity' => 4, 'time' => '05:30:00'],
        ]);

        $this->operator('Carla Aqua', 'carla@aqua.com', 'Aqua Jeri', 'aqua-jeri', '+55 88 99912-0012', [
            ['category' => 'swimming',    'name' => 'Natação na Lagoa',          'slug' => 'natacao-lagoa-jeri',  'meeting_point' => 'Lagoa do Paraíso — entrada principal, Jericoacoara', 'description' => 'Aulas e sessões de natação na Lagoa do Paraíso.', 'price' => 60.00, 'duration' => 60, 'capacity' => 8, 'time' => '08:00:00'],
            ['category' => 'water-skiing','name' => 'Esqui Aquático',            'slug' => 'esqui-aquatico-jeri', 'meeting_point' => 'Lagoa do Paraíso — entrada principal, Jericoacoara', 'description' => 'Esqui aquático nas águas calmas da lagoa.', 'price' => 180.00, 'duration' => 60, 'capacity' => 4, 'time' => '10:00:00'],
            ['category' => 'tree-house',  'name' => 'Casa na Árvore',            'slug' => 'casa-arvore-jeri',    'meeting_point' => 'Recepção da Pousada Vila Kalango, Jericoacoara', 'description' => 'Pernoite em casa na árvore no meio da mata.', 'price' => 380.00, 'duration' => 720, 'capacity' => 2, 'time' => '15:00:00'],
            ['category' => 'ice-climbing','name' => 'Escalada — Parede Artificial','slug' => 'escalada-parede',  'meeting_point' => 'Praça Principal de Jericoacoara', 'description' => 'Escalada em parede artificial indoor.', 'price' => 90.00, 'duration' => 90, 'capacity' => 6, 'time' => '09:00:00'],
        ]);

        $this->operator('Diego Snow', 'diego@snow.com', 'Snow & Sky Brasil', 'snow-sky-brasil', '+55 88 99913-0013', [
            ['category' => 'skiing',       'name' => 'Esqui na Serra Gaúcha',  'slug' => 'esqui-serra-gaucha',    'meeting_point' => 'Estação Base da Serra Gaúcha, Gramado — RS', 'description' => 'Esqui nas encostas nevadas da Serra Gaúcha.', 'price' => 520.00, 'duration' => 240, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'snow-boarding','name' => 'Snowboard no Sul',       'slug' => 'snowboard-sul',         'meeting_point' => 'Estação Base da Serra Gaúcha, Gramado — RS', 'description' => 'Snowboard nas pistas do sul do Brasil.', 'price' => 480.00, 'duration' => 240, 'capacity' => 4, 'time' => '09:00:00'],
        ]);
    }

    private function pending(string $name, string $email, string $business, string $slug, string $whatsapp): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('Praia847!'), 'role' => 'provider', 'email_verified_at' => now()]
        );

        Provider::firstOrCreate(
            ['user_id' => $user->id],
            ['business_name' => $business, 'slug' => $slug, 'status' => 'pending', 'whatsapp_number' => $whatsapp]
        );
    }

    private function operator(string $name, string $email, string $business, string $slug, string $whatsapp, array $tours): void
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('Praia847!'), 'role' => 'provider', 'email_verified_at' => now()]
        );

        $provider = Provider::firstOrCreate(
            ['user_id' => $user->id],
            ['business_name' => $business, 'slug' => $slug, 'status' => 'approved', 'whatsapp_number' => $whatsapp]
        );

        foreach ($tours as $t) {
            $category = Category::where('slug', $t['category'])->firstOrFail();

            $activity = Activity::firstOrCreate(
                ['slug' => $t['slug']],
                [
                    'provider_id'      => $provider->id,
                    'category_id'      => $category->id,
                    'name'             => $t['name'],
                    'description'      => $t['description'],
                    'meeting_point'    => $t['meeting_point'] ?? null,
                    'price'            => $t['price'],
                    'duration_minutes' => $t['duration'],
                    'capacity'         => $t['capacity'],
                    'city'             => 'Jericoacoara',
                    'state'            => 'CE',
                    'is_active'        => true,
                ]
            );

            Media::firstOrCreate(
                ['activity_id' => $activity->id, 'type' => 'cover'],
                ['url' => 'https://picsum.photos/seed/' . $t['slug'] . '/800/500', 'order' => 0]
            );
            foreach ([1, 2, 3] as $i) {
                Media::firstOrCreate(
                    ['activity_id' => $activity->id, 'type' => 'gallery', 'order' => $i],
                    ['url' => 'https://picsum.photos/seed/' . $t['slug'] . '-' . $i . '/800/500']
                );
            }

            // Seed 3 reviews per activity
            $reviewCount = Review::where('activity_id', $activity->id)->count();
            if ($reviewCount === 0) {
                $picks = array_splice($this->reviewPool, 0, 3);
                $this->reviewPool = array_merge($this->reviewPool, $picks); // cycle back
                foreach ($picks as $r) {
                    Review::create([
                        'activity_id' => $activity->id,
                        'author_name' => $r['name'],
                        'rating'      => $r['rating'],
                        'body'        => $r['body'],
                        'source'      => 'user_submitted',
                        'is_approved' => true,
                    ]);
                }
            }

            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::today()->addDays($i)->toDateString();
                Schedule::firstOrCreate(
                    ['activity_id' => $activity->id, 'date' => $date, 'time' => $t['time']],
                    ['seats_total' => $t['capacity'], 'seats_booked' => 0]
                );
            }
        }
    }
}
