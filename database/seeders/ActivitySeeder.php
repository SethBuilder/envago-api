<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Media;
use App\Models\Review;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $provider = Provider::first();

        $activities = [
            [
                'name'             => 'Passeio de Buggy nas Dunas',
                'slug'             => 'passeio-buggy-dunas-jericoacoara',
                'category_slug'    => 'buggy',
                'price'            => 180.00,
                'duration_minutes' => 180,
                'capacity'         => 4,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Suba nas dunas mais altas de Jericoacoara a bordo de um buggy 4x4 preparado para o terreno. O passeio percorre as dunas do Pôr do Sol, a Lagoa do Paraíso e pontos panorâmicos com vista para o mar. Ideal para quem busca adrenalina e paisagens de tirar o fôlego. Guia experiente incluso, sem necessidade de experiência prévia.',
                'cover'            => 'https://picsum.photos/seed/jeri-buggy-dunes/600/400',
                'reviews' => [
                    ['author' => 'Marina S.',   'rating' => 5, 'body' => 'Emocionante demais! Valeu cada centavo.'],
                    ['author' => 'Pedro A.',     'rating' => 5, 'body' => 'Melhor passeio de Jeri, sem duvida!'],
                    ['author' => 'Carol V.',     'rating' => 5, 'body' => 'Guia super atencioso, recomendo muito.'],
                ],
            ],
            [
                'name'             => 'Aula de Kitesurf para Iniciantes',
                'slug'             => 'aula-kitesurf-jericoacoara',
                'category_slug'    => 'kitesurf',
                'price'            => 250.00,
                'duration_minutes' => 120,
                'capacity'         => 2,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Jericoacoara é considerada um dos melhores destinos do mundo para kitesurf, com vento constante e águas rasas perfeitas para aprender. Nesta aula para iniciantes, você aprende desde o controle da pipa até os primeiros deslizes na água. Instrutor certificado, equipamento completo incluso e turmas com no máximo 2 alunos para garantia de atenção individual.',
                'cover'            => 'https://picsum.photos/seed/kitesurf-jericoacoara-ce/600/400',
                'reviews' => [
                    ['author' => 'Lucas F.',    'rating' => 5, 'body' => 'Aprendi do zero! Instrutor incrivel.'],
                    ['author' => 'Beatriz M.',  'rating' => 5, 'body' => 'Fui com medo e sai apaixonada pelo esporte!'],
                ],
            ],
            [
                'name'             => 'Passeio de Barco ao Por do Sol',
                'slug'             => 'passeio-barco-por-do-sol',
                'category_slug'    => 'barco',
                'price'            => 90.00,
                'duration_minutes' => 120,
                'capacity'         => 20,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Navegue ao longo da costa de Jericoacoara enquanto o sol mergulha no horizonte. O passeio sai no fim da tarde e inclui música ao vivo, caipirinha e petiscos a bordo. Um dos cenários mais famosos do litoral cearense, visto de dentro do mar. Embarcação confortável com capacidade para grupos, perfeita para casais e famílias.',
                'cover'            => 'https://picsum.photos/seed/sunset-boat-jeri/600/400',
                'reviews' => [
                    ['author' => 'Juliana T.',  'rating' => 5, 'body' => 'Por do sol mais lindo da minha vida.'],
                    ['author' => 'Leonardo B.', 'rating' => 4, 'body' => 'Caipirinha inclusa, perfeito demais!'],
                ],
            ],
            [
                'name'             => 'Trilha para a Pedra Furada',
                'slug'             => 'trilha-pedra-furada',
                'category_slug'    => 'trilhas',
                'price'            => 60.00,
                'duration_minutes' => 240,
                'capacity'         => 15,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'A Pedra Furada é o cartão-postal de Jericoacoara: um arco de pedra esculpido pelo vento e pelo mar, no meio de uma praia selvagem. A trilha atravessa dunas, matas e costa rochosa, com paradas para banho e fotos. Guia local incluso que conta a história da região. Indicado para quem tem boa condição física e quer ver a Jeri mais autêntica.',
                'cover'            => 'https://picsum.photos/seed/pedra-furada-jeri-hiking/600/400',
                'reviews' => [
                    ['author' => 'Natalia C.',  'rating' => 5, 'body' => 'Paisagem de tirar o folego!'],
                    ['author' => 'Vinicius R.',  'rating' => 5, 'body' => 'Guia conhece cada cantinho da trilha.'],
                ],
            ],
            [
                'name'             => 'Caiaque na Lagoa do Paraiso',
                'slug'             => 'caiaque-lagoa-do-paraiso',
                'category_slug'    => 'caiaque',
                'price'            => 80.00,
                'duration_minutes' => 120,
                'capacity'         => 6,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Explore a Lagoa do Paraíso de caiaque, rodeado por água cristalina de tom esverdeado e barracas flutuantes. O passeio é tranquilo, ideal para todos os níveis, e inclui equipamento e colete. Você pode parar para nadar, tomar um suco de caju e relaxar nas redes suspensas sobre a lagoa. Um dos programas mais relaxantes de Jericoacoara.',
                'cover'            => 'https://picsum.photos/seed/lagoa-paraiso-kayak/600/400',
                'reviews' => [
                    ['author' => 'Paula N.',    'rating' => 5, 'body' => 'Agua cristalina, parecia sonho!'],
                    ['author' => 'Diego K.',    'rating' => 5, 'body' => 'Lagoa linda, passeio top demais.'],
                ],
            ],
            [
                'name'             => 'Mergulho com Snorkel na Lagoa Azul',
                'slug'             => 'snorkeling-lagoa-azul',
                'category_slug'    => 'snorkeling',
                'price'            => 70.00,
                'duration_minutes' => 90,
                'capacity'         => 10,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'A Lagoa Azul tem água tão transparente que você vê o fundo a metros de profundidade. Com máscara e snorkel, você nada entre peixes coloridos num ambiente preservado e de rara beleza. Equipamento completo incluso e monitores na água para sua segurança. Não é necessário saber mergulhar — apenas gostar de água.',
                'cover'            => 'https://picsum.photos/seed/snorkel-lagoa-azul-jeri/600/400',
                'reviews' => [
                    ['author' => 'Alice M.',    'rating' => 5, 'body' => 'Peixinhos coloridos incriveis!'],
                    ['author' => 'Bruno S.',    'rating' => 4, 'body' => 'Agua clarinha, visibilidade otima!'],
                ],
            ],
            [
                'name'             => 'Cavalgada na Praia ao Por do Sol',
                'slug'             => 'cavalgada-praia-jericoacoara',
                'category_slug'    => 'cavalgada',
                'price'            => 120.00,
                'duration_minutes' => 90,
                'capacity'         => 8,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Passeio a cavalo pela beira-mar de Jericoacoara, com saída no fim da tarde para aproveitar a luz dourada do pôr do sol. Os cavalos são mansos e bem cuidados, adequados para iniciantes e crianças. O roteiro passa por praias desertas e dunas costeiras, com parada para fotos inesquecíveis. Um programa romântico e único.',
                'cover'            => 'https://picsum.photos/seed/horse-beach-jericoacoara/600/400',
                'reviews' => [
                    ['author' => 'Bianca H.',   'rating' => 5, 'body' => 'Cavalos mansissimos, super seguro.'],
                    ['author' => 'Renata P.',   'rating' => 5, 'body' => 'Foto no por do sol ficou perfeita!'],
                ],
            ],
            [
                'name'             => 'Buggy Noturno: Noite nas Dunas',
                'slug'             => 'buggy-noturno-dunas',
                'category_slug'    => 'buggy',
                'price'            => 220.00,
                'duration_minutes' => 180,
                'capacity'         => 4,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Uma experiência completamente diferente: explorar as dunas de Jericoacoara à noite, sob um céu estrelado sem poluição luminosa. O buggy avança pelas dunas escuras enquanto o guia apaga os faróis para revelar a Via Láctea. Parada para contemplação e fotos astronômicas. Um passeio que pouquíssimos visitantes de Jeri já fizeram.',
                'cover'            => 'https://picsum.photos/seed/night-dunes-jeri-stars/600/400',
                'reviews' => [
                    ['author' => 'Eduardo S.',  'rating' => 5, 'body' => 'Ceu estrelado nas dunas: indescritivel.'],
                    ['author' => 'Priscila J.', 'rating' => 5, 'body' => 'Passeio mais diferente que ja fiz na vida!'],
                ],
            ],
            [
                'name'             => 'Sandboard nas Grandes Dunas',
                'slug'             => 'sandboard-dunas-jericoacoara',
                'category_slug'    => 'buggy',
                'price'            => 50.00,
                'duration_minutes' => 60,
                'capacity'         => 20,
                'city'             => 'Jericoacoara',
                'state'            => 'CE',
                'description'      => 'Desça de prancha as maiores dunas de Jericoacoara em alta velocidade. O sandboard é fácil de aprender e muito divertido — em minutos você já está descendo sozinho. A prancha e cera são fornecidas, e o instrutor ajuda nos primeiros deslizes. Perfeito para um programa curto cheio de adrenalina, inclusive para crianças a partir de 8 anos.',
                'cover'            => 'https://picsum.photos/seed/sandboard-dunas-grandes/600/400',
                'reviews' => [
                    ['author' => 'Kaio R.',     'rating' => 5, 'body' => 'Adrenalina pura! Fiz 10 descidas.'],
                    ['author' => 'Maya D.',     'rating' => 5, 'body' => 'Melhor R$50 que ja gastei em viagem!'],
                ],
            ],
        ];

        foreach ($activities as $data) {
            $category = Category::where('slug', $data['category_slug'])->first();

            $activity = Activity::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'provider_id'      => $provider->id,
                    'category_id'      => $category?->id,
                    'name'             => $data['name'],
                    'description'      => $data['description'],
                    'price'            => $data['price'],
                    'duration_minutes' => $data['duration_minutes'],
                    'capacity'         => $data['capacity'],
                    'city'             => $data['city'],
                    'state'            => $data['state'],
                    'is_active'        => true,
                ]
            );

            Media::firstOrCreate(
                ['activity_id' => $activity->id, 'type' => 'cover'],
                ['url' => $data['cover'], 'order' => 0]
            );

            foreach ($data['reviews'] as $r) {
                Review::firstOrCreate(
                    ['activity_id' => $activity->id, 'author_name' => $r['author']],
                    [
                        'rating'      => $r['rating'],
                        'body'        => $r['body'],
                        'source'      => 'scraped',
                        'is_approved' => true,
                    ]
                );
            }
        }
    }
}