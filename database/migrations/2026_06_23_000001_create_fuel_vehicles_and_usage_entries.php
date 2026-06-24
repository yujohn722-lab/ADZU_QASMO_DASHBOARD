<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_name');
            $table->string('plate_number')->nullable();
            $table->string('fuel_type')->default('DIESEL');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('fuel_vehicle_uses', function (Blueprint $table) {
            $table->decimal('total_fuel_liters_loaded', 12, 2)->nullable()->after('total_fuel_cost_incurred');
        });

        Schema::create('fuel_vehicle_use_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_vehicle_use_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fuel_vehicle_id')->constrained('fuel_vehicles');
            $table->decimal('fuel_cost', 12, 2)->default(0);
            $table->decimal('fuel_liters', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['fuel_vehicle_use_id', 'fuel_vehicle_id'], 'fuel_use_vehicle_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_vehicle_use_entries');

        Schema::table('fuel_vehicle_uses', function (Blueprint $table) {
            $table->dropColumn('total_fuel_liters_loaded');
        });

        Schema::dropIfExists('fuel_vehicles');
    }
};
