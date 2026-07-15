<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure the Shield super_admin role exists first
        $roleName = 'super_admin';
        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

        // 2. Create or find the Super Admin user account
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'], // Unique identifier check
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Admin@123'), // Change to a secure password
                'email_verified_at' => now(),
            ]
        );

        // 3. Assign the Shield role to the user safely
        if (! $user->hasRole($roleName)) {
            $user->assignRole($role);
            $this->command->info("Super Admin created and assigned to '{$roleName}' role successfully!");
        } else {
            $this->command->comment("User '{$user->email}' already holds the '{$roleName}' role.");
        }
    }
}
