<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assistance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained('incidents')->cascadeOnDelete();

            // Assistance details
            $table->string('assistance_type'); // relief goods, financial, medical, etc.
            $table->string('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit')->nullable(); // packs, kg, pieces, etc.
            $table->decimal('amount', 16, 2)->default(0);

            // Provider information
            $table->string('provider_agency')->nullable();
            $table->string('delivered_by')->nullable(); // legacy field
            $table->timestamp('date_provided')->nullable(); // legacy field
            $table->timestamp('delivered_at')->nullable();

            // Recipient information
            $table->unsignedBigInteger('received_by_resident_id')->nullable(); // FK can be added later when residents table exists

            // Approval workflow
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending|released|received|cancelled

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance');
    }
};
