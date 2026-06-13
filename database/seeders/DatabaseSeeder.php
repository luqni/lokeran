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
            ['name' => 'Loker.id', 'icon_path' => '...', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KitaLulus', 'icon_path' => '...', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Karirhub Kemnaker', 'icon_path' => '...', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'JobStreet', 'icon_path' => '...', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Indeed', 'icon_path' => '...', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

    }
}
