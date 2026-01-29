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
     * Adds sector_id and geographic location references to projects table.
     * This enables proper routing of approvals through the RA 7160 hierarchy:
     * Barangay → Municipality → Province → Governor
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Add sector_id (will eventually replace department_id)
            $table->foreignId('sector_id')
                ->nullable()
                ->after('department_id')
                ->constrained('lgu_sectors')
                ->nullOnDelete();

            // Add geographic location references
            $table->foreignId('municipality_id')
                ->nullable()
                ->after('sector_id')
                ->constrained('municipalities')
                ->nullOnDelete()
                ->comment('Municipality where project is implemented');

            $table->foreignId('province_id')
                ->nullable()
                ->after('municipality_id')
                ->constrained('provinces')
                ->nullOnDelete()
                ->comment('Province (auto-filled from municipality)');

            $table->string('barangay')
                ->nullable()
                ->after('province_id')
                ->comment('Barangay name (free text for now)');

            // Add indexes for performance
            $table->index('sector_id');
            $table->index('municipality_id');
            $table->index('province_id');
            $table->index('barangay');
        });

        // Migrate existing projects: Map department_id to sector_id
        // This mapping assumes the following department → sector alignment:
        // - Agricultural departments → Economic Services
        // - Health/Social departments → Social Services
        // - Infrastructure departments → Infrastructure & Environmental Management
        // - Admin departments → General Public Services

        // Get sector IDs
        $economicSector = DB::table('lgu_sectors')->where('code', 'ES')->value('id');
        $socialSector = DB::table('lgu_sectors')->where('code', 'SS')->value('id');
        $infraSector = DB::table('lgu_sectors')->where('code', 'IEM')->value('id');
        $generalSector = DB::table('lgu_sectors')->where('code', 'GPS')->value('id');

        if ($economicSector) {
            // Map agricultural and economic departments to Economic Services
            DB::table('projects')
                ->whereIn('department_id', function ($query) {
                    $query->select('id')
                        ->from('departments')
                        ->where(function ($q) {
                            $q->where('name', 'LIKE', '%agri%')
                                ->orWhere('name', 'LIKE', '%fish%')
                                ->orWhere('name', 'LIKE', '%livestock%')
                                ->orWhere('name', 'LIKE', '%crop%')
                                ->orWhere('name', 'LIKE', '%tourism%')
                                ->orWhere('name', 'LIKE', '%trade%')
                                ->orWhere('name', 'LIKE', '%economic%');
                        });
                })
                ->update(['sector_id' => $economicSector]);
        }

        if ($socialSector) {
            // Map social departments to Social Services
            DB::table('projects')
                ->whereIn('department_id', function ($query) {
                    $query->select('id')
                        ->from('departments')
                        ->where(function ($q) {
                            $q->where('name', 'LIKE', '%health%')
                                ->orWhere('name', 'LIKE', '%education%')
                                ->orWhere('name', 'LIKE', '%social%')
                                ->orWhere('name', 'LIKE', '%welfare%')
                                ->orWhere('name', 'LIKE', '%community%');
                        });
                })
                ->update(['sector_id' => $socialSector]);
        }

        if ($infraSector) {
            // Map infrastructure departments
            DB::table('projects')
                ->whereIn('department_id', function ($query) {
                    $query->select('id')
                        ->from('departments')
                        ->where(function ($q) {
                            $q->where('name', 'LIKE', '%infra%')
                                ->orWhere('name', 'LIKE', '%public works%')
                                ->orWhere('name', 'LIKE', '%engineer%')
                                ->orWhere('name', 'LIKE', '%environment%')
                                ->orWhere('name', 'LIKE', '%natural%')
                                ->orWhere('name', 'LIKE', '%disaster%');
                        });
                })
                ->update(['sector_id' => $infraSector]);
        }

        if ($generalSector) {
            // Map administrative departments to General Public Services
            DB::table('projects')
                ->whereIn('department_id', function ($query) {
                    $query->select('id')
                        ->from('departments')
                        ->where(function ($q) {
                            $q->where('name', 'LIKE', '%admin%')
                                ->orWhere('name', 'LIKE', '%planning%')
                                ->orWhere('name', 'LIKE', '%legal%')
                                ->orWhere('name', 'LIKE', '%budget%')
                                ->orWhere('name', 'LIKE', '%finance%')
                                ->orWhere('name', 'LIKE', '%general%');
                        });
                })
                ->update(['sector_id' => $generalSector]);
        }

        // For any remaining projects without sector_id, default to Economic Services
        // (assuming agriculture-focused system)
        if ($economicSector) {
            DB::table('projects')
                ->whereNull('sector_id')
                ->update(['sector_id' => $economicSector]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['sector_id']);
            $table->dropForeign(['municipality_id']);
            $table->dropForeign(['province_id']);

            $table->dropIndex(['sector_id']);
            $table->dropIndex(['municipality_id']);
            $table->dropIndex(['province_id']);
            $table->dropIndex(['barangay']);

            $table->dropColumn(['sector_id', 'municipality_id', 'province_id', 'barangay']);
        });
    }
};
