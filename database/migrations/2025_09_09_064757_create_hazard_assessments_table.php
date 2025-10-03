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
        Schema::create('hazard_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_title');
            $table->foreignId('hazard_id')->constrained('hazards'); // single select Hazard type
            $table->string('location')->index();               // Division/Province/City text
            $table->string('risk_level')->index();             // High/Medium/Low
            $table->text('description')->nullable();           // long description
            $table->longText('methodology')->nullable();
            $table->longText('key_findings')->nullable();
            $table->longText('recommendations')->nullable();
            $table->unsignedInteger('number_of_schools')->default(0);
            $table->string('estimated_impact')->nullable();    // e.g., Severe, Moderate
            $table->string('status')->default('Draft');        // Draft, Submitted, Validated
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hazard_assessments');
    }
};
