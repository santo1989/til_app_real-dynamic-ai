<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IdpDevelopmentObjective;

class IdpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $skillAreas = [
            'Excel',
            'ERP',
            'Lean Manufacturing',
            'Machine Operation',
            'Quality Control Procedures',
        ];

        foreach ($skillAreas as $area) {
            IdpDevelopmentObjective::updateOrCreate(
                ['skill_area' => $area],
                [
                    'is_active' => true,
                    'created_by' => null,
                ]
            );
        }
    }
}
