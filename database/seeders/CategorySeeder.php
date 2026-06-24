<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Buggy nas Dunas',   'slug' => 'buggy',      'icon_path' => '/icons/categories/sand-boarding.svg'],
            ['name' => 'Surfe',             'slug' => 'surfe',      'icon_path' => '/icons/categories/surfing.svg'],
            ['name' => 'Kitesurf',          'slug' => 'kitesurf',   'icon_path' => '/icons/categories/wind-sailing.svg'],
            ['name' => 'Passeio de Barco',  'slug' => 'barco',      'icon_path' => '/icons/categories/sailing.svg'],
            ['name' => 'Caiaque',           'slug' => 'caiaque',    'icon_path' => '/icons/categories/kayaking.svg'],
            ['name' => 'Trilhas',           'slug' => 'trilhas',    'icon_path' => '/icons/categories/hiking-and-trekking.svg'],
            ['name' => 'Snorkeling',        'slug' => 'snorkeling', 'icon_path' => '/icons/categories/snorkeling.svg'],
            ['name' => 'Mergulho',          'slug' => 'mergulho',   'icon_path' => '/icons/categories/scuba-diving.svg'],
            ['name' => 'Ecoturismo',        'slug' => 'ecoturismo', 'icon_path' => '/icons/categories/eco-tourism.svg'],
            ['name' => 'Pesca',             'slug' => 'pesca',      'icon_path' => '/icons/categories/fishing.svg'],
            ['name' => 'Cavalgada',         'slug' => 'cavalgada',  'icon_path' => '/icons/categories/horse-riding.svg'],
            ['name' => 'Jet Ski',           'slug' => 'jet-ski',    'icon_path' => '/icons/categories/jet-ski.svg'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}