<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_vehicle_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedTinyInteger('reporting_month');
            $table->unsignedSmallInteger('reporting_year');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['reporting_year', 'reporting_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_vehicle_uses');
    }
};
