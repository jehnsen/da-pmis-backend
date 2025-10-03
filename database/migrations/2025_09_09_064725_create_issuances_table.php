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
        Schema::create('issuances', function (Blueprint $table) {
            $table->id();
            $table->string('type');                // Memo, Circular, Advisory
            $table->string('level');               // Regional, Division, School
            $table->unsignedSmallInteger('series_year');
            $table->string('reference_number');    // e.g., 021
            $table->string('title');
            $table->date('effective_date')->index();
            $table->date('valid_until')->nullable();
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issuances');
    }
};
