<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add municipality_id to users table for RA 7160 territorial jurisdiction compliance
 *
 * Purpose: Enable proper authorization checks to ensure municipal officers can only approve
 * projects within their territorial jurisdiction (municipality).
 *
 * RA 7160 Compliance: Municipal Planning and Development Officers (MPDOs) and Barangay
 * Development Council (BDC) officers should only approve projects within their municipality.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add municipality_id for territorial jurisdiction
            $table->foreignId('municipality_id')
                ->nullable()
                ->after('department_id')
                ->constrained('municipalities')
                ->nullOnDelete();

            // Add index for performance on jurisdiction queries
            $table->index('municipality_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['municipality_id']);
            $table->dropIndex(['municipality_id']);
            $table->dropColumn('municipality_id');
        });
    }
};
