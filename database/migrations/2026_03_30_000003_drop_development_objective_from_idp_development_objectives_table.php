<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropDevelopmentObjectiveFromIdpDevelopmentObjectivesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('idp_development_objectives')) {
            return;
        }

        if (Schema::hasColumn('idp_development_objectives', 'development_objective')) {
            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->dropUnique('idp_dev_obj_unique_pair');
            });

            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->dropColumn('development_objective');
            });
        }

        if (!Schema::hasColumn('idp_development_objectives', 'skill_area')) {
            return;
        }

        // Check if unique index already exists (MySQL fix)
        $hasUnique = \DB::select(\DB::raw("SHOW INDEX FROM idp_development_objectives WHERE Key_name = 'idp_dev_obj_unique_skill_area'"));
        if (empty($hasUnique)) {
            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->unique('skill_area', 'idp_dev_obj_unique_skill_area');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('idp_development_objectives')) {
            return;
        }

        if (!Schema::hasColumn('idp_development_objectives', 'development_objective')) {
            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->dropUnique('idp_dev_obj_unique_skill_area');
            });

            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->string('development_objective')->nullable();
            });

            Schema::table('idp_development_objectives', function (Blueprint $table) {
                $table->dropUnique('idp_dev_obj_unique_skill_area');
                $table->unique(['skill_area', 'development_objective'], 'idp_dev_obj_unique_pair');
            });
        }
    }
}

return new DropDevelopmentObjectiveFromIdpDevelopmentObjectivesTable();
