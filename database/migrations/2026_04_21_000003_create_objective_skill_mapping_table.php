<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectiveSkillMappingTable extends Migration
{
    public function up()
    {
        Schema::create('objective_skill_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_master_id')->constrained('individual_objective_masters')->onDelete('cascade');
            $table->foreignId('skill_area_id')->constrained('idp_development_objectives')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['objective_master_id', 'skill_area_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('objective_skill_mapping');
    }
}