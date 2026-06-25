<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('solar_performances')
            ->where('building_name', 'Ernesto Carretero (FEC) Building')
            ->update(['building_name' => 'Fr.Ernesto Carretero (FEC) Building']);
    }

    public function down(): void
    {
        DB::table('solar_performances')
            ->where('building_name', 'Fr.Ernesto Carretero (FEC) Building')
            ->update(['building_name' => 'Ernesto Carretero (FEC) Building']);
    }
};
