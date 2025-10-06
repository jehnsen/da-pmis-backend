<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livestock_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('livestock_type');
            $table->bigInteger('population')->default(0);
            $table->string('unit', 50);
            $table->date('recorded_date')->nullable();
            $table->year('fiscal_year');
            $table->timestamps();

            $table->index('region_id');
            $table->index('fiscal_year');
            $table->index('livestock_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livestock_statistics');
    }
};
