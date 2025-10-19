<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCompanyRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create demo users
        $users = [
            [
                'id' => '11111111-1111-1111-1111-111111111111',
                'username' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@adamjaya.com',
                'phone' => '08123456789',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'id' => '22222222-2222-2222-2222-222222222222',
                'username' => 'finance',
                'name' => 'Finance Manager',
                'email' => 'finance@adamjaya.com',
                'phone' => '08234567890',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'id' => '33333333-3333-3333-3333-333333333333',
                'username' => 'warehouse',
                'name' => 'Warehouse Staff',
                'email' => 'warehouse@adamjaya.com',
                'phone' => '08345678901',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'id' => '44444444-4444-4444-4444-444444444444',
                'username' => 'viewer',
                'name' => 'Viewer Only',
                'email' => 'viewer@adamjaya.com',
                'phone' => '08456789012',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create($userData);
            
            // Assign roles to users
            switch ($userData['username']) {
                case 'admin':
                    $user->assignRole('super_admin');
                    break;
                case 'finance':
                    $user->assignRole('finance');
                    break;
                case 'warehouse':
                    $user->assignRole('warehouse');
                    break;
                case 'viewer':
                    $user->assignRole('viewer');
                    break;
            }
        }

        // Create user company roles
        $userCompanyRoles = [
            [
                'user_id' => '11111111-1111-1111-1111-111111111111',
                'company_id' => '01234567-89ab-cdef-0123-456789abcdef',
                'role' => 'admin',
                'is_default' => true,
            ],
            [
                'user_id' => '22222222-2222-2222-2222-222222222222',
                'company_id' => '01234567-89ab-cdef-0123-456789abcdef',
                'role' => 'finance',
                'is_default' => true,
            ],
            [
                'user_id' => '33333333-3333-3333-3333-333333333333',
                'company_id' => '01234567-89ab-cdef-0123-456789abcdef',
                'role' => 'warehouse',
                'is_default' => true,
            ],
            [
                'user_id' => '44444444-4444-4444-4444-444444444444',
                'company_id' => '01234567-89ab-cdef-0123-456789abcdef',
                'role' => 'viewer',
                'is_default' => true,
            ],
        ];

        foreach ($userCompanyRoles as $roleData) {
            UserCompanyRole::create($roleData);
        }
    }
}