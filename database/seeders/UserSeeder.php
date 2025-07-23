<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Master Admin',
                'email' => 'master@banklampung.com',
                'role' => 1, // Master
                'password' => Hash::make('password123'),
                'is_approved' => true,
            ],
            [
                'name' => 'Administrator',
                'email' => 'admin@banklampung.com',
                'role' => 2, // Administrator
                'password' => Hash::make('password123'),
                'is_approved' => true, 
            ],
            [
                'name' => 'Annisa Citra Pratiwi',
                'email' => 'ancit@banklampung.com',
                'role' => 2, // Administrator
                'password' => Hash::make('password123'),
                'is_approved' => true, 
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
