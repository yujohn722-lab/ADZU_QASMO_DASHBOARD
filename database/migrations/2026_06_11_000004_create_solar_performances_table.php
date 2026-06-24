<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedTinyInteger('reporting_month');
            $table->unsignedSmallInteger('reporting_year');
            $table->string('building_name');
            $table->decimal('monthly_solar_energy_kwh', 12, 2)->nullable();
            $table->decimal('estimated_savings', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['reporting_year', 'reporting_month']);
            $table->index('building_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_performances');
    }
};
