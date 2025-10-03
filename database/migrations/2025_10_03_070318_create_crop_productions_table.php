<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('crop_name');
            $table->decimal('production_volume', 15, 2)->nullable();
            $table->string('unit')->default('kg');
            $table->date('harvest_date')->nullable();
            $table->year('fiscal_year');
            $table->timestamps();

            $table->index('region_id');
            $table->index('fiscal_year');
            $table->index('crop_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_productions');
    }
};
