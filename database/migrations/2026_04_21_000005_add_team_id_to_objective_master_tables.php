<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeamIdToObjectiveMasterTables extends Migration
{
    public function up()
    {
        // Add team_id to individual objective masters
        Schema::table('individual_objective_masters', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null')->after('department_id');
        });

        // Add team_id to departmental objective masters
        Schema::table('departmental_objective_masters', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null')->after('department_id');
        });
    }

    public function down()
    {
        Schema::table('individual_objective_masters', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });

        Schema::table('departmental_objective_masters', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
}