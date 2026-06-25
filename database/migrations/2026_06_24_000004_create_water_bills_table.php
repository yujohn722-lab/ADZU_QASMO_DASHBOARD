<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('water_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('reporting_year');
            $table->integer('reporting_month');
            $table->string('responder_name')->nullable();
            $table->decimal('lantaka_annex_a', 10, 2)->nullable();
            $table->decimal('lantaka_old_4_st', 10, 2)->nullable();
            $table->decimal('jr_kitchen', 10, 2)->nullable();
            $table->decimal('main', 10, 2)->nullable();
            $table->decimal('fws_ppo_shop', 10, 2)->nullable();
            $table->decimal('aux_old_dorm', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_bills');
    }
};
