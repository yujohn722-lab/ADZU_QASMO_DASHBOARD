<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimated_savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedSmallInteger('reporting_year');
            $table->string('office_unit_name');
            $table->text('savings_areas')->nullable();
            $table->decimal('reduced_travel_savings', 14, 2)->nullable();
            $table->decimal('reduced_utilities_savings', 14, 2)->nullable();
            $table->decimal('reduced_activities_savings', 14, 2)->nullable();
            $table->decimal('total_estimated_savings', 14, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('reporting_year');
            $table->index('office_unit_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimated_savings');
    }
};
