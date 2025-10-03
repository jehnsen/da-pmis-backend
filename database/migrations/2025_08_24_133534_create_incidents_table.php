<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED AUTO_INCREMENT

            $table->string('ref_no', 32)->unique();
            $table->foreignId('type_id')->constrained('incident_types')->restrictOnDelete();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title', 255);
            $table->text('description')->nullable();

            // ENUMs per provided schema
            $table->enum('status', ['open','in_progress','resolved','closed','dismissed'])
                  ->default('open');
            $table->enum('severity', ['low','medium','high','critical'])
                  ->default('medium');

            $table->dateTime('occurred_at');
            $table->dateTime('reported_at');

            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();

            $table->timestamps(); // created_at, updated_at (nullable by default in Laravel 10+)

            // Indexes
            $table->index('school_id', 'idx_incidents_school_id');
            $table->index('type_id', 'idx_incidents_type_id');
            $table->index('status', 'idx_incidents_status');
            $table->index('occurred_at', 'idx_incidents_occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
