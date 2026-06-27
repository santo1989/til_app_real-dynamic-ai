<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateIdpsTableForNewColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('idps', function (Blueprint $table) {
            $table->dropColumn(['attainment', 'visible_demonstration']);
            $table->text('tracking_indicator')->nullable()->after('review_date');
            $table->text('action_points_agreed')->nullable()->after('tracking_indicator');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('idps', function (Blueprint $table) {
            $table->boolean('attainment')->nullable();
            $table->text('visible_demonstration')->nullable();
            $table->dropColumn(['tracking_indicator', 'action_points_agreed']);
        });
    }
}
