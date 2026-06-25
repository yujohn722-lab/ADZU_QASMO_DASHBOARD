<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->decimal('petron_ado', 8, 2)->nullable()->after('shell_v_power_premium_sport');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_prices', function (Blueprint $table) {
            $table->dropColumn('petron_ado');
        });
    }
};
