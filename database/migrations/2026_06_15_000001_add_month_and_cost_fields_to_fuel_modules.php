<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->unsignedTinyInteger('reporting_month')->nullable()->after('respondent_name');
        });

        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropUnique('fuel_prices_period_user_unique');
        });

        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->unique(['reporting_year', 'reporting_month', 'week_number', 'user_id'], 'fuel_prices_period_user_unique');
        });

        Schema::table('fuel_vehicle_uses', function (Blueprint $table) {
            $table->decimal('total_fuel_cost_incurred', 12, 2)->nullable()->after('reporting_year');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_vehicle_uses', function (Blueprint $table) {
            $table->dropColumn('total_fuel_cost_incurred');
        });

        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropUnique('fuel_prices_period_user_unique');
        });

        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropColumn('reporting_month');
        });

        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->unique(['reporting_year', 'week_number', 'user_id'], 'fuel_prices_period_user_unique');
        });
    }
};
