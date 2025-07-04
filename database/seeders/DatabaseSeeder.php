<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default user for testing
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '1234567891',
            'address' => '123 Test Street',
            'password' => bcrypt('password'),
        ]);

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $officeRole = Role::firstOrCreate(['name' => 'office']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create permissions
        $editArticles = Permission::firstOrCreate(['name' => 'edit articles']);
        $deleteArticles = Permission::firstOrCreate(['name' => 'delete articles']);
        $viewDashboard = Permission::firstOrCreate(['name' => 'view dashboard']);

        // Assign permissions to admin role
        $adminRole->givePermissionTo([$editArticles, $deleteArticles, $viewDashboard]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin Account',
            'email' => 'admin@example.com',
            'phone_number' => '1234567890',
            'address' => 'sample address',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create office user
        $office = User::factory()->create([
            'name' => 'Office Account',
            'email' => 'office@example.com',
            'phone_number' => '0987654321',
            'address' => 'another sample address',
            'password' => bcrypt('password'),
        ]);
        $office->assignRole($officeRole);

        // Create regular user
        $user = User::factory()->create([
            'name' => 'User Account',
            'email' => 'user@example.com',
            'phone_number' => '1122334455',
            'address' => 'user sample address',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole($userRole);
    }
}
