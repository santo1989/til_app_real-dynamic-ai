<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInnovationFlagToObjectivesTable extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->boolean('is_innovation_achieved')->default(false)->after('certifying_authority');
            $table->text('innovation_remarks')->nullable()->after('is_innovation_achieved');
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropColumn(['is_innovation_achieved', 'innovation_remarks']);
        });
    }
}