<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\Hash;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@envago.live'],
            [
                'name'     => 'Operador Demo',
                'phone'    => '+55 88 99999-0000',
                'password' => Hash::make('password'),
            ]
        );

        Provider::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'Aventuras Jericoacoara',
                'pix_key'       => 'demo@envago.live',
                'verified_at'   => now(),
            ]
        );
    }
}