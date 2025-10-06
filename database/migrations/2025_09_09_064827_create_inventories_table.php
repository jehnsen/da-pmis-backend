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
        Schema::create('inventories', function (Blueprint $t) {
            $t->id();

            // Basic info
            $t->string('equipment_name');
            $t->string('equipment_type')->nullable();
            $t->string('category')->nullable();
            $t->unsignedInteger('quantity')->default(0);
            $t->string('unit')->nullable(); // unit of measurement
            $t->string('condition')->default('Good'); // Good/Fair/Poor

            // Location
            $t->string('location')->nullable();
            $t->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();

            // Technical details
            $t->string('serial_number')->nullable();
            $t->date('purchase_date')->nullable();
            $t->string('warranty_period')->nullable(); // e.g., "2 years"
            $t->decimal('purchase_cost', 12, 2)->nullable();
            $t->string('supplier')->nullable();

            // Additional info
            $t->longText('description')->nullable();
            $t->boolean('is_qrf_funded')->default(false);

            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
