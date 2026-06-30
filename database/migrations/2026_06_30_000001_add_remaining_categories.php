<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('categories')->insertOrIgnore([
            ['name' => 'Rapel',                 'slug' => 'abseiling',           'icon_path' => '/icons/categories/abseiling.svg',           'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Balão',                 'slug' => 'air-balloon',         'icon_path' => '/icons/categories/air-balloon.svg',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mochilão',              'slug' => 'backpacking',         'icon_path' => '/icons/categories/backpacking.svg',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Observação de Aves',    'slug' => 'bird-watching',       'icon_path' => '/icons/categories/bird-watching.svg',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bungee Jump',           'slug' => 'bungee-jumping',      'icon_path' => '/icons/categories/bungee-jumping.svg',      'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camping',               'slug' => 'camping',             'icon_path' => '/icons/categories/camping.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Espeleologia',          'slug' => 'caving',              'icon_path' => '/icons/categories/caving.svg',              'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Atividades Culturais',  'slug' => 'cultural-activities', 'icon_path' => '/icons/categories/cultural-activities.svg', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ciclismo',              'slug' => 'cycling',             'icon_path' => '/icons/categories/cycling.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Expedições',            'slug' => 'expeditions',         'icon_path' => '/icons/categories/expeditions.svg',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Festivais e Feiras',    'slug' => 'festivals-fairs',     'icon_path' => '/icons/categories/festivals-fairs.svg',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Voo',                   'slug' => 'flying',              'icon_path' => '/icons/categories/flying.svg',              'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Expedição na Floresta', 'slug' => 'forest-expeditions',  'icon_path' => '/icons/categories/forest-expeditions.svg',  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Planador',              'slug' => 'gliding',             'icon_path' => '/icons/categories/gliding.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Caça',                  'slug' => 'hunting',             'icon_path' => '/icons/categories/hunting.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Escalada no Gelo',      'slug' => 'ice-climbing',        'icon_path' => '/icons/categories/ice-climbing.svg',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Moto',                  'slug' => 'motor-biking',        'icon_path' => '/icons/categories/motor-biking.svg',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parasail',              'slug' => 'parasailing',         'icon_path' => '/icons/categories/parasailing.svg',         'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rafting',               'slug' => 'rafting',             'icon_path' => '/icons/categories/rafting.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Escalada',              'slug' => 'rock-climbing',       'icon_path' => '/icons/categories/rock-climbing.svg',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Corrida',               'slug' => 'running',             'icon_path' => '/icons/categories/running.svg',             'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sandboard',             'slug' => 'sand-boarding',       'icon_path' => '/icons/categories/sand-boarding.svg',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Esqui',                 'slug' => 'skiing',              'icon_path' => '/icons/categories/skiing.svg',              'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paraquedismo',          'slug' => 'sky-diving',          'icon_path' => '/icons/categories/sky-diving.svg',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Snowboard',             'slug' => 'snow-boarding',       'icon_path' => '/icons/categories/snow-boarding.svg',       'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Natação',               'slug' => 'swimming',            'icon_path' => '/icons/categories/swimming.svg',            'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Casa na Árvore',        'slug' => 'tree-house',          'icon_path' => '/icons/categories/tree-house.svg',          'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Voluntariado',          'slug' => 'volunteering',        'icon_path' => '/icons/categories/volunteering.svg',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Esqui Aquático',        'slug' => 'water-skiing',        'icon_path' => '/icons/categories/water-skiing.svg',        'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Safari',                'slug' => 'wildlife-safari',     'icon_path' => '/icons/categories/wildlife-safari.svg',     'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wingsuit',              'slug' => 'wing-suit-flying',    'icon_path' => '/icons/categories/wing-suit-flying.svg',    'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tirolesa',              'slug' => 'zip-lining',          'icon_path' => '/icons/categories/zip-lining.svg',          'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void {
        DB::table('categories')->whereIn('slug', [
            'abseiling','air-balloon','backpacking','bird-watching','bungee-jumping','camping',
            'caving','cultural-activities','cycling','expeditions','festivals-fairs','flying',
            'forest-expeditions','gliding','hunting','ice-climbing','motor-biking','parasailing',
            'rafting','rock-climbing','running','sand-boarding','skiing','sky-diving','snow-boarding',
            'swimming','tree-house','volunteering','water-skiing','wildlife-safari','wing-suit-flying',
            'zip-lining',
        ])->delete();
    }
};
