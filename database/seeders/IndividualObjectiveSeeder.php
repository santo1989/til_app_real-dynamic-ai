<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IndividualObjectiveMaster;

class IndividualObjectiveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $objectives = [
            'Ensure line efficiency target of 85% by optimizing machine layout and operator allocation',
            'Reduce garment rejection rate to below 1.5% through hourly inline quality checks',
            'Minimize fabric wastage during cutting section to less than 2.0% of total yardage',
            'Achieve 100% adherence to safety protocol compliance on the sewing floor',
            'Complete stitching and inspection of designated batch orders within the scheduled lead time',
        ];

        foreach ($objectives as $title) {
            IndividualObjectiveMaster::updateOrCreate(
                ['title' => $title],
                [
                    'is_active' => true,
                    'created_by' => null,
                ]
            );
        }
    }
}
