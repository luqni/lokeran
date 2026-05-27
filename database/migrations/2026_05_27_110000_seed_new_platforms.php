<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Platform;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $platforms = [
            [
                'name' => 'LinkedIn',
                'icon_path' => 'https://cdn-icons-png.flaticon.com/512/174/174857.png',
                'is_active' => true
            ],
            [
                'name' => 'JobStreet',
                'icon_path' => 'https://ui-avatars.com/api/?name=JobStreet&color=ffffff&background=002F6C&bold=true&size=128',
                'is_active' => true
            ],
            [
                'name' => 'Indeed',
                'icon_path' => 'https://ui-avatars.com/api/?name=Indeed&color=ffffff&background=003A9B&bold=true&size=128',
                'is_active' => true
            ],
            [
                'name' => 'Karir.com',
                'icon_path' => 'https://ui-avatars.com/api/?name=Karir&color=ffffff&background=FF6B00&bold=true&size=128',
                'is_active' => true
            ],
            [
                'name' => 'Loker.id',
                'icon_path' => 'https://ui-avatars.com/api/?name=Loker.id&color=ffffff&background=28A745&bold=true&size=128',
                'is_active' => true
            ],
            [
                'name' => 'Karirhub Kemnaker',
                'icon_path' => 'https://ui-avatars.com/api/?name=Kemnaker&color=ffffff&background=007BFF&bold=true&size=128',
                'is_active' => true
            ]
        ];

        // Deactivate all existing other platforms
        Platform::query()->update(['is_active' => false]);

        foreach ($platforms as $platData) {
            Platform::updateOrCreate(
                ['name' => $platData['name']],
                [
                    'icon_path' => $platData['icon_path'],
                    'is_active' => $platData['is_active']
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reactivate Threads and X if down is called
        Platform::whereIn('name', ['Threads', 'X (Twitter)'])->update(['is_active' => true]);
        
        // Deactivate others
        Platform::whereIn('name', ['JobStreet', 'Indeed', 'Karir.com', 'Loker.id', 'Karirhub Kemnaker'])->update(['is_active' => false]);
    }
};
