<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Media;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $activities = Activity::all();

        foreach ($activities as $activity) {
            for ($i = 1; $i <= 4; $i++) {
                Media::firstOrCreate(
                    [
                        'activity_id' => $activity->id,
                        'type'        => 'gallery',
                        'order'       => $i,
                    ],
                    [
                        'url' => "https://picsum.photos/seed/{$activity->slug}-g{$i}/800/500",
                    ]
                );
            }
        }
    }
}