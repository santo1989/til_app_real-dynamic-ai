<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            FinancialYearSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            IndividualObjectiveSeeder::class,
            DepartmentalObjectiveSeeder::class,
            IdpSeeder::class,
        ]);
    }
}
