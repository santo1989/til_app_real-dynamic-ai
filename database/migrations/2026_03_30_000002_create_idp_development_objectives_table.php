<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateIdpDevelopmentObjectivesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('idp_development_objectives')) {
            Schema::create('idp_development_objectives', function (Blueprint $table) {
                $table->id();
                $table->string('skill_area');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique('skill_area', 'idp_dev_obj_unique_skill_area');
                $table->index('is_active');
            });
        }

        if (Schema::hasTable('idps') && Schema::hasTable('idp_development_objectives')) {
            $rows = DB::table('idps')
                ->whereNotNull('skill_area')
                ->whereNotNull('description')
                ->where('skill_area', '!=', '')
                ->where('description', '!=', '')
                ->select('skill_area', 'description')
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                DB::table('idp_development_objectives')->updateOrInsert(
                    [
                        'skill_area' => $row->skill_area,
                    ],
                    [
                        'is_active' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('idp_development_objectives');
    }
}
