<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departmental_objective_assignments', function (Blueprint $table) {
            $table->string('midterm_status')->nullable()->after('certifying_authority_user_id'); // triggered, completed
            $table->string('final_status')->nullable()->after('midterm_status'); // triggered, completed
            $table->text('midterm_notes')->nullable()->after('final_status');
            $table->decimal('final_score', 8, 2)->nullable()->after('midterm_notes');
            $table->string('final_rating')->nullable()->after('final_score');
        });
    }

    public function down()
    {
        Schema::table('departmental_objective_assignments', function (Blueprint $table) {
            $table->dropColumn(['midterm_status', 'final_status', 'midterm_notes', 'final_score', 'final_rating']);
        });
    }
};
