<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('electricity_consumptions', function (Blueprint $table) {
            $table->decimal('main_kwh', 12, 2)->nullable()->after('reporting_year');
            $table->decimal('fws_kwh', 12, 2)->nullable()->after('main_kwh');
        });
    }

    public function down(): void
    {
        Schema::table('electricity_consumptions', function (Blueprint $table) {
            $table->dropColumn(['main_kwh', 'fws_kwh']);
        });
    }
};
