<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialYear;
use App\Models\Objective;
use App\Models\User;

class ObjectiveSeeder extends Seeder
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

        // Individual objectives: total 70%
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Increase production efficiency', 'weightage' => 25, 'target' => '5% reduction in downtime', 'status' => 'set', 'financial_year' => $fyLabel]);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Reduce scrap rate', 'weightage' => 20, 'target' => 'Scrap <2%', 'status' => 'set', 'financial_year' => $fyLabel]);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Improve on-time delivery', 'weightage' => 15, 'target' => 'OTD > 98%', 'status' => 'set', 'financial_year' => $fyLabel]);
        Objective::create(['user_id' => $emp->id, 'type' => 'individual', 'description' => 'Reduce energy consumption', 'weightage' => 10, 'target' => 'Reduce kWh/unit by 3%', 'status' => 'set', 'financial_year' => $fyLabel]);
    }
}
