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
            ['email' => 'admin@calape.com'],
            [
                'name' => 'Admin Account',
                'phone_number' => '1234567890',
                'address' => 'sample address',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create office user if it doesn't exist
        //1
        $office = User::firstOrCreate(
            ['email' => 'crm@calape.com'],
            [
                'name' => 'CRM Office Account',
                'phone_number' => '0987654301',
                'address' => '8711',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);

        //2 
        $office = User::firstOrCreate(
            ['email' => 'mo@calape.com'],
            [
                'name' => 'MO Office Account',
                'phone_number' => '0987654302',
                'address' => '1011',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //3
        $office = User::firstOrCreate(
            ['email' => 'mao@calape.com'],
            [
                'name' => 'MAO Office Account',
                'phone_number' => '0987654303',
                'address' => '8711',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //4
        $office = User::firstOrCreate(
            ['email' => 'lcr@calape.com'],
            [
                'name' => 'LCR Office Account',
                'phone_number' => '0987654304',
                'address' => '1051',
                'password' => bcrypt('password'),
            ]
        );
        //5
        $office->assignRole($officeRole);
        $office = User::firstOrCreate(
            ['email' => 'engineering@calape.com'],
            [
                'name' => 'Engineering Office Account',
                'phone_number' => '0987654305',
                'address' => '8751',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //6
        $office = User::firstOrCreate(
            ['email' => 'accounting@calape.com'],
            [
                'name' => 'Accounting Office Account',
                'phone_number' => '0987654306',
                'address' => '1081',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //7
        $office = User::firstOrCreate(
            ['email' => 'mswdo@calape.com'],
            [
                'name' => 'MSWDO Office Account',
                'phone_number' => '0987654307',
                'address' => '7611',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //8
        $office = User::firstOrCreate(
            ['email' => 'rhu@calape.com'],
            [
                'name' => 'RHU Office Account',
                'phone_number' => '0987654308',
                'address' => '4411',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //9
        $office = User::firstOrCreate(
            ['email' => 'waterworks@calape.com'],
            [
                'name' => 'Waterworks Office Account',
                'phone_number' => '0987654309',
                'address' => '8771',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //10
        $office = User::firstOrCreate(
            ['email' => 'ldrrm@example.com'],
            [
                'name' => 'LDRRM Office Account',
                'phone_number' => '0987654310',
                'address' => '1012',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //11
        $office = User::firstOrCreate(
            ['email' => 'mto@calape.com'],
            [
                'name' => 'MTO Office Account',
                'phone_number' => '0987654311',
                'address' => '1091',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //12
        $office = User::firstOrCreate(
            ['email' => 'pnp@calape.com'],
            [
                'name' => 'PNP Office Account',
                'phone_number' => '0987654312',
                'address' => '1181',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //13
        $office = User::firstOrCreate(
            ['email' => 'hrmo@calape.com'],
            [
                'name' => 'HRMO Office Account',
                'phone_number' => '0987654313',
                'address' => '1011',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //14
        $office = User::firstOrCreate(
            ['email' => 'nutririon@calape.com'],
            [
                'name' => 'Nutrition Office Account',
                'phone_number' => '0987654314',
                'address' => '4411',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //15
        $office = User::firstOrCreate(
            ['email' => 'mswdosca@calape.com'],
            [
                'name' => 'MSWD Office Account',
                'phone_number' => '0987654315',
                'address' => '7611',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //16
        $office = User::firstOrCreate(
            ['email' => 'sb@calape.com'],
            [
                'name' => 'SB Office Account',
                'phone_number' => '0987654316',
                'address' => '1022',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);
        //17
        $office = User::firstOrCreate(
            ['email' => 'assessor@calape.com'],
            [
                'name' => 'Assessor Office Account',
                'phone_number' => '0987654317',
                'address' => '1101',
                'password' => bcrypt('password'),
            ]
        );
        $office->assignRole($officeRole);


        // Create regular user if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'user@calape.com'],
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
            ['email' => 'test@calape.com'],
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
