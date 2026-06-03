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
        Platform::updateOrCreate(
            ['name' => 'KitaLulus'],
            [
                'icon_path' => 'https://ui-avatars.com/api/?name=KitaLulus&color=ffffff&background=00BFFF&bold=true&size=128',
                'is_active' => true
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Platform::where('name', 'KitaLulus')->update(['is_active' => false]);
    }
};
