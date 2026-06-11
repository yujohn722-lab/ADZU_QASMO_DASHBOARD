<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedSmallInteger('reporting_year');
            $table->unsignedTinyInteger('week_number');
            $table->decimal('shell_fuel_save_diesel', 8, 2)->nullable();
            $table->decimal('shell_v_power_diesel', 8, 2)->nullable();
            $table->decimal('shell_fuel_save_regular', 8, 2)->nullable();
            $table->decimal('shell_v_power_premium', 8, 2)->nullable();
            $table->decimal('shell_v_power_premium_sport', 8, 2)->nullable();
            $table->decimal('petron_diesel_max', 8, 2)->nullable();
            $table->decimal('petron_turbo_diesel', 8, 2)->nullable();
            $table->decimal('petron_xtra_advance_regular', 8, 2)->nullable();
            $table->decimal('petron_xcs_premium', 8, 2)->nullable();
            $table->decimal('caltex_silver_regular', 8, 2)->nullable();
            $table->decimal('caltex_platinum_premium', 8, 2)->nullable();
            $table->decimal('caltex_diesel', 8, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['reporting_year', 'week_number', 'user_id'], 'fuel_prices_period_user_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};
