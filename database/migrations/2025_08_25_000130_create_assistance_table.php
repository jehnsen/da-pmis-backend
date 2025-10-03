<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Your table is singular per your model: protected $table = 'assistance';
        Schema::table('assistance', function (Blueprint $table) {
            // New fields to match your payload
            if (!Schema::hasColumn('assistance', 'description')) {
                $table->string('description')->nullable()->after('assistance_type');
            }
            if (!Schema::hasColumn('assistance', 'amount')) {
                $table->decimal('amount', 16, 2)->default(0)->after('unit');
            }
            if (!Schema::hasColumn('assistance', 'provider_agency')) {
                $table->string('provider_agency')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('assistance', 'received_by_resident_id')) {
                $table->foreignId('received_by_resident_id')->nullable()->after('provider_agency')
                    ->constrained('residents')->nullOnDelete();
            }
            if (!Schema::hasColumn('assistance', 'approved_by_user_id')) {
                $table->foreignId('approved_by_user_id')->nullable()->after('received_by_resident_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('assistance', 'status')) {
                $table->string('status', 20)->default('pending')->after('approved_by_user_id'); // pending|released|received|cancelled
            }

            // Keep legacy columns but support new name: date_provided -> delivered_at (handled in service)
            // Keep 'delivered_by' as legacy equivalent to 'provider_agency' (service will sync)
        });
    }

    public function down(): void
    {
        Schema::table('assistance', function (Blueprint $table) {
            $drop = [];
            foreach (['description','amount','provider_agency','received_by_resident_id','approved_by_user_id','status'] as $c) {
                if (Schema::hasColumn('assistance', $c)) $drop[] = $c;
            }
            if (!empty($drop)) $table->dropColumn($drop);
        });
    }
};
