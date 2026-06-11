<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('electricity_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedTinyInteger('reporting_month');
            $table->unsignedSmallInteger('reporting_year');
            $table->decimal('father_ernesto_carretero_kwh', 12, 2)->nullable();
            $table->decimal('canisius_gonzaga_xavier_kwh', 12, 2)->nullable();
            $table->decimal('bellarmine_campion_kwh', 12, 2)->nullable();
            $table->decimal('senior_high_school_kwh', 12, 2)->nullable();
            $table->decimal('sauras_kwh', 12, 2)->nullable();
            $table->decimal('college_of_law_kwh', 12, 2)->nullable();
            $table->decimal('jesuit_residence_kwh', 12, 2)->nullable();
            $table->decimal('total_salvador_kwh', 12, 2)->nullable();
            $table->decimal('grade_school_complex_kwh', 12, 2)->nullable();
            $table->decimal('junior_high_school_kwh', 12, 2)->nullable();
            $table->decimal('total_kreutz_kwh', 12, 2)->nullable();
            $table->decimal('total_lantaka_kwh', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['reporting_year', 'reporting_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electricity_consumptions');
    }
};
