<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Provider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            // Approved - activities will be seeded for this one
            [
                'name'          => 'Operador Demo',
                'email'         => 'demo@envago.live',
                'business_name' => 'Aventuras Jericoacoara',
                'whatsapp'      => '+55 88 99911-0001',
                'status'        => 'approved',
            ],
            // More approved providers (tours visible in feed)
            [
                'name'          => 'Carlos Buggy',
                'email'         => 'carlos@envago.live',
                'business_name' => 'Carlos Buggy Jeri',
                'whatsapp'      => '+55 88 99911-0002',
                'status'        => 'approved',
            ],
            [
                'name'          => 'Lagoa Kite School',
                'email'         => 'lagoa@envago.live',
                'business_name' => 'Lagoa Kite School',
                'whatsapp'      => '+55 88 99911-0003',
                'status'        => 'approved',
            ],
            // Pending - waiting for approval, not visible in feed
            [
                'name'          => 'João Barcos',
                'email'         => 'joao@envago.live',
                'business_name' => 'João Passeios de Barco',
                'whatsapp'      => '+55 88 99911-0004',
                'status'        => 'pending',
            ],
            [
                'name'          => 'Trilhas Ceará',
                'email'         => 'trilhas@envago.live',
                'business_name' => 'Trilhas Ceará',
                'whatsapp'      => '+55 88 99911-0005',
                'status'        => 'pending',
            ],
            [
                'name'          => 'Surf Escola Jeri',
                'email'         => 'surf@envago.live',
                'business_name' => 'Surf Escola Jericoacoara',
                'whatsapp'      => '+55 88 99911-0006',
                'status'        => 'pending',
            ],
        ];

        foreach ($providers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'role'     => 'provider',
                    'password' => Hash::make('password'),
                ]
            );

            Provider::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'business_name'    => $data['business_name'],
                    'slug'             => Str::slug($data['business_name']),
                    'whatsapp_number'  => $data['whatsapp'],
                    'status'           => $data['status'],
                    'verified_at'      => $data['status'] === 'approved' ? now() : null,
                ]
            );
        }
    }
}
