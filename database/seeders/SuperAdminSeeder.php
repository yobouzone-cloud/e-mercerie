<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'super_admin')->first();

        if (!$role) {
            $this->command->warn('Role super_admin non trouvé — exécute RolesSeeder d’abord.');
            return;
        }

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('ChangeMe123!'),
                'role_id' => $role->id,
            ]
        );

        $this->command->info('Super administrateur créé : admin@example.com / ChangeMe123!');
    }
}
