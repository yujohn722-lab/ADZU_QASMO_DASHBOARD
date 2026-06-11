<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_service_volumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_name');
            $table->unsignedTinyInteger('reporting_month');
            $table->unsignedSmallInteger('reporting_year');
            $table->string('office_unit_name');
            $table->unsignedInteger('student_transactions_count')->nullable();
            $table->text('service_types')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['reporting_year', 'reporting_month']);
            $table->index('office_unit_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_service_volumes');
    }
};
