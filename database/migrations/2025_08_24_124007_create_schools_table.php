// database/migrations/2025_08_24_000003_create_schools_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->cascadeOnDelete();
            $table->string('school_id_code', 64)->unique();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 32)->nullable();
            $table->integer('enrollment')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('risk_status', ['none', 'flood', 'earthquake', 'typhoon', 'multi'])->default('none');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('schools');
    }
};
