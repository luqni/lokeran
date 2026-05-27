<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table) {
            $table->string('location')->nullable()->after('status');
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('location')->nullable()->after('company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
