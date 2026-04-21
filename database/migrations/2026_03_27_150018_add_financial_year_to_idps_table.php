<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFinancialYearToIdpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (!Schema::hasColumn('idps', 'financial_year')) {
                $table->string('financial_year')->nullable()->after('user_id');
                $table->index('financial_year');
            }
        });

        if (Schema::hasColumn('idps', 'financial_year')) {
            $activeFy = DB::table('financial_years')
                ->where('is_active', true)
                ->orderByDesc('id')
                ->value('label');

            if (empty($activeFy)) {
                $year = (int) now()->format('Y');
                $month = (int) now()->format('n');
                $startYear = $month >= 7 ? $year : $year - 1;
                $activeFy = sprintf('%d-%02d', $startYear, ($startYear + 1) % 100);
            }

            DB::table('idps')
                ->whereNull('financial_year')
                ->update(['financial_year' => $activeFy]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (Schema::hasColumn('idps', 'financial_year')) {
                $table->dropIndex(['financial_year']);
                $table->dropColumn('financial_year');
            }
        });
    }
}
