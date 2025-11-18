<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => 'Super Admin'],
            ['name' => 'mercerie', 'label' => 'Mercerie'],
            ['name' => 'couturier', 'label' => 'Couturier'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }

        $this->command->info(count($roles) . ' roles ajoutés.');
    }
}
