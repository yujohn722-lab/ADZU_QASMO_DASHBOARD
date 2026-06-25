<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('report_types')->nullable()->after('approved_at');
        });

        DB::table('users')->update([
            'report_types' => json_encode([
                'fuel-prices',
                'electricity-consumptions',
                'fuel-vehicle-uses',
                'solar-performances',
                'student-service-volumes',
                'estimated-savings',
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('report_types');
        });
    }
};
