<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepartmentalObjectiveMaster;
use App\Models\Department;

class DepartmentalObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $mfg = Department::where('name', 'Manufacturing')->first();
        $sales = Department::where('name', 'Sales')->first();
        $hr = Department::where('name', 'HR')->first();
        $mgmt = Department::where('name', 'Management')->first();
        $it = Department::where('name', 'IT')->first();

        $objectives = [
            [
                'department_id' => $mfg ? $mfg->id : null,
                'title' => 'Optimize production scheduling to reduce changeover downtime by 20%',
            ],
            [
                'department_id' => $sales ? $sales->id : null,
                'title' => 'Secure 3 new international buyer accounts with minimum order values of $200k',
            ],
            [
                'department_id' => $hr ? $hr->id : null,
                'title' => 'Reduce operator absenteeism by 15% through employee engagement and incentive programs',
            ],
            [
                'department_id' => $mgmt ? $mgmt->id : null,
                'title' => 'Increase overall factory capacity utilization to 90% across all shifts',
            ],
            [
                'department_id' => $it ? $it->id : null,
                'title' => 'Deploy a computerized real-time production tracking system on the shop floor',
            ],
        ];

        foreach ($objectives as $obj) {
            DepartmentalObjectiveMaster::updateOrCreate(
                [
                    'department_id' => $obj['department_id'],
                    'title' => $obj['title']
                ],
                [
                    'is_active' => true,
                    'created_by' => null,
                ]
            );
        }
    }
}
