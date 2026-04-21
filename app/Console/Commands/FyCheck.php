<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialYear;
use App\Services\FinancialYearService;

class FyCheck extends Command
{
    protected $signature = 'app:fy-check {label? : Financial year label, e.g. 2025-26}';
    protected $description = 'Create sample FinancialYear (if missing) and print computed dates via FinancialYearService';

    public function handle()
    {
        $label = (string) ($this->argument('label') ?: FinancialYear::getActiveName() ?: now()->format('Y') . '-' . now()->addYear()->format('y'));

        // Parse a YYYY-YY label to derive FY dates; fallback to the current date window.
        $parts = explode('-', $label);
        $firstYear = (isset($parts[0]) && ctype_digit($parts[0])) ? (int) $parts[0] : (int) now()->format('Y');
        $startDate = sprintf('%04d-07-01', $firstYear);
        $endDate = sprintf('%04d-06-30', $firstYear + 1);

        $fy = FinancialYear::firstOrCreate(
            ['label' => $label],
            ['start_date' => $startDate, 'end_date' => $endDate, 'is_active' => true]
        );

        // ensure only this is active
        FinancialYear::where('id', '!=', $fy->id)->update(['is_active' => false]);
        $fy->update(['is_active' => true]);

        $svc = new FinancialYearService($fy);

        $this->info('Financial Year: ' . $fy->label);
        $this->line('Start: ' . $svc->getStart()->toDateString());
        $this->line('Midterm (6 months): ' . $svc->midtermDate()->toDateString());
        $this->line('9th month cutoff: ' . $svc->ninthMonthCutoff()->toDateString());
        $this->line('Year end: ' . $svc->yearEndDate()->toDateString());

        return 0;
    }
}
