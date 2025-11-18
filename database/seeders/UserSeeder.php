<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@emercerie.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        // User::firstOrCreate(
        //     ['email' => 'belle@emercerie.com'],
        //     ['name' => 'Mercerie Belle Couture', 'password' => Hash::make('password'), 'role' => 'mercerie']
        // );

        // User::firstOrCreate(
        //     ['email' => 'alain@emercerie.com'],
        //     ['name' => 'Couturier Alain', 'password' => Hash::make('password'), 'role' => 'couturier']
        // );
    }
}
