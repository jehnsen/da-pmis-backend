<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates LGU Sectors table aligned with RA 7160 governance structure.
     * Replaces the 'departments' concept with 'sectors' for provincial governance.
     *
     * LGU Sectors (RA 7160):
     * 1. Social Services (Health, Education, Social Welfare)
     * 2. Economic Services (Agriculture, Tourism, Trade & Industry)
     * 3. Infrastructure & Environmental Management
     * 4. General Public Services (Admin, Planning, Legal)
     */
    public function up(): void
    {
        Schema::create('lgu_sectors', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Social Services"
            $table->string('code')->unique(); // e.g., "SS", "ES", "IEM", "GPS"
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // For UI representation
            $table->string('color_code')->nullable(); // For dashboard visualization
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('display_order');
        });

        // Seed initial LGU sectors
        DB::table('lgu_sectors')->insert([
            [
                'name' => 'Social Services',
                'code' => 'SS',
                'description' => 'Health, Education, Social Welfare, and Community Development programs',
                'icon' => 'users',
                'color_code' => '#3B82F6',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Economic Services',
                'code' => 'ES',
                'description' => 'Agriculture, Fisheries, Tourism, Trade & Industry development',
                'icon' => 'trending-up',
                'color_code' => '#10B981',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Infrastructure & Environmental Management',
                'code' => 'IEM',
                'description' => 'Public works, utilities, environment, natural resources, disaster risk reduction',
                'icon' => 'building',
                'color_code' => '#F59E0B',
                'is_active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'General Public Services',
                'code' => 'GPS',
                'description' => 'Administration, Planning, Legal, Budget, and General Governance',
                'icon' => 'shield',
                'color_code' => '#8B5CF6',
                'is_active' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lgu_sectors');
    }
};
