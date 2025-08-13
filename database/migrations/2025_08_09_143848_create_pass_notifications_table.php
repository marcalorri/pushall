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
        Schema::create('pass_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_pass_id');
            $table->string('title')->nullable();
            $table->text('message');
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            $table->enum('status', ['draft', 'queued', 'sent', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('meta')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('wallet_pass_id')->references('id')->on('wallet_passes')->cascadeOnDelete();
            $table->index(['wallet_pass_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pass_notifications');
    }
};
