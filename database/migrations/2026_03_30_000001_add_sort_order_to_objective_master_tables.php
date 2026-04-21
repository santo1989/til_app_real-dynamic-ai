<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToObjectiveMasterTables extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('individual_objective_masters') && !Schema::hasColumn('individual_objective_masters', 'sort_order')) {
            Schema::table('individual_objective_masters', function (Blueprint $table) {
                $table->integer('sort_order')->default(100)->after('is_active');
            });
        }

        if (Schema::hasTable('departmental_objective_masters') && !Schema::hasColumn('departmental_objective_masters', 'sort_order')) {
            Schema::table('departmental_objective_masters', function (Blueprint $table) {
                $table->integer('sort_order')->default(100)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('individual_objective_masters') && Schema::hasColumn('individual_objective_masters', 'sort_order')) {
            Schema::table('individual_objective_masters', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }

        if (Schema::hasTable('departmental_objective_masters') && Schema::hasColumn('departmental_objective_masters', 'sort_order')) {
            Schema::table('departmental_objective_masters', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
}
