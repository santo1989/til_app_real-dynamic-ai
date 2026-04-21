<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('objectives', 'certifying_authority')) {
                $table->string('certifying_authority')->nullable()->after('target');
            }
        });
    }

    public function down(): void
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (Schema::hasColumn('objectives', 'certifying_authority')) {
                $table->dropColumn('certifying_authority');
            }
        });
    }
};
