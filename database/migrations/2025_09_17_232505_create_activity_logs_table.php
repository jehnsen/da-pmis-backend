<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id();
            $t->string('action', 100);                     // e.g., created, updated, approved
            $t->text('description')->nullable();           // human-readable summary

            // Polymorphic subject (what the action was performed on)
            $t->string('subject_type')->nullable();
            $t->unsignedBigInteger('subject_id')->nullable();
            $t->index(['subject_type','subject_id']);

            // Who performed the action
            $t->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Additional context
            $t->json('meta')->nullable();                  // arbitrary JSON (diffs, fields, refs)
            $t->string('ip_address', 45)->nullable();      // IPv4/IPv6
            $t->string('user_agent', 512)->nullable();

            $t->timestamps();
            $t->index('action');
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
