<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailFieldsToIdpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (!Schema::hasColumn('idps', 'skill_area')) {
                $table->string('skill_area')->nullable();
            }
            if (!Schema::hasColumn('idps', 'expected_benefits')) {
                $table->text('expected_benefits')->nullable();
            }
            if (!Schema::hasColumn('idps', 'action_plan')) {
                $table->text('action_plan')->nullable();
            }
            if (!Schema::hasColumn('idps', 'resources_required')) {
                $table->text('resources_required')->nullable();
            }
            if (!Schema::hasColumn('idps', 'status')) {
                $table->string('status')->default('open');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (Schema::hasColumn('idps', 'resources_required')) {
                $table->dropColumn('resources_required');
            }
            if (Schema::hasColumn('idps', 'action_plan')) {
                $table->dropColumn('action_plan');
            }
            if (Schema::hasColumn('idps', 'expected_benefits')) {
                $table->dropColumn('expected_benefits');
            }
            if (Schema::hasColumn('idps', 'skill_area')) {
                $table->dropColumn('skill_area');
            }
            if (Schema::hasColumn('idps', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
