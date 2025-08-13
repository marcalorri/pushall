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
            $columns = [
                'logo_path',
                'strip_path',
                'background_path',
                'thumbnail_path',
                'icon_path',
            ];

            foreach ($columns as $col) {
                if (!Schema::hasColumn('wallet_passes', $col)) {
                    $table->string($col)->nullable()->after('image_path');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            $columns = [
                'logo_path',
                'strip_path',
                'background_path',
                'thumbnail_path',
                'icon_path',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('wallet_passes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
