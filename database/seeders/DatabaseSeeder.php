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

        // Create admin user if it doesn't exist
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Account',
                'phone_number' => '1234567890',
                'address' => 'sample address',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create office user if it doesn't exist
        $office = User::firstOrCreate(
            ['email' => 'office@example.com'],
            [
                'name' => 'Office Account',
                'phone_number' => '0987654321',
                'address' => 'another sample address',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);

        // Create regular user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User Account',
                'phone_number' => '1122334455',
                'address' => 'user sample address',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole($userRole);

        // Create test user if it doesn't exist
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone_number' => '1234567891',
                'address' => '123 Test Street',
                'password' => bcrypt('password'),
            ]
        );
        $testUser->assignRole($userRole);
    }
}
