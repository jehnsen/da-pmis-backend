<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('incident_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable(); // reported|verified|responding|resolved|closed
            $table->string('to_status', 20);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'changed_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('incident_status_histories');
    }
};