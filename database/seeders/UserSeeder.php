<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'      => 'Essis Cedric',
            'email'     => 'cedric@example.com',
            'password'  => Hash::make('password123'),
            'role'      => 'worker',
            'status'    => 'active',
            'avatar'    => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            'profession' => 'Project Manager',
            'skills' => json_encode(['Leadership', 'Agile Methodologies', 'Communication']),
            'experience_years' => 5,
            'availability'   => true
        ]);

        User::create([
            'name'      => 'Akali Adin',
            'email'     => 'admin@example.com',
            'password'  => Hash::make('password123'),
            'role'      => 'manager',
            'status'    => 'active',
            'avatar'    => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            'company_name'     => 'Tech Solutions',
            'company_activity' => 'Software Development',
            'company_verified' => true,

        ]);

        User::create([
    'name'     => 'Super Admin',
    'email'    => 'admin@mye.com',
    'password' => Hash::make('admin123'),
    'role'     => 'admin',
    'status'   => 'active',
]);

    }
}
