<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Provider;
use App\Models\Category;
use App\Models\Activity;
use App\Models\Schedule;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'moghrabi@gmail.com'],
            ['name' => 'Seif', 'password' => Hash::make('admin123'), 'role' => 'admin', 'email_verified_at' => now()]
        );

        $this->operator('João Buggy', 'joao@buggy.com', 'Buggy Jeri do João', 'buggy-jeri-do-joao', '+55 88 99901-0001', [
            ['category' => 'buggy',        'name' => 'Buggy nas Dunas',        'slug' => 'buggy-nas-dunas',        'description' => 'Passeio pelas dunas da Pedra Furada e pôr do sol.', 'price' => 180.00, 'duration' => 180, 'capacity' => 4, 'time' => '08:00:00'],
            ['category' => 'buggy',        'name' => 'Buggy Lagoa Azul',       'slug' => 'buggy-lagoa-azul',       'description' => 'Roteiro completo pela Lagoa Azul e Lagoa do Paraíso.', 'price' => 220.00, 'duration' => 240, 'capacity' => 4, 'time' => '14:00:00'],
            ['category' => 'sand-boarding','name' => 'Sandboard nas Dunas',    'slug' => 'sandboard-dunas',        'description' => 'Adrenalina pura descendo as dunas de sandboard.', 'price' => 60.00, 'duration' => 60, 'capacity' => 6, 'time' => '10:00:00'],
        ]);

        $this->operator('Marina Kite', 'marina@kite.com', 'Jeri Kite School', 'jeri-kite-school', '+55 88 99902-0002', [
            ['category' => 'kitesurf',     'name' => 'Kitesurf Iniciante',     'slug' => 'kitesurf-iniciante-jeri','description' => 'Aula individual na melhor escola de kite do Brasil.', 'price' => 350.00, 'duration' => 120, 'capacity' => 2, 'time' => '09:00:00'],
            ['category' => 'surfe',        'name' => 'Aula de Surfe',          'slug' => 'aula-surfe-jeri',        'description' => 'Aprenda a surfar nas ondas perfeitas de Jericoacoara.', 'price' => 200.00, 'duration' => 90, 'capacity' => 4, 'time' => '07:00:00'],
        ]);

        $this->operator('Carlos Barco', 'carlos@barco.com', 'Passeios de Barco Jeri', 'passeios-de-barco-jeri', '+55 88 99903-0003', [
            ['category' => 'barco',        'name' => 'Barco Pedra Furada',     'slug' => 'barco-pedra-furada-jeri','description' => 'Passeio pela costa de Jeri até a Pedra Furada.', 'price' => 120.00, 'duration' => 150, 'capacity' => 8, 'time' => '08:30:00'],
            ['category' => 'snorkeling',   'name' => 'Snorkeling Lagoa Azul',  'slug' => 'snorkeling-lagoa-azul',  'description' => 'Mergulho com snorkel na cristalina Lagoa Azul.', 'price' => 80.00, 'duration' => 90, 'capacity' => 8, 'time' => '10:00:00'],
            ['category' => 'mergulho',     'name' => 'Mergulho com Cilindro',  'slug' => 'mergulho-cilindro-jeri', 'description' => 'Mergulho autônomo para iniciantes e avançados.', 'price' => 320.00, 'duration' => 180, 'capacity' => 4, 'time' => '09:00:00'],
        ]);

        $this->operator('Ana Eco', 'ana@eco.com', 'Eco Jeri Tours', 'eco-jeri-tours', '+55 88 99904-0004', [
            ['category' => 'ecoturismo',   'name' => 'Tour Ecoturismo Jeri',   'slug' => 'ecoturismo-jeri',        'description' => 'Conheça a fauna e flora da APA de Jericoacoara.', 'price' => 90.00, 'duration' => 180, 'capacity' => 8, 'time' => '07:30:00'],
            ['category' => 'trilhas',      'name' => 'Trilha Pedra Furada',    'slug' => 'trilha-pedra-furada',    'description' => 'Trilha guiada até a icônica Pedra Furada.', 'price' => 70.00, 'duration' => 240, 'capacity' => 10, 'time' => '06:00:00'],
            ['category' => 'bird-watching','name' => 'Observação de Aves',     'slug' => 'bird-watching-jeri',     'description' => 'Birding nas lagoas e mangues de Jericoacoara.', 'price' => 110.00, 'duration' => 180, 'capacity' => 6, 'time' => '05:30:00'],
            ['category' => 'camping',      'name' => 'Camping nas Dunas',      'slug' => 'camping-dunas-jeri',     'description' => 'Noite nas dunas sob o céu estrelado.', 'price' => 150.00, 'duration' => 720, 'capacity' => 8, 'time' => '17:00:00'],
        ]);

        $this->operator('Pedro Cavalo', 'pedro@cavalo.com', 'Cavalos de Jeri', 'cavalos-de-jeri', '+55 88 99905-0005', [
            ['category' => 'cavalgada',    'name' => 'Cavalgada Pôr do Sol',   'slug' => 'cavalgada-por-do-sol',   'description' => 'Cavalgada na praia ao pôr do sol, inesquecível.', 'price' => 130.00, 'duration' => 90, 'capacity' => 6, 'time' => '16:30:00'],
            ['category' => 'pesca',        'name' => 'Pesca Esportiva',        'slug' => 'pesca-esportiva-jeri',   'description' => 'Pesca em alto mar com barco equipado.', 'price' => 200.00, 'duration' => 300, 'capacity' => 5, 'time' => '05:00:00'],
            ['category' => 'caiaque',      'name' => 'Caiaque Lagoa do Paraíso','slug' => 'caiaque-lagoa-paraiso', 'description' => 'Remada tranquila nas águas cristalinas.', 'price' => 85.00, 'duration' => 120, 'capacity' => 6, 'time' => '09:00:00'],
        ]);

        $this->operator('Rafael Adrenalina', 'rafael@adrenalina.com', 'Adrenalina Jeri', 'adrenalina-jeri', '+55 88 99906-0006', [
            ['category' => 'jet-ski',       'name' => 'Jet Ski Jericoacoara',  'slug' => 'jet-ski-jeri',           'description' => 'Alta velocidade nas águas de Jeri.', 'price' => 160.00, 'duration' => 30, 'capacity' => 2, 'time' => '10:00:00'],
            ['category' => 'parasailing',   'name' => 'Parasail sobre o Mar',  'slug' => 'parasail-jeri',          'description' => 'Voar sobre o mar preso a um paraquedas.', 'price' => 250.00, 'duration' => 30, 'capacity' => 2, 'time' => '11:00:00'],
            ['category' => 'bungee-jumping','name' => 'Bungee Jump',           'slug' => 'bungee-jump-jeri',       'description' => 'O salto de bungee mais emocionante do Ceará.', 'price' => 190.00, 'duration' => 60, 'capacity' => 4, 'time' => '14:00:00'],
            ['category' => 'sky-diving',    'name' => 'Paraquedismo',          'slug' => 'paraquedismo-jeri',      'description' => 'Salto duplo de paraquedas com vista para Jeri.', 'price' => 750.00, 'duration' => 120, 'capacity' => 4, 'time' => '09:00:00'],
        ]);

        $this->operator('Lucas Escalada', 'lucas@escalada.com', 'Vertical Jeri', 'vertical-jeri', '+55 88 99907-0007', [
            ['category' => 'rock-climbing', 'name' => 'Escalada nas Falésias', 'slug' => 'escalada-falesias',      'description' => 'Escalada nas falésias da costa cearense.', 'price' => 180.00, 'duration' => 180, 'capacity' => 4, 'time' => '07:00:00'],
            ['category' => 'abseiling',     'name' => 'Rapel nas Falésias',    'slug' => 'rapel-falesias',         'description' => 'Descida de rapel pelas falésias com vista para o mar.', 'price' => 150.00, 'duration' => 120, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'zip-lining',    'name' => 'Tirolesa na Floresta',  'slug' => 'tirolesa-floresta',      'description' => 'Tirolesa entre as árvores da mata nativa.', 'price' => 120.00, 'duration' => 90, 'capacity' => 6, 'time' => '10:00:00'],
            ['category' => 'caving',        'name' => 'Espeleologia — Grutas', 'slug' => 'espeleologia-grutas',    'description' => 'Exploração de cavernas e grutas da região.', 'price' => 200.00, 'duration' => 240, 'capacity' => 6, 'time' => '08:00:00'],
        ]);

        $this->operator('Sofia Cultura', 'sofia@cultura.com', 'Cultura Jeri', 'cultura-jeri', '+55 88 99908-0008', [
            ['category' => 'cultural-activities','name' => 'Tour Cultural Jeri','slug' => 'tour-cultural-jeri',   'description' => 'Conheça a história e cultura de Jericoacoara.', 'price' => 80.00, 'duration' => 180, 'capacity' => 12, 'time' => '15:00:00'],
            ['category' => 'festivals-fairs',    'name' => 'Festa da Lua Cheia','slug' => 'festa-lua-cheia',      'description' => 'A famosa festa mensal na Duna do Pôr do Sol.', 'price' => 50.00, 'duration' => 300, 'capacity' => 20, 'time' => '18:00:00'],
            ['category' => 'backpacking',        'name' => 'Mochilão pela Costa','slug' => 'mochilao-costa-ce',   'description' => 'Rota de mochileiro pela costa do Ceará.', 'price' => 320.00, 'duration' => 1440, 'capacity' => 8, 'time' => '08:00:00'],
            ['category' => 'volunteering',       'name' => 'Voluntariado na Praia','slug' => 'voluntariado-praia','description' => 'Mutirão de limpeza e conservação das praias.', 'price' => 0.00, 'duration' => 180, 'capacity' => 20, 'time' => '07:00:00'],
        ]);

        $this->operator('Felipe Ar', 'felipe@ar.com', 'Esportes Aéreos Jeri', 'esportes-aereos-jeri', '+55 88 99909-0009', [
            ['category' => 'air-balloon',     'name' => 'Passeio de Balão',      'slug' => 'balao-jeri',           'description' => 'Voo de balão ao amanhecer sobre as dunas.', 'price' => 480.00, 'duration' => 60, 'capacity' => 4, 'time' => '05:30:00'],
            ['category' => 'flying',          'name' => 'Voo de Asa Delta',      'slug' => 'asa-delta-jeri',       'description' => 'Voo livre sobre as dunas e o mar de Jeri.', 'price' => 350.00, 'duration' => 30, 'capacity' => 2, 'time' => '07:00:00'],
            ['category' => 'gliding',         'name' => 'Planador Panorâmico',   'slug' => 'planador-jeri',        'description' => 'Voo de planador com vista 360° da costa.', 'price' => 400.00, 'duration' => 45, 'capacity' => 2, 'time' => '08:00:00'],
            ['category' => 'wing-suit-flying','name' => 'Wingsuit Experience',   'slug' => 'wingsuit-jeri',        'description' => 'A experiência de voar como um pássaro.', 'price' => 900.00, 'duration' => 60, 'capacity' => 2, 'time' => '09:00:00'],
        ]);

        $this->operator('Bianca Motor', 'bianca@motor.com', 'Off-Road Jeri', 'off-road-jeri', '+55 88 99910-0010', [
            ['category' => 'motor-biking', 'name' => 'Moto Off-Road nas Dunas','slug' => 'moto-off-road-jeri',    'description' => 'Trilha de moto pelas dunas e caminhos de terra.', 'price' => 220.00, 'duration' => 180, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'cycling',      'name' => 'Ciclismo na Costa',      'slug' => 'ciclismo-costa-jeri',   'description' => 'Pedalada pela orla e caminhos de Jericoacoara.', 'price' => 70.00, 'duration' => 120, 'capacity' => 8, 'time' => '07:00:00'],
            ['category' => 'running',      'name' => 'Corrida na Praia',       'slug' => 'corrida-praia-jeri',    'description' => 'Run guiado pelas praias ao nascer do sol.', 'price' => 40.00, 'duration' => 60, 'capacity' => 10, 'time' => '06:00:00'],
            ['category' => 'rafting',      'name' => 'Rafting no Rio Coreaú',  'slug' => 'rafting-coreau',        'description' => 'Rafting nas corredeiras do Rio Coreaú.', 'price' => 180.00, 'duration' => 180, 'capacity' => 8, 'time' => '08:00:00'],
        ]);

        // Pending providers (waiting for admin approval, not visible in feed)
        $this->pending('Marcos Buggy Novo',    'marcos@buggy.com',    'Dunas Vivas Jeri',         'dunas-vivas-jeri',        '+55 88 99920-0001');
        $this->pending('Fernanda Stand Up',    'fernanda@sup.com',    'SUP Jeri Escola',           'sup-jeri-escola',         '+55 88 99920-0002');
        $this->pending('Ricardo Mergulho',     'ricardo@mergulho.com','Profundezas Jericoacoara',  'profundezas-jericoacoara','+55 88 99920-0003');
        $this->pending('Tatiana Cavalgada',    'tatiana@cavalos.com', 'Cavalos da Dunas',          'cavalos-das-dunas',       '+55 88 99920-0004');

        $this->operator('Gabriel Nature', 'gabriel@nature.com', 'Wild Jeri', 'wild-jeri', '+55 88 99911-0011', [
            ['category' => 'wildlife-safari',    'name' => 'Safari de Vida Selvagem','slug' => 'safari-jeri',     'description' => 'Observação de animais nativos da caatinga e mata.', 'price' => 140.00, 'duration' => 240, 'capacity' => 6, 'time' => '06:00:00'],
            ['category' => 'expeditions',        'name' => 'Expedição Jericoacoara', 'slug' => 'expedicao-jeri',  'description' => 'Expedição completa por Jeri e arredores em 2 dias.', 'price' => 450.00, 'duration' => 480, 'capacity' => 8, 'time' => '07:00:00'],
            ['category' => 'forest-expeditions', 'name' => 'Expedição na Floresta',  'slug' => 'exp-floresta-jeri','description' => 'Exploração guiada da mata nativa da APA.', 'price' => 130.00, 'duration' => 300, 'capacity' => 6, 'time' => '07:00:00'],
            ['category' => 'hunting',            'name' => 'Caça Fotográfica',       'slug' => 'caca-fotografica', 'description' => 'Fotografia de fauna silvestre na natureza.', 'price' => 160.00, 'duration' => 240, 'capacity' => 4, 'time' => '05:30:00'],
        ]);

        $this->operator('Carla Aqua', 'carla@aqua.com', 'Aqua Jeri', 'aqua-jeri', '+55 88 99912-0012', [
            ['category' => 'swimming',    'name' => 'Natação na Lagoa',          'slug' => 'natacao-lagoa-jeri',  'description' => 'Aulas e sessões de natação na Lagoa do Paraíso.', 'price' => 60.00, 'duration' => 60, 'capacity' => 8, 'time' => '08:00:00'],
            ['category' => 'water-skiing','name' => 'Esqui Aquático',            'slug' => 'esqui-aquatico-jeri', 'description' => 'Esqui aquático nas águas calmas da lagoa.', 'price' => 180.00, 'duration' => 60, 'capacity' => 4, 'time' => '10:00:00'],
            ['category' => 'tree-house',  'name' => 'Casa na Árvore',            'slug' => 'casa-arvore-jeri',    'description' => 'Pernoite em casa na árvore no meio da mata.', 'price' => 380.00, 'duration' => 720, 'capacity' => 2, 'time' => '15:00:00'],
            ['category' => 'ice-climbing','name' => 'Escalada — Parede Artificial','slug' => 'escalada-parede',  'description' => 'Escalada em parede artificial indoor.', 'price' => 90.00, 'duration' => 90, 'capacity' => 6, 'time' => '09:00:00'],
        ]);

        $this->operator('Diego Snow', 'diego@snow.com', 'Snow & Sky Brasil', 'snow-sky-brasil', '+55 88 99913-0013', [
            ['category' => 'skiing',       'name' => 'Esqui na Serra Gaúcha',  'slug' => 'esqui-serra-gaucha',    'description' => 'Esqui nas encostas nevadas da Serra Gaúcha.', 'price' => 520.00, 'duration' => 240, 'capacity' => 4, 'time' => '09:00:00'],
            ['category' => 'snow-boarding','name' => 'Snowboard no Sul',       'slug' => 'snowboard-sul',         'description' => 'Snowboard nas pistas do sul do Brasil.', 'price' => 480.00, 'duration' => 240, 'capacity' => 4, 'time' => '09:00:00'],
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
                    'price'            => $t['price'],
                    'duration_minutes' => $t['duration'],
                    'capacity'         => $t['capacity'],
                    'city'             => 'Jericoacoara',
                    'state'            => 'CE',
                    'is_active'        => true,
                ]
            );

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
