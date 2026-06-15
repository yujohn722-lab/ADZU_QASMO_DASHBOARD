<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('module_key');
            $table->string('module_label');
            $table->string('reportable_type');
            $table->unsignedBigInteger('reportable_id');
            $table->foreignId('respondent_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('admin_message')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['reportable_type', 'reportable_id']);
            $table->index(['module_key', 'status']);
        });

        Schema::create('report_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('report_review_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_notifications');
        Schema::dropIfExists('report_reviews');
    }
};
