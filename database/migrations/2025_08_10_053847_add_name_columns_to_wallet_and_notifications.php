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
            if (!Schema::hasColumn('wallet_passes', 'name')) {
                $table->string('name')->nullable()->after('type');
            }
        });
        if (Schema::hasTable('pass_notifications')) {
            Schema::table('pass_notifications', function (Blueprint $table) {
                if (!Schema::hasColumn('pass_notifications', 'name')) {
                    $table->string('name')->nullable()->after('wallet_pass_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_passes', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_passes', 'name')) {
                $table->dropColumn('name');
            }
        });
        if (Schema::hasTable('pass_notifications')) {
            Schema::table('pass_notifications', function (Blueprint $table) {
                if (Schema::hasColumn('pass_notifications', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};
