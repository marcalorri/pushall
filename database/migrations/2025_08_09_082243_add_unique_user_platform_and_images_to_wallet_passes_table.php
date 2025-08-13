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
        Schema::table('wallet_passes', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_passes', 'image_path')) {
                $table->string('image_path')->nullable()->after('meta');
            }

            // Add composite unique index for one pass per user+platform
            $table->unique(['user_id', 'platform'], 'wallet_passes_user_platform_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            // Drop unique index and image column if exist
            $table->dropUnique('wallet_passes_user_platform_unique');
            if (Schema::hasColumn('wallet_passes', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
