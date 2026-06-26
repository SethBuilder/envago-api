<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LaraOnboardService
{
    public function onboard(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $op    = $data['operator'];
            $tours = $data['tours'];

            $tempPassword = $this->generatePassword();

            $user  = User::where('email', $op['email'])->first();
            $isNew = !$user;

            if ($isNew) {
                $user = User::create([
                    'name'              => $op['business_name'],
                    'email'             => $op['email'],
                    'password'          => Hash::make($tempPassword),
                    'role'              => 'provider',
                    'email_verified_at' => now(),
                ]);
            }

            $bankDetails = null;
            if (!empty($op['bank'])) {
                $b = $op['bank'];
                $bankDetails = array_filter([
                    'beneficiary'  => $b['beneficiary'] ?? null,
                    'cpf_cnpj'     => $b['cpf_cnpj'] ?? null,
                    'bank_name'    => $b['bank_name'] ?? null,
                    'address'      => $b['address'] ?? null,
                    'city'         => $b['city'] ?? null,
                    'state'        => isset($b['state']) ? strtoupper($b['state']) : null,
                    'postcode'     => $b['postcode'] ?? null,
                    'account_type' => $b['account_type'] ?? null,
                    'swift'        => isset($b['swift']) ? strtoupper($b['swift']) : null,
                    'branch'       => $b['branch'] ?? null,
                    'account'      => $b['account'] ?? null,
                    'country'      => 'Brasil',
                ]);
            }

            $provider = Provider::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name'   => $op['business_name'],
                    'whatsapp_number' => $op['whatsapp'] ?? null,
                    'status'          => 'approved',
                    'verified_at'     => now(),
                    'bank_details'    => $bankDetails ?: null,
                ]
            );

            if (!$provider->wasRecentlyCreated && $bankDetails) {
                $provider->update(['bank_details' => $bankDetails]);
            }

            foreach ($tours as $tour) {
                $catSlug  = Str::slug($tour['category_slug'] ?? 'tour');
                $catLabel = $tour['category_name'] ?? ucwords(str_replace('-', ' ', $catSlug));

                $category = Category::firstOrCreate(
                    ['slug' => $catSlug],
                    ['name' => $catLabel]
                );

                $base = Str::slug($tour['name']);
                $slug = $base;
                $i    = 2;
                while (Activity::where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i++;
                }

                $activity = Activity::firstOrCreate(
                    ['provider_id' => $provider->id, 'name' => $tour['name']],
                    [
                        'category_id'      => $category->id,
                        'slug'             => $slug,
                        'price'            => $tour['price'],
                        'duration_minutes' => $tour['duration_minutes'],
                        'capacity'         => $tour['max_seats'],
                        'city'             => $op['city'],
                        'state'            => $op['state'],
                        'description'      => $tour['description'] ?? null,
                        'is_active'        => true,
                    ]
                );

                if ($activity->wasRecentlyCreated) {
                    $dailySeats = $tour['daily_seats'] ?? $tour['max_seats'];
                    $startTime  = $tour['start_time']  ?? '08:00:00';

                    $schedules = [];
                    for ($day = 0; $day < 60; $day++) {
                        $schedules[] = [
                            'activity_id'  => $activity->id,
                            'date'         => Carbon::today()->addDays($day)->toDateString(),
                            'time'         => $startTime,
                            'seats_total'  => $dailySeats,
                            'seats_booked' => 0,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    Schedule::insert($schedules);
                }
            }

            return [
                'success'       => true,
                'is_new'        => $isNew,
                'provider_slug' => $provider->slug,
                'page_url'      => 'https://envago.live/operadores/' . $provider->slug,
                'login_url'     => url('/login'),
                'email'         => $user->email,
                'temp_password' => $isNew ? $tempPassword : null,
            ];
        });
    }

    private function generatePassword(): string
    {
        $words = ['Praia', 'Buggy', 'Jeri', 'Vento', 'Mare', 'Dunas', 'Kite', 'Barco'];
        return $words[array_rand($words)] . rand(100, 999) . '!';
    }
}
