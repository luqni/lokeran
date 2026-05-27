<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Platform;
use App\Models\UserToken;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@jobpulse.com',
            'password' => Hash::make('password'),
        ]);

        UserToken::create([
            'user_id' => $user->id,
            'token_balance' => 1000
        ]);

        Platform::insert([
            ['name' => 'LinkedIn', 'icon_path' => 'https://cdn-icons-png.flaticon.com/512/174/174857.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Threads', 'icon_path' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Threads_%28app%29_logo.svg/1024px-Threads_%28app%29_logo.svg.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'X (Twitter)', 'icon_path' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/X_logo_2023.svg/240px-X_logo_2023.svg.png', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
