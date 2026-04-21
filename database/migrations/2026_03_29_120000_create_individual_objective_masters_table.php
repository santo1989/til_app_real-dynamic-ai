<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('individual_objective_masters')) {
            Schema::create('individual_objective_masters', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique('title');
                $table->index('is_active');
            });
        }

        if (Schema::hasTable('objectives')) {
            $rows = DB::table('objectives')
                ->where('type', 'individual')
                ->whereNotNull('description')
                ->where('description', '!=', '')
                ->select('description')
                ->distinct()
                ->orderBy('description')
                ->get();

            foreach ($rows as $row) {
                DB::table('individual_objective_masters')->updateOrInsert(['title' => $row->description], [
                    'title' => $row->description,
                    'is_active' => true,
                    'created_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('individual_objective_masters');
    }
};
