<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Essis Cedric',
            'email' => 'cedric@example',
            'password' => Hash::make('password'),
            'role' => 'worker',
            'avatar' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            'created_at' => now(),
            'updated_at' => now(),
            'google_id' => null

        ]);

        DB::table('users')->insert([
            'name' => 'Akali Adin',
            'email' => 'admin@example',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'avatar' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            'created_at' => now(),
            'updated_at' => now(),
            'google_id' => null

        ]);
    }
}
