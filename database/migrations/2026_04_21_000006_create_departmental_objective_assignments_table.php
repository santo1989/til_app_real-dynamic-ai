<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Clean up any partial table from failed attempts
        Schema::dropIfExists('departmental_objective_assignments');

        Schema::create('departmental_objective_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            
            $table->unsignedBigInteger('objective_master_id');
            $table->string('timeline')->nullable();
            $table->integer('weightage')->default(0);
            $table->string('certifying_authority_role')->default('line_manager');
            $table->unsignedBigInteger('certifying_authority_user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Manual foreign keys with explicitly short names
            $table->foreign('objective_master_id', 'obj_asgn_master_fk')
                  ->references('id')->on('departmental_objective_masters')
                  ->onDelete('cascade');

            $table->foreign('certifying_authority_user_id', 'obj_asgn_auth_fk')
                  ->references('id')->on('users')
                  ->onDelete('set null');

            $table->foreign('created_by', 'obj_asgn_creator_fk')
                  ->references('id')->on('users')
                  ->onDelete('set null');

            $table->index(['financial_year_id', 'department_id', 'team_id'], 'obj_asgn_scope_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departmental_objective_assignments');
    }
};
