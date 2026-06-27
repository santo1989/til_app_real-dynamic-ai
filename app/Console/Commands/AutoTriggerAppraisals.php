<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialYear;
use App\Models\Appraisal;
use App\Models\User;
use App\Models\DepartmentalObjectiveAssignment;
use App\Models\AuditLog;
use App\Services\FinancialYearService;
use Illuminate\Support\Facades\Log;

class AutoTriggerAppraisals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appraisals:auto-trigger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically trigger midterm and final appraisals based on financial year dates';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $activeModel = FinancialYear::getActive();
        
        if (!$activeModel) {
            $this->info('No active financial year found. Auto-trigger skipped.');
            return 0;
        }

        $fyService = new FinancialYearService($activeModel);
        $activeFY = $fyService->label();

        $this->info("Checking auto-triggers for Financial Year: {$activeFY}");

        $triggeredSomething = false;

        // --- MIDTERM CHECK ---
        if ($fyService->isOnOrAfterMidterm(now())) {
            $this->info('We are in or past the Midterm window. Checking for pending midterms...');
            
            // 1. Departmental Midterms
            $deptMidtermCount = DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel->id)
                ->whereNull('midterm_status')
                ->update(['midterm_status' => 'triggered']);

            if ($deptMidtermCount > 0) {
                $this->logAction('system_bulk_dept_midterm_triggered', "System automatically triggered bulk departmental midterm reviews for {$deptMidtermCount} assignments.");
                $this->info("Triggered {$deptMidtermCount} departmental midterm reviews.");
                $triggeredSomething = true;
            }

            // 2. Individual Midterms
            $employees = User::where('is_active', true)->where('role', 'employee')->get();
            $indivMidtermCount = 0;

            foreach ($employees as $emp) {
                $exists = Appraisal::where('user_id', $emp->id)
                    ->where('type', 'midterm')
                    ->where('financial_year', $activeFY)
                    ->exists();

                if (!$exists) {
                    Appraisal::create([
                        'user_id' => $emp->id,
                        'type' => 'midterm',
                        'status' => Appraisal::STATUS_MIDTERM_TRIGGERED,
                        'financial_year' => $activeFY,
                        'date' => now(),
                        'conducted_by' => null // Null indicates system triggered
                    ]);
                    $indivMidtermCount++;
                }
            }

            if ($indivMidtermCount > 0) {
                $this->logAction('system_bulk_midterm_triggered', "System automatically triggered midterm reviews for {$indivMidtermCount} employees (FY {$activeFY})");
                $this->info("Triggered {$indivMidtermCount} individual midterm reviews.");
                $triggeredSomething = true;
            }
        }

        // --- FINAL YEAR CHECK ---
        if ($fyService->isOnOrAfterYearEnd(now())) {
            $this->info('We are in or past the Final Year window. Checking for pending finals...');

            // 1. Departmental Finals
            $deptFinalCount = DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel->id)
                ->whereNotNull('midterm_status')
                ->whereNull('final_status')
                ->update(['final_status' => 'triggered']);

            if ($deptFinalCount > 0) {
                $this->logAction('system_bulk_dept_final_triggered', "System automatically triggered bulk departmental final evaluations for {$deptFinalCount} assignments.");
                $this->info("Triggered {$deptFinalCount} departmental final evaluations.");
                $triggeredSomething = true;
            }

            // 2. Individual Finals
            $indivFinalCount = Appraisal::where('type', 'midterm')
                ->where('financial_year', $activeFY)
                ->whereIn('status', [Appraisal::STATUS_MIDTERM_TRIGGERED, Appraisal::STATUS_IN_PROGRESS, Appraisal::STATUS_MIDTERM_COMPLETED])
                ->update(['status' => Appraisal::STATUS_READY_FOR_FINAL]);

            if ($indivFinalCount > 0) {
                $this->logAction('system_bulk_final_triggered', "System automatically triggered final evaluations for {$indivFinalCount} employees (FY {$activeFY})");
                $this->info("Triggered {$indivFinalCount} individual final evaluations.");
                $triggeredSomething = true;
            }
        }

        if (!$triggeredSomething) {
            $this->info('No new appraisals needed to be triggered today.');
        } else {
            $this->info('Auto-trigger process completed successfully.');
        }

        return 0;
    }

    private function logAction($action, $details)
    {
        // Find the first Super Admin or HR Admin to assign the log to, or just leave it null if the DB allows
        // Assuming user_id can be null or we use an admin
        $admin = User::whereIn('role', ['super_admin', 'hr_admin'])->first();
        
        AuditLog::create([
            'user_id' => $admin ? $admin->id : 1, // Fallback to 1 if needed
            'action' => $action,
            'details' => $details,
        ]);
        
        Log::info("AutoTriggerAppraisals: {$details}");
    }
}
