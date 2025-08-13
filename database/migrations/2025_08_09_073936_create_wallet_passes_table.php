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
        Schema::create('wallet_passes', function (Blueprint $table) {
            // Primary key (UUID)
            $table->uuid('id')->primary();

            // Owner
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Platform and type enums
            $table->enum('platform', ['apple', 'google']);
            $table->enum('type', ['generic', 'loyalty', 'offer', 'event']);

            // Identifiers
            // Apple
            $table->string('serial_number')->nullable()->unique();
            // Google
            $table->string('class_id')->nullable(); // {issuerId}.{classId}
            $table->string('object_id')->nullable()->unique(); // {issuerId}.{objectId}

            // Apple device registration
            $table->string('device_library_identifier')->nullable();
            $table->string('push_token')->nullable();

            // Status
            $table->enum('status', ['draft', 'active', 'revoked', 'expired'])->default('draft');

            // Arbitrary metadata
            $table->json('meta')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['platform', 'status']);
            $table->index('class_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_passes');
    }
};
