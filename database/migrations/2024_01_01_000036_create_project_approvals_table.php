<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // who took action
            $table->enum('action', ['submitted', 'approved', 'rejected', 'requested_changes']);
            $table->text('comments')->nullable();
            $table->string('reason')->nullable(); // rejection reason
            $table->enum('level', ['barangay', 'municipal', 'provincial', 'governor']);
            $table->string('from_status')->nullable(); // previous approval status
            $table->string('to_status')->nullable(); // new approval status
            $table->timestamp('action_taken_at');
            $table->timestamps();

            $table->index(['project_id', 'level']);
            $table->index(['user_id', 'action']);
            $table->index('action_taken_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_approvals');
    }
};
