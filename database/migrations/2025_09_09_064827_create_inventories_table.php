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
            // Basic info
            $t->string('equipment_name')->after('id');                 // from UI
            $t->string('equipment_type')->nullable()->after('equipment_name');
            $t->string('category')->nullable()->change();              // keep if already exists
            $t->unsignedInteger('quantity')->default(0)->after('unit'); // UI "Quantity"
            $t->string('condition')->default('Good')->change();        // UI condition dropdown

            // Location (link to school if you have School model)
            $t->foreignId('school_id')->nullable()->after('location')
              ->constrained('schools')->nullOnDelete();                // optional FK
            $t->string('location')->nullable()->change();              // keep free-text fallback

            // Technical details
            $t->string('serial_number')->nullable()->after('location');
            $t->date('purchase_date')->nullable()->after('serial_number');
            $t->string('warranty_period')->nullable()->after('purchase_date'); // e.g., "2 years"
            $t->decimal('purchase_cost', 12, 2)->nullable()->after('warranty_period');
            $t->string('supplier')->nullable()->after('purchase_cost');

            // Additional info
            $t->longText('description')->nullable()->after('supplier');
            $t->boolean('is_qrf_funded')->default(false)->after('description'); // checkbox
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
