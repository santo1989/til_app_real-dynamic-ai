<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('departmental_objective_masters')) {
            Schema::create('departmental_objective_masters', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_id')->nullable();
                $table->string('title');
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->unique(['department_id', 'title']);
                $table->index('is_active');
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            });
        }

        if (Schema::hasTable('objectives')) {
            $rows = DB::table('objectives')
                ->where('type', 'departmental')
                ->whereNotNull('description')
                ->where('description', '!=', '')
                ->select('department_id', 'description')
                ->distinct()
                ->orderBy('description')
                ->get();

            foreach ($rows as $row) {
                DB::table('departmental_objective_masters')->updateOrInsert([
                    'department_id' => $row->department_id,
                    'title' => $row->description,
                ], [
                    'department_id' => $row->department_id,
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
        Schema::dropIfExists('departmental_objective_masters');
    }
};
