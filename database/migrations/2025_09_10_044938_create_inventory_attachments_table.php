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
        Schema::create('inventory_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $t->string('file_path');                 // storage/app/...
            $t->string('file_type')->nullable();     // mime or logical type
            $t->string('original_name')->nullable(); // original filename
            $t->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_attachments');
    }
};
