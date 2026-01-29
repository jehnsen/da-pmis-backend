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
        Schema::create('approval_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('attempted_action', ['approve', 'reject', 'request_changes', 'revoke', 'submit']);
            $table->enum('result', ['success', 'failed', 'unauthorized', 'conflict']);
            $table->string('failure_reason')->nullable();
            $table->string('user_level')->nullable(); // The level user tried to act as
            $table->string('project_status_at_attempt')->nullable(); // Project status when attempt was made
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('request_data')->nullable(); // Store attempt details
            $table->timestamps();

            // Indexes for querying and security monitoring
            $table->index(['project_id', 'result']);
            $table->index(['user_id', 'result']);
            $table->index(['attempted_action', 'result']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_attempts');
    }
};
