<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Schedule;
use Carbon\Carbon;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $activities = Activity::all();

        // Seed next 14 days of schedules per activity
        foreach ($activities as $activity) {
            for ($day = 0; $day < 14; $day++) {
                $date = Carbon::today()->addDays($day);

                // Skip if schedule already exists
                if (Schedule::where('activity_id', $activity->id)->where('date', $date->toDateString())->exists()) {
                    continue;
                }

                // Morning slot
                Schedule::create([
                    'activity_id'  => $activity->id,
                    'date'         => $date->toDateString(),
                    'time'         => '08:00:00',
                    'seats_total'  => $activity->capacity,
                    'seats_booked' => rand(0, max(0, $activity->capacity - 1)),
                ]);

                // Afternoon slot (not for very long activities)
                if ($activity->duration_minutes <= 180) {
                    Schedule::create([
                        'activity_id'  => $activity->id,
                        'date'         => $date->toDateString(),
                        'time'         => '14:00:00',
                        'seats_total'  => $activity->capacity,
                        'seats_booked' => rand(0, max(0, $activity->capacity - 1)),
                    ]);
                }
            }
        }
    }
}