<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('funding_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('funding_source')->nullable();
            $table->year('fiscal_year');
            $table->date('allocated_date')->nullable();
            $table->timestamps();

            $table->index('department_id');
            $table->index('project_id');
            $table->index('fiscal_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('funding_distributions');
    }
};
