<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE `appraisals`
            MODIFY `status` ENUM(
                'draft',
                'pending',
                'in_progress',
                'completed',
                'approved',
                'midterm_triggered',
                'midterm_completed',
                'ready_for_final',
                'final_completed',
                'pending_hr'
            ) NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE `appraisals`
            MODIFY `status` ENUM(
                'draft',
                'pending',
                'in_progress',
                'completed',
                'approved'
            ) NOT NULL DEFAULT 'draft'
        ");
    }
};

