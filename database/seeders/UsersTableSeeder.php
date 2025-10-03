<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Peter Parker',
            'email' => 'peter@marvel.com',
            'username' => 'parker',
            'email_verified_at' => now(),
            'password' => '$2y$12$Vo/97jEmATbA40f.oVrXhOh.w9N7z/k32jTVCDjc7eTTq4JwWYt4S', // Use bcrypt hashing for the password
            'remember_token' => \Illuminate\Support\Str::random(10),
            'role' => 1, // Administrator
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
