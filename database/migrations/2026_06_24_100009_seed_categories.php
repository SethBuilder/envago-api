<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::table('categories')->insertOrIgnore([
            // Core Brazil beach categories
            ['name' => 'Buggy',                 'slug' => 'buggy',              'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Surfe',                 'slug' => 'surfe',              'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Kitesurf',              'slug' => 'kitesurf',           'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Barco',                 'slug' => 'barco',              'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Caiaque',               'slug' => 'caiaque',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Trilhas',               'slug' => 'trilhas',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Snorkeling',            'slug' => 'snorkeling',         'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mergulho',              'slug' => 'mergulho',           'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ecoturismo',            'slug' => 'ecoturismo',         'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pesca',                 'slug' => 'pesca',              'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cavalgada',             'slug' => 'cavalgada',          'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Jet-ski',               'slug' => 'jet-ski',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            // Extended categories (from v1 icon set)
            ['name' => 'Rapel',                 'slug' => 'abseiling',          'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Balão',                 'slug' => 'air-balloon',        'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mochilão',              'slug' => 'backpacking',        'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Observação de Aves',    'slug' => 'bird-watching',      'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bungee Jump',           'slug' => 'bungee-jumping',     'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Camping',               'slug' => 'camping',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Espeleologia',          'slug' => 'caving',             'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Atividades Culturais',  'slug' => 'cultural-activities','icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ciclismo',              'slug' => 'cycling',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Expedições',            'slug' => 'expeditions',        'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Festivais e Feiras',    'slug' => 'festivals-fairs',    'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Voo',                   'slug' => 'flying',             'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Expedição na Floresta', 'slug' => 'forest-expeditions', 'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Planador',              'slug' => 'gliding',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Caça',                  'slug' => 'hunting',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Escalada no Gelo',      'slug' => 'ice-climbing',       'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Moto',                  'slug' => 'motor-biking',       'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Parasail',              'slug' => 'parasailing',        'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rafting',               'slug' => 'rafting',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Escalada',              'slug' => 'rock-climbing',      'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Corrida',               'slug' => 'running',            'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sandboard',             'slug' => 'sand-boarding',      'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Esqui',                 'slug' => 'skiing',             'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paraquedismo',          'slug' => 'sky-diving',         'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Snowboard',             'slug' => 'snow-boarding',      'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Natação',               'slug' => 'swimming',           'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Casa na Árvore',        'slug' => 'tree-house',         'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Voluntariado',          'slug' => 'volunteering',       'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Esqui Aquático',        'slug' => 'water-skiing',       'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Safari',                'slug' => 'wildlife-safari',    'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wingsuit',              'slug' => 'wing-suit-flying',   'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Tirolesa',              'slug' => 'zip-lining',         'icon_path' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void {
        DB::table('categories')->whereIn('slug', [
            'buggy','surfe','kitesurf','barco','caiaque','trilhas','snorkeling','mergulho',
            'ecoturismo','pesca','cavalgada','jet-ski','abseiling','air-balloon','backpacking',
            'bird-watching','bungee-jumping','camping','caving','cultural-activities','cycling',
            'expeditions','festivals-fairs','flying','forest-expeditions','gliding','hunting',
            'ice-climbing','motor-biking','parasailing','rafting','rock-climbing','running',
            'sand-boarding','skiing','sky-diving','snow-boarding','swimming','tree-house',
            'volunteering','water-skiing','wildlife-safari','wing-suit-flying','zip-lining',
        ])->delete();
    }
};
