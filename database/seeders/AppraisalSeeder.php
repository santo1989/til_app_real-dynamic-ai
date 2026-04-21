<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appraisal;
use App\Models\FinancialYear;
use App\Models\User;

class AppraisalSeeder extends Seeder
{
    public function run()
    {
        $emp = User::where('employee_id', 'E001')->first();
        if (!$emp) {
            return;
        }

        $fyLabel = FinancialYear::getActiveName() ?: FinancialYear::query()->orderByDesc('start_date')->value('label');
        if (empty($fyLabel)) {
            return;
        }

        // Create sample appraisals with different statuses
        Appraisal::create([
            'user_id' => $emp->id,
            'type' => 'objective_setting',
            'status' => 'completed',
            'date' => now()->subMonths(2),
            'comments' => 'Initial objectives set for FY ' . $fyLabel,
            'financial_year' => $fyLabel
        ]);

        Appraisal::create([
            'user_id' => $emp->id,
            'type' => 'midterm',
            'status' => 'pending',
            'date' => now()->subMonth(),
            'comments' => 'Midterm review scheduled',
            'financial_year' => $fyLabel
        ]);

        // Get more users and create appraisals for them
        $users = User::where('role', 'employee')->take(5)->get();
        foreach ($users as $user) {
            Appraisal::create([
                'user_id' => $user->id,
                'type' => 'objective_setting',
                'status' => rand(0, 1) ? 'completed' : 'pending',
                'date' => now()->subMonths(rand(1, 3)),
                'comments' => 'Objectives set for ' . $user->name,
                'financial_year' => $fyLabel
            ]);
        }
    }
}
