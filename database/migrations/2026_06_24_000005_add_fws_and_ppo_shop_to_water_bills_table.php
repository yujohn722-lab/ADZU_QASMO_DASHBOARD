<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('water_bills')) {
            return;
        }

        if (! Schema::hasColumn('water_bills', 'fws')) {
            Schema::table('water_bills', function (Blueprint $table) {
                $table->decimal('fws', 10, 2)->nullable()->after('main');
            });
        }

        if (! Schema::hasColumn('water_bills', 'ppo_shop')) {
            Schema::table('water_bills', function (Blueprint $table) {
                $table->decimal('ppo_shop', 10, 2)->nullable()->after('fws');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('water_bills')) {
            return;
        }

        if (Schema::hasColumn('water_bills', 'ppo_shop')) {
            Schema::table('water_bills', function (Blueprint $table) {
                $table->dropColumn('ppo_shop');
            });
        }

        if (Schema::hasColumn('water_bills', 'fws')) {
            Schema::table('water_bills', function (Blueprint $table) {
                $table->dropColumn('fws');
            });
        }
    }
};
