<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('idps', function (Blueprint $table) {
            if (!Schema::hasColumn('idps', 'attainment')) {
                $table->boolean('attainment')->nullable()->after('status');
            }
            if (!Schema::hasColumn('idps', 'visible_demonstration')) {
                $table->text('visible_demonstration')->nullable()->after('attainment');
            }
            if (!Schema::hasColumn('idps', 'hr_input')) {
                $table->text('hr_input')->nullable()->after('visible_demonstration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('idps', function (Blueprint $table) {
            if (Schema::hasColumn('idps', 'hr_input')) {
                $table->dropColumn('hr_input');
            }
            if (Schema::hasColumn('idps', 'visible_demonstration')) {
                $table->dropColumn('visible_demonstration');
            }
            if (Schema::hasColumn('idps', 'attainment')) {
                $table->dropColumn('attainment');
            }
        });
    }
};

