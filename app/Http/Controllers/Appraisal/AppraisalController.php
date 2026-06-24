<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appraisal;
use App\Models\Objective;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\FinancialYear;
use App\Models\Pip;
use App\Models\Idp;
use Illuminate\Support\Facades\DB;
use App\Services\FinancialYearService;
use App\Services\PerformanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Requests\YearendAssessmentRequest;
use App\Http\Requests\MidtermRevisionRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppraisalController extends Controller
{
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? $activeModel->label : null;
        
        if (!$activeModel) {
            $midtermList = [];
            $finalYearList = [];
            $deptMidtermList = [];
            $deptFinalList = [];
            return view('appraisal.super_admin.appraisals_index', compact('midtermList', 'finalYearList', 'deptMidtermList', 'deptFinalList', 'activeFY'));
        }

        /* 
        $midtermThreshold = \Carbon\Carbon::parse($activeModel->start_date)->addMonths(6);
        $isMidtermWindow = now()->greaterThanOrEqualTo($midtermThreshold);
        */

        // DEMO MODE: Force midterm window to be open for presentation
        $isMidtermWindow = true;
        $midtermThreshold = \Carbon\Carbon::parse($activeModel->start_date)->addMonths(6); // Keep for view context

        // Fetch all active staff with 'employee' role
        $employees = User::where('is_active', true)
                         ->where('role', 'employee')
                         ->get();

        $midtermList = [];
        $finalYearList = [];

        foreach ($employees as $emp) {
            $midTermApp = Appraisal::where('user_id', $emp->id)
                ->where('financial_year', $activeFY)
                ->where('type', 'midterm')
                ->first();

            // Midterm Logic: Eligible if 6 months passed AND not yet completed
            if ($isMidtermWindow) {
                // Now and in the future, we use the specific midterm_triggered status
                $isMidtermActive = $midTermApp && in_array($midTermApp->status, [
                    Appraisal::STATUS_MIDTERM_TRIGGERED, 
                    Appraisal::STATUS_IN_PROGRESS,
                    Appraisal::STATUS_DRAFT // Legacy support
                ]);

                if (!$midTermApp || $isMidtermActive) {
                    $midtermList[] = [
                        'user' => $emp,
                        'status' => $midTermApp ? $midTermApp->status : 'eligible',
                        'appraisal_id' => $midTermApp ? $midTermApp->id : null
                    ];
                }
            }

            // Final Year Logic: Ready if midterm completed
            if ($midTermApp && in_array($midTermApp->status, [
                Appraisal::STATUS_MIDTERM_COMPLETED,
                Appraisal::STATUS_READY_FOR_FINAL,
                Appraisal::STATUS_FINAL_COMPLETED
            ])) {
                $finalYearList[] = [
                    'user' => $emp,
                    'status' => $midTermApp->status
                ];
            }
        }

        // Departmental Assignments Status Tracking
        $deptAssignments = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel->id)
            ->with(['department', 'certifyingAuthorityUser', 'master'])
            ->get();

        $deptMidtermList = [];
        $deptFinalList = [];

        foreach ($deptAssignments as $asgn) {
            // Midterm: show all
            $deptMidtermList[] = $asgn;
            
            // Final: show all where midterm is completed
            if ($asgn->midterm_status === 'completed') {
                $deptFinalList[] = $asgn;
            }
        }

        return view('appraisal.super_admin.appraisals_index', compact(
            'midtermList', 'finalYearList', 'deptMidtermList', 'deptFinalList', 
            'activeFY', 'isMidtermWindow', 'midtermThreshold'
        ));
    }

    public function triggerDeptMidterm($id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::findOrFail($id);
        $asgn->update(['midterm_status' => 'triggered']);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'dept_midterm_triggered',
            'details' => "HR triggered departmental midterm review for Dept: {$asgn->department->name}, Authority: {$asgn->certifyingAuthorityUser->name}",
        ]);

        return back()->with('success', 'Departmental midterm review triggered.');
    }

    public function triggerAllDeptMidterms()
    {
        $activeFY = FinancialYear::getActive();
        if (!$activeFY) return back()->withErrors(['message' => 'No active financial year.']);

        $count = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeFY->id)
            ->whereNull('midterm_status')
            ->update(['midterm_status' => 'triggered']);

        if ($count > 0) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_dept_midterm_triggered',
                'details' => "HR triggered bulk departmental midterm reviews for {$count} assignments.",
            ]);
            return back()->with('success', "Triggered {$count} departmental midterm reviews.");
        }

        return back()->with('info', 'No new departmental objectives eligible for trigger.');
    }

    public function triggerDeptFinal($id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::findOrFail($id);
        $asgn->update(['final_status' => 'triggered']);
        
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'dept_final_triggered',
            'details' => "HR triggered departmental final evaluation for Dept: {$asgn->department->name}, Authority: {$asgn->certifyingAuthorityUser->name}",
        ]);

        return back()->with('success', 'Departmental final evaluation triggered.');
    }

    public function triggerAllDeptFinals()
    {
        $activeFY = FinancialYear::getActive();
        if (!$activeFY) return back()->withErrors(['message' => 'No active financial year.']);

        $count = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeFY->id)
            ->where('midterm_status', 'completed')
            ->whereNull('final_status')
            ->update(['final_status' => 'triggered']);

        if ($count > 0) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_dept_final_triggered',
                'details' => "HR triggered bulk departmental final evaluations for {$count} assignments.",
            ]);
            return back()->with('success', "Triggered {$count} departmental final evaluations.");
        }

        return back()->with('info', 'No new departmental objectives eligible for trigger.');
    }

    public function triggerMidterm($user_id)
    {
        $employee = User::findOrFail($user_id);
        $activeFY = FinancialYear::getActiveName();
        
        if (!$activeFY) {
            return back()->withErrors(['message' => 'No active financial year found.']);
        }

        $appraisal = Appraisal::updateOrCreate(
            [
                'user_id' => $employee->id,
                'type' => 'midterm',
                'financial_year' => $activeFY
            ],
            [
                'status' => Appraisal::STATUS_MIDTERM_TRIGGERED,
                'date' => now(),
                'conducted_by' => auth()->id() // HR Initiated
            ]
        );

        // SYNC DEPARTMENTAL OBJECTIVES
        // $this->syncDepartmentalObjectives($employee); // Obsolete: Dept objectives read dynamically

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'midterm_review_triggered',
            'details' => "HR triggered midterm review for {$employee->name} (FY {$activeFY})",
        ]);

        return back()->with('success', "Midterm review request sent for {$employee->name}. Line Manager will be notified.");
    }

    // --- Line Manager Midterm Workflow ---

    public function midtermList()
    {
        $activeFY = FinancialYear::getActiveName();
        if (!$activeFY) return back()->withErrors(['message' => 'No active financial year.']);

        // Employees who are in my department OR have objectives assigned to me as authority
        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('department_id', auth()->user()->department_id)
                  ->orWhereHas('objectives', function($oq) {
                      $oq->where('certifying_authority_user_id', auth()->id());
                  });
            })
            ->whereHas('appraisals', function($q) use ($activeFY) {
                $q->where('type', 'midterm')
                  ->where('financial_year', $activeFY)
                  ->whereIn('status', [Appraisal::STATUS_MIDTERM_TRIGGERED, Appraisal::STATUS_IN_PROGRESS, Appraisal::STATUS_MIDTERM_COMPLETED]);
            })
            ->get();

        return view('appraisal.line_manager.midterm_list', compact('employees', 'activeFY'));
    }

    public function storeMidterm(Request $request)
    {
        $appraisal = Appraisal::findOrFail($request->appraisal_id);
        
        // Store per-objective notes in JSON
        $notes = $request->input('notes', []);
        $appraisal->update([
            'ratings' => ['notes' => $notes],
            'status' => Appraisal::STATUS_MIDTERM_COMPLETED,
            'date' => now()->toDateString()
        ]);

        return redirect()->route('appraisal.midterm.list')->with('success', 'Midterm notes saved successfully.');
    }

    // --- Line Manager Final Year Workflow ---

    public function finalList()
    {
        $activeFY = FinancialYear::getActiveName();
        if (!$activeFY) return back()->withErrors(['message' => 'No active financial year.']);

        // Employees who are in my department OR have objectives assigned to me as authority
        $statuses = [Appraisal::STATUS_READY_FOR_FINAL, Appraisal::STATUS_FINAL_COMPLETED];

        $employees = User::where('role', 'employee')
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('department_id', auth()->user()->department_id)
                  ->orWhereHas('objectives', function($oq) {
                      $oq->where('certifying_authority_user_id', auth()->id());
                  });
            })
            ->with(['appraisals' => function($q) use ($activeFY, $statuses) {
                $q->where('type', 'midterm')
                    ->where('financial_year', $activeFY)
                    ->whereIn('status', $statuses);
            }])
            ->whereHas('appraisals', function($q) use ($activeFY) {
                $q->where('type', 'midterm')
                  ->where('financial_year', $activeFY)
                  ->whereIn('status', [Appraisal::STATUS_READY_FOR_FINAL, Appraisal::STATUS_FINAL_COMPLETED]);
            })
            ->get();

        return view('appraisal.line_manager.final_list', compact('employees', 'activeFY'));
    }

    public function conductFinal($user_id)
    {
        $employee = User::findOrFail($user_id);
        $isAuthority = $employee->objectives()->where('certifying_authority_user_id', auth()->id())->exists();
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin() && $employee->line_manager_id !== auth()->id() && !$isAuthority) {
            abort(403);
        }
        $activeFY = FinancialYear::getActiveName();
        $activeModel = FinancialYear::getActive();
        $objectives = $employee->objectives()->where('financial_year', $activeFY)->get();
        
        $deptObjectives = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel?->id)
            ->where('department_id', $employee->department_id)
            ->where(function($q) use ($employee) {
                $q->whereNull('team_id')->orWhere('team_id', $employee->team_id);
            })
            ->with('master')
            ->get();
        
        $appraisal = Appraisal::where([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ])->first();

        $idps = Idp::query()
            ->where('user_id', $employee->id)
            ->where('financial_year', $activeFY)
            ->orderBy('id')
            ->get();

        return view('appraisal.line_manager.conduct_final', compact('employee', 'objectives', 'deptObjectives', 'appraisal', 'activeFY', 'idps'));
    }

    public function storeFinal(Request $request)
    {
        $appraisal = Appraisal::findOrFail($request->appraisal_id);

        if (($appraisal->status ?? null) === Appraisal::STATUS_FINAL_COMPLETED) {
            return redirect()->route('appraisal.final.list')->withErrors(['message' => 'Final assessment is already completed.']);
        }

        $request->validate([
            'scores' => 'nullable|array',
            'scores.*' => 'nullable|numeric|min:0|max:100',
            'manager_comment' => 'required|string',
        ]);

        $submittedScores = $request->input('scores', []);
        $totalWeightedScore = 0;
        
        $finalScores = [];

        $objectives = \App\Models\Objective::where('user_id', $appraisal->user_id)
            ->where('financial_year', \App\Models\FinancialYear::getActiveName())
            ->where('type', 'individual')
            ->get();

        foreach ($objectives as $objective) {
            $ta = $submittedScores[$objective->id] ?? 0;
            $finalScores[$objective->id] = $ta;
            $totalWeightedScore += ($objective->weightage * $ta / 100);
        }

        $deptObjectives = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', \App\Models\FinancialYear::getActive()?->id)
            ->where('department_id', $appraisal->user->department_id)
            ->where(function($q) use ($appraisal) {
                $q->whereNull('team_id')->orWhere('team_id', $appraisal->user->team_id);
            })
            ->get();

        $deptFinalScores = [];
        foreach ($deptObjectives as $deptObj) {
            $ta = $deptObj->final_score ?? 0;
            $deptFinalScores[$deptObj->id] = $ta;
            $totalWeightedScore += ($deptObj->weightage * $ta / 100);
        }

        // Determine Rating
        $rating = 'below';
        if ($totalWeightedScore >= 95) $rating = 'outstanding';
        elseif ($totalWeightedScore >= 85) $rating = 'good'; // "Very Good" in view, "good" in enum
        elseif ($totalWeightedScore >= 70) $rating = 'average'; // "Good" in view, "average" in enum

        $appraisal->update([
            'ratings' => array_merge($appraisal->ratings ?? [], ['scores' => $finalScores]),
            'total_score' => $totalWeightedScore,
            'rating' => $rating,
            'status' => Appraisal::STATUS_FINAL_COMPLETED,
            'action_points' => $request->input('manager_comment'),
            'signed_by_manager' => true,
            'manager_signed_at' => now()
        ]);

        // PIP Assignment Logic
        if ($totalWeightedScore < 60) {
            \App\Models\Pip::firstOrCreate(
                [
                    'user_id' => $appraisal->user_id,
                    'appraisal_id' => $appraisal->id,
                ],
                [
                    'status' => 'pending',
                    'reason' => 'Final performance score below 60%',
                    'created_by' => auth()->id(),
                ]
            );
        }

        return redirect()->route('appraisal.final.list')->with('success', 'Final assessment submitted successfully.');
    }

    // --- HR Gating ---

    public function triggerFinal($user_id)
    {
        $activeFY = FinancialYear::getActiveName();
        if (!$activeFY) {
            return back()->withErrors(['message' => 'No active financial year found.']);
        }

        $appraisal = Appraisal::where('user_id', $user_id)
            ->where('type', 'midterm')
            ->where('financial_year', $activeFY)
            ->where('status', Appraisal::STATUS_MIDTERM_COMPLETED)
            ->firstOrFail();

        $appraisal->update(['status' => Appraisal::STATUS_READY_FOR_FINAL]);

        return back()->with('success', 'Employee cleared for final evaluation.');
    }

    public function triggerAllFinals()
    {
        $activeFY = FinancialYear::getActiveName();
        if (!$activeFY) {
            return back()->withErrors(['message' => 'No active financial year found.']);
        }

        $count = Appraisal::where('type', 'midterm')
            ->where('financial_year', $activeFY)
            ->where('status', Appraisal::STATUS_MIDTERM_COMPLETED)
            ->update(['status' => Appraisal::STATUS_READY_FOR_FINAL]);

        if ($count > 0) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_final_triggered',
                'details' => "HR triggered final evaluations for {$count} employees (FY {$activeFY})",
            ]);
        }

        return back()->with('success', "Final evaluations triggered for {$count} employees.");
    }

    public function triggerAllMidterms()
    {
        $activeFY = FinancialYear::getActiveName();
        if (!$activeFY) {
            return back()->withErrors(['message' => 'No active financial year found.']);
        }

        $employees = User::where('is_active', true)
                         ->where('role', 'employee')
                         ->get();
        $count = 0;

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
                    'conducted_by' => auth()->id()
                ]);

                // SYNC DEPARTMENTAL OBJECTIVES
                // $this->syncDepartmentalObjectives($emp); // Obsolete: Dept objectives read dynamically

                $count++;
            }
        }

        if ($count > 0) {
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'bulk_midterm_triggered',
                'details' => "HR triggered midterm reviews for {$count} employees (FY {$activeFY})",
            ]);
            return back()->with('success', "Midterm reviews successfully triggered for {$count} employees.");
        }

        return back()->with('info', "No new employees were eligible for trigger.");
    }
    public function create()
    {
        $users = User::all();
        $years = FinancialYear::orderBy('start_date')->pluck('label')->toArray();
        return view('appraisal.super_admin.appraisals_create', compact('users', 'years'));
    }

    /**
     * Show the year-end assessment summary for an employee.
     */
    public function yearendAssessment($user_id)
    {
        $employee = User::findOrFail($user_id);
        // Ensure we always pass a string to downstream services; cast to string so
        // computeUserScores won't receive null during tests when no active FY exists.
        $activeFY = (string) FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterYearEnd(now())) {
                return back()->withErrors(['message' => 'Year-end marking is available only after 12 months of the active financial year.']);
            }
        }

        $teamObjectives = Objective::where('department_id', $employee->department_id)
            ->where('type', 'departmental')
            ->where('financial_year', $activeFY)
            ->get();
        $individualObjectives = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $activeFY)
            ->get();
        return view('appraisal.yearend.assessment', compact('employee', 'teamObjectives', 'individualObjectives', 'activeFY'));
    }

    /**
     * Save the year-end assessment for an employee (objectives scores).
     */
    public function saveYearendAssessment(YearendAssessmentRequest $request, $user_id)
    {
        if (empty(FinancialYear::getActiveName())) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()])->withInput();
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterYearEnd(now())) {
                return back()->withErrors(['message' => 'Year-end marking is available only after 12 months of the active financial year.'])->withInput();
            }
        }

        foreach ($request->input('teamObjectives', []) as $row) {
            $obj = Objective::find($row['id']);
            if ($obj) {
                // Ensure the current user is authorized to enter/modify the achieved value
                $this->authorize('enterAchieved', $obj);

                $obj->target_achieved = $row['target_achieved'];
                $obj->final_score = $row['final_score'];
                if (!is_null($row['target_achieved'])) {
                    $obj->target_achieved_entered_by = auth()->id();
                    $obj->target_achieved_entered_at = now();
                } else {
                    // Clear enterer metadata when a null is saved
                    $obj->target_achieved_entered_by = null;
                    $obj->target_achieved_entered_at = null;
                }
                $obj->save();
            }
        }
        foreach ($request->input('individualObjectives', []) as $row) {
            $obj = Objective::find($row['id']);
            if ($obj) {
                // Ensure the current user is authorized to enter/modify the achieved value
                $this->authorize('enterAchieved', $obj);

                $obj->target_achieved = $row['target_achieved'];
                $obj->final_score = $row['final_score'];
                if (!is_null($row['target_achieved'])) {
                    $obj->target_achieved_entered_by = auth()->id();
                    $obj->target_achieved_entered_at = now();
                } else {
                    $obj->target_achieved_entered_by = null;
                    $obj->target_achieved_entered_at = null;
                }
                $obj->save();
            }
        }
        return redirect()->route('appraisal.yearend.assessment', $user_id)->with('success', 'Assessment saved.');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'date' => 'required|date',
            'achievement_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'comments' => 'nullable|string',
            'financial_year' => 'required|string',
        ]);
        // Normalize rating labels (front-end may send display labels) to DB-safe codes
        if (!empty($data['rating'])) {
            $data['rating'] = $this->normalizeRatingToDb($data['rating']);
        }
        $appraisal = Appraisal::create($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_created',
            'table_name' => 'appraisals',
            'record_id' => $appraisal->id,
            'details' => "Appraisal created for user_id {$appraisal->user_id} (ID {$appraisal->id})",
        ]);
        return redirect()->route('appraisals.index')->with('success', 'Appraisal created successfully.');
    }
    public function show(Appraisal $appraisal)
    {
        return view('appraisal.super_admin.appraisals_show', compact('appraisal'));
    }
    public function edit(Appraisal $appraisal)
    {
        $users = User::all();
        $years = FinancialYear::orderBy('start_date')->pluck('label')->toArray();
        return view('appraisal.super_admin.appraisals_edit', compact('appraisal', 'users', 'years'));
    }
    public function update(Request $request, Appraisal $appraisal)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'date' => 'required|date',
            'achievement_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'comments' => 'nullable|string',
            'financial_year' => 'required|string',
        ]);
        if (!empty($data['rating'])) {
            $data['rating'] = $this->normalizeRatingToDb($data['rating']);
        }
        $appraisal->update($data);
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_updated',
            'table_name' => 'appraisals',
            'record_id' => $appraisal->id,
            'details' => "Appraisal updated: ID {$appraisal->id}",
        ]);
        return redirect()->route('appraisals.show', $appraisal)->with('success', 'Appraisal updated successfully.');
    }
    public function destroy(Appraisal $appraisal)
    {
        $appId = $appraisal->id;
        $appraisal->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_deleted',
            'table_name' => 'appraisals',
            'record_id' => $appId,
            'details' => "Appraisal deleted: ID {$appId}",
        ]);
        return redirect()->route('appraisals.index')->with('success', 'Appraisal deleted.');
    }

    // Legacy and user-specific methods
    public function adminIndex()
    {
        $appraisals = Appraisal::with('user')->orderByDesc('id')->get();
        return view('appraisal.super_admin.appraisals_index', compact('appraisals'));
    }
    public function midtermIndex()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }
        $objectives = $user->objectives()->where('financial_year', $activeFY)->get();
        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $user->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ], [
            'date' => now(),
            'status' => 'in_progress',
            'conducted_by' => $user->id,
        ]);

        return view('appraisal.midterm.index', compact('objectives', 'activeFY', 'appraisal'));
    }

    public function midtermSubmit(Request $request)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string'
        ]);

        // Simplified: compute achievement_score
        $total = 0;
        $count = 0;
        foreach ($request->input('achievements') as $a) {
            $total += floatval($a['score']);
            $count++;
        }

        $avg = $count ? $total / $count : 0;
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()])->withInput();
        }

        // Enforce midterm submissions during the midterm window only.
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterMidterm(now())) {
                return back()->withErrors(['message' => 'Midterm comments can be submitted only after 6 months of the active financial year.']);
            }
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm submissions are closed after the 9th month of the financial year.']);
            }
        }

        $appraisal = Appraisal::create([
            'user_id' => auth()->id(),
            'type' => 'midterm',
            'date' => now(),
            'achievement_score' => $avg,
            'comments' => $request->input('comments'),
            'financial_year' => $activeFY
        ]);

        // Persist any inline signatures from midterm form: sign_employee_mid, sign_manager_mid, sign_hr_mid
        try {
            $this->storeAppraisalSignatures($appraisal, [
                'sign_employee_mid' => 'employee',
                'sign_manager_mid' => 'manager',
                'sign_hr_mid' => 'hr',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed storing midterm inline signatures', ['error' => $e->getMessage(), 'user' => auth()->id()]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'midterm_submitted',
            'details' => "Midterm self-assessment submitted for FY {$activeFY}",
        ]);

        return redirect()->route('appraisals.midterm')->with('success', 'Midterm submitted.');
    }

    public function yearEndIndex()
    {
        /** @var User $user */
        $user = auth()->user();
        $activeModel = FinancialYear::getActive();
        $activeFY = $activeModel ? (new FinancialYearService($activeModel))->label() : FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }
        $deptObjectives = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel?->id)
            ->where('department_id', $user->department_id)
            ->where(function($q) use ($user) {
                $q->whereNull('team_id')->orWhere('team_id', $user->team_id);
            })
            ->with('master')
            ->get();

        $individualObjectives = $user->objectives()
            ->where('financial_year', $activeFY)
            ->where('type', 'individual')
            ->get();

        $appraisal = Appraisal::query()
            ->where('user_id', $user->id)
            ->where('type', 'midterm')
            ->where('financial_year', $activeFY)
            ->first();

        $idps = Idp::query()
            ->where('user_id', $user->id)
            ->where('financial_year', $activeFY)
            ->orderBy('id')
            ->get();

        return view('appraisal.yearend.index', compact('user', 'deptObjectives', 'individualObjectives', 'appraisal', 'idps', 'activeFY'));
    }

    public function yearEndSubmit(Request $request)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.id' => 'required|integer|exists:objectives,id',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'comments' => 'nullable|string',
            'supervisor_comments' => 'nullable|string'
        ]);

        $activeFY = FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }
        $activeFY = (string) $activeFY;

        // Persist per-objective achievements as sent in request
        foreach ($request->input('achievements', []) as $a) {
            $obj = Objective::find($a['id'] ?? null);
            if ($obj) {
                // Authorization: employees may enter their own achievements; otherwise use policy
                if (auth()->user()->role !== 'employee' || $obj->user_id !== auth()->id()) {
                    $this->authorize('enterAchieved', $obj);
                }

                $obj->target_achieved = floatval($a['score']);
                if (!is_null($a['score'])) {
                    $obj->target_achieved_entered_by = auth()->id();
                    $obj->target_achieved_entered_at = now();
                } else {
                    $obj->target_achieved_entered_by = null;
                    $obj->target_achieved_entered_at = null;
                }
                $obj->save();
            }
        }

        // Compute scores using PerformanceService
        $perf = (new PerformanceService())->computeUserScores(auth()->id(), $activeFY);

        $ratingCode = $this->normalizeRatingToDb($perf['status']);

        $appraisal = Appraisal::create([
            'user_id' => auth()->id(),
            'type' => 'year_end',
            'date' => now(),
            'achievement_score' => null,
            'total_score' => $perf['total_score'],
            'rating' => $ratingCode,
            'comments' => $request->input('comments'),
            'supervisor_comments' => $request->input('supervisor_comments'),
            'financial_year' => $activeFY,
            'conducted_by' => auth()->id(),
            'status' => 'completed',
        ]);

        // Persist any inline signatures from year-end form: sign_employee_year, sign_manager_year, sign_supervisor_year, sign_hr_manager_year
        try {
            $this->storeAppraisalSignatures($appraisal, [
                'sign_employee_year' => 'employee',
                'sign_manager_year' => 'manager',
                'sign_supervisor_year' => 'supervisor',
                'sign_hr_manager_year' => 'hr',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed storing yearend inline signatures', ['error' => $e->getMessage(), 'user' => auth()->id()]);
        }

        if ($ratingCode === 'below') {
            $pip = Pip::create([
                'user_id' => auth()->id(),
                'appraisal_id' => $appraisal->id,
                'status' => 'open',
                'reason' => 'Year-end total score below threshold',
                'created_by' => auth()->id(),
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'notes' => 'Auto-generated PIP due to low performance score',
            ]);

            try {
                // Prefer using Notifications / PipController helper which now uses Notification when user exists
                \App\Http\Controllers\PipController::notifyHrAboutPip($pip);
            } catch (\Exception $e) {
                // swallow
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'pip_created',
                'table_name' => 'pips',
                'record_id' => $pip->id,
                'details' => "Auto-created PIP for " . auth()->user()->name . " due to year-end score {$perf['total_score']}",
            ]);
        }

        $message = 'Year-end review conducted for ' . auth()->user()->name;
        if ($perf['status'] === 'below') {
            $message .= ' - PIP created due to low score.';
        }

        return redirect()->route('appraisals.yearend')->with('success', $message);
    }

    public function conductMidterm(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        $isAuthority = $employee->objectives()->where('certifying_authority_user_id', auth()->id())->exists();
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin() && $employee->line_manager_id !== auth()->id() && !$isAuthority) {
            abort(403);
        }
        $activeModel = FinancialYear::getActive();
        $activeFY = FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }

        $objectives = $employee->objectives()->where('financial_year', $activeFY)->get();

        $deptObjectives = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeModel?->id)
            ->where('department_id', $employee->department_id)
            ->where(function($q) use ($employee) {
                $q->whereNull('team_id')->orWhere('team_id', $employee->team_id);
            })
            ->with('master')
            ->get();

        // Find the triggered midterm Appraisal record
        $appraisal = Appraisal::where([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ])->first();

        if (!$appraisal) {
            if (auth()->user()->isHrAdmin() || auth()->user()->isSuperAdmin()) {
                // HR can create on the fly if needed
            } else {
                 return back()->withErrors(['message' => 'The Midterm review for this employee has not been initiated by HR yet.']);
            }
        }
        
        // Ensure its status is in_progress when manager starts
        if ($appraisal && $appraisal->status === Appraisal::STATUS_MIDTERM_TRIGGERED) {
            $appraisal->update(['status' => Appraisal::STATUS_IN_PROGRESS]);
        }

        // Handle case where it doesn't exist but user is HR (creating for convenience)
        if (!$appraisal && (auth()->user()->isHrAdmin() || auth()->user()->isSuperAdmin())) {
            $appraisal = Appraisal::create([
                'user_id' => $employee->id,
                'type' => 'midterm',
                'financial_year' => $activeFY,
                'date' => now(),
                'status' => Appraisal::STATUS_IN_PROGRESS,
                'conducted_by' => auth()->id(),
            ]);
        }

        // Ensure current user is authorized to view/conduct this appraisal
        // $this->authorize('view', $appraisal);

        return view('appraisal.line_manager.conduct_midterm', compact('employee', 'objectives', 'deptObjectives', 'appraisal', 'activeFY'));
    }

    public function conductMidtermSubmit(Request $request, $user_id)
    {
        $request->validate([
            'reviews' => 'required|array|min:1',
            'reviews.*.id' => 'required|integer|exists:objectives,id',
            'reviews.*.score' => 'required|numeric|min:0|max:100',
            'reviews.*.comment' => 'nullable|string',
        ]);
        $employee = User::findOrFail($user_id);
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin() && $employee->line_manager_id !== auth()->id()) {
            abort(403);
        }
        if (empty(FinancialYear::getActiveName())) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()])->withInput();
        }
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterMidterm(now())) {
                return back()->withErrors(['message' => 'Midterm comments can be entered only after 6 months of the active financial year.'])->withInput();
            }
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm revisions are locked after the 9th month of the financial year.']);
            }
        }
        $total = 0;
        $count = 0;
        foreach ($request->input('reviews') as $r) {
            $total += floatval($r['score']);
            $count++;
        }
        $avg = $count ? $total / $count : 0;
        $activeFY = FinancialYear::getActiveName();
        // Upsert the midterm appraisal so it's consistent and can be used for signing/PDF
        $appraisal = Appraisal::firstOrCreate([
            'user_id' => $employee->id,
            'type' => 'midterm',
            'financial_year' => $activeFY,
        ], [
            'date' => now(),
            'conducted_by' => auth()->id(),
        ]);

        $ratings = [];
        foreach ($request->input('reviews') as $idx => $rev) {
            // map objective id to a rating key structure
            // if front-end sends reviews by index, ensure objective id is sent
            $objId = $rev['id'] ?? $idx;
            $obj = Objective::find($objId);
            if (!$obj || $obj->user_id !== $employee->id) {
                return back()->withErrors(['reviews' => 'One or more objectives do not belong to this employee.']);
            }
            $ratings[$objId] = [
                'score' => $rev['score'],
                'comment' => $rev['comment'] ?? null,
            ];
        }

        $appraisal->update([
            'achievement_score' => $avg,
            'comments' => json_encode($request->input('reviews')),
            'ratings' => $ratings,
            'financial_year' => $activeFY,
            'conducted_by' => auth()->id(),
            'status' => Appraisal::STATUS_MIDTERM_COMPLETED,
        ]);
        return redirect()->route('objectives.team')->with('success', 'Midterm conducted for ' . $employee->name);
    }

    /**
     * Apply midterm revisions (manager-driven add/update/delete) transactionally.
     */
    public function conductMidtermRevision(MidtermRevisionRequest $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        if (empty(FinancialYear::getActiveName())) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()])->withInput();
        }
        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterMidterm(now())) {
                return back()->withErrors(['message' => 'Midterm comments can be entered only after 6 months of the active financial year.'])->withInput();
            }
            if (!$fyService->isBeforeNinthMonth(now())) {
                return back()->withErrors(['message' => 'Midterm revisions are locked after the 9th month of the financial year.']);
            }
        }

        $revisions = $request->input('revisions');

        // Treat the revisions payload as a replacement set: build new objectives list,
        // then replace the existing objectives for the employee & active FY atomically.
        DB::beginTransaction();
        try {
            $activeFY = FinancialYear::getActiveName();
            if (empty($activeFY)) {
                throw new \Exception($this->missingActiveFinancialYearMessage());
            }
            $newObjects = [];
            foreach ($revisions as $rev) {
                $action = $rev['action'] ?? 'add';
                if ($action === 'delete') {
                    // skip deleted items in the new set
                    continue;
                }

                // Prefer legacy 'title' field if present (tests expect title to become description),
                // otherwise fall back to 'description'.
                $desc = $rev['title'] ?? $rev['description'] ?? null;
                $weight = isset($rev['weightage']) ? intval($rev['weightage']) : 0;
                $type = $rev['type'] ?? 'individual';

                $newObjects[] = [
                    'user_id' => $employee->id,
                    'department_id' => $rev['department_id'] ?? $employee->department_id,
                    'type' => $type,
                    'description' => $desc,
                    'weightage' => $weight,
                    'target' => $rev['target'] ?? null,
                    'status' => 'set',
                    'financial_year' => $activeFY,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Replace existing objectives for this employee & FY (force delete to avoid soft-delete edge cases)
            Objective::where('user_id', $employee->id)->where('financial_year', $activeFY)->forceDelete();
            if (!empty($newObjects)) {
                Objective::insert($newObjects);
            }

            // validate totals
            $objectives = Objective::where('user_id', $employee->id)->where('financial_year', $activeFY)->get();
            $totalWeight = $objectives->sum('weightage');
            $teamWeight = $objectives->where('type', 'departmental')->sum('weightage');
            $teamCount = $objectives->where('type', 'departmental')->count();

            if ($totalWeight !== 100) {
                throw new \Exception("Invalid total weightage after revisions: {$totalWeight} (expected 100)");
            }
            if ($teamWeight > 30) {
                throw new \Exception("Invalid departmental weightage after revisions: {$teamWeight} (max 30)");
            }
            // Enforce departmental objectives count between 2 and 3
            $deptMin = config('appraisal.departmental_min_count', 2);
            $deptMax = config('appraisal.departmental_max_count', 3);
            if ($teamCount > 0 && ($teamCount < $deptMin || $teamCount > $deptMax)) {
                throw new \Exception("Invalid number of departmental objectives: {$teamCount} (expected between {$deptMin} and {$deptMax})");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['message' => 'Failed to apply midterm revisions: ' . $e->getMessage()]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'midterm_revisions_applied',
            'details' => 'Applied midterm revisions for ' . $employee->name,
        ]);

        return redirect()->route('objectives.team')->with('success', 'Midterm revisions applied for ' . $employee->name);
    }

    public function conductYearEnd(Request $request, $user_id)
    {
        $employee = User::findOrFail($user_id);
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin() && $employee->line_manager_id !== auth()->id()) {
            abort(403);
        }
        $activeFY = FinancialYear::getActiveName();
        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()]);
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterYearEnd(now())) {
                return back()->withErrors(['message' => 'Year-end marking is available only after 12 months of the active financial year.']);
            }
        }

        $objectives = $employee->objectives()->where('financial_year', $activeFY)->get();
        return view('appraisal.line_manager.conduct_yearend', compact('employee', 'objectives', 'activeFY'));
    }

    public function conductYearEndSubmit(Request $request, $user_id)
    {
        $request->validate([
            'achievements' => 'required|array|min:1',
            'achievements.*.id' => 'required|integer|exists:objectives,id',
            'achievements.*.score' => 'required|numeric|min:0|max:100',
            'achievements.*.rating' => 'required|numeric|min:0|max:100',
            'supervisor_comments' => 'nullable|string',
        ]);
        $employee = User::findOrFail($user_id);
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin() && $employee->line_manager_id !== auth()->id()) {
            abort(403);
        }
        $activeFY = FinancialYear::getActiveName();

        if (empty($activeFY)) {
            return back()->withErrors(['financial_year' => $this->missingActiveFinancialYearMessage()])->withInput();
        }

        $activeModel = FinancialYear::getActive();
        if ($activeModel) {
            $fyService = new FinancialYearService($activeModel);
            if (!$fyService->isOnOrAfterYearEnd(now())) {
                return back()->withErrors(['message' => 'Year-end marking is available only after 12 months of the active financial year.'])->withInput();
            }
        }

        $achievementRows = collect($request->input('achievements', []));
        $objectiveIds = $achievementRows->pluck('id')->filter()->values()->all();

        $objectivesById = Objective::whereIn('id', $objectiveIds)->get()->keyBy('id');
        $effectiveFY = (string) $activeFY;

        // Persist per-objective achievements as sent in request
        foreach ($request->input('achievements', []) as $a) {
            $obj = $objectivesById->get($a['id'] ?? null);
            if ($obj) {
                if ((int) $obj->user_id !== (int) $employee->id) {
                    return back()->withErrors(['achievements' => 'One or more objectives do not belong to this employee.']);
                }
                // Authorization: super admins and HR admins can always enter
                if (!auth()->user()->isSuperAdmin() && !auth()->user()->isHrAdmin()) {
                    // Line managers can enter for their direct reports
                    if (auth()->user()->isLineManager()) {
                        $obj->load('user'); // ensure user relationship is loaded
                        if (!$obj->user || $obj->user->line_manager_id !== auth()->id()) {
                            $this->authorize('enterAchieved', $obj);
                        }
                    } else {
                        // All other roles must pass policy check
                        $this->authorize('enterAchieved', $obj);
                    }
                }

                $obj->target_achieved = floatval($a['score']);
                if (!is_null($a['score'])) {
                    $obj->target_achieved_entered_by = auth()->id();
                    $obj->target_achieved_entered_at = now();
                } else {
                    $obj->target_achieved_entered_by = null;
                    $obj->target_achieved_entered_at = null;
                }
                $obj->save();
            }
        }

        // Compute scores using PerformanceService for the employee
        // Ensure a string is passed to computeUserScores. If the request didn't include
        // a financial_year, try to use the active financial year label. Cast to string
        // to avoid a TypeError when tests or callers omit the value.
        $scores = (new PerformanceService())->computeUserScores($employee->id, (string) $effectiveFY);

        $ratingCode = $this->normalizeRatingToDb($scores['status']);

        $appraisal = Appraisal::create([
            'user_id' => $employee->id,
            'type' => 'year_end',
            'date' => now(),
            'achievement_score' => null,
            'total_score' => $scores['total_score'],
            'rating' => $ratingCode,
            'comments' => json_encode($request->input('achievements')),
            'supervisor_comments' => $request->input('supervisor_comments'),
            'financial_year' => $effectiveFY,
            'conducted_by' => auth()->id(),
            'status' => 'completed',
        ]);
        if ($ratingCode === 'below') {
            $pip = Pip::create([
                'user_id' => $employee->id,
                'appraisal_id' => $appraisal->id,
                'status' => 'open',
                'reason' => 'Year-end total score below threshold',
                'created_by' => auth()->id(),
                'start_date' => now()->toDateString(),
                'end_date' => now()->addMonths(3)->toDateString(),
                'notes' => 'Auto-generated PIP due to low performance score',
            ]);

            try {
                \App\Http\Controllers\PipController::notifyHrAboutPip($pip);
            } catch (\Exception $e) {
                // swallow
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'pip_created',
                'table_name' => 'pips',
                'record_id' => $pip->id,
                'details' => "Auto-created PIP for {$employee->name} due to year-end score {$scores['total_score']}",
            ]);
        }

        $message = 'Year-end review conducted for ' . $employee->name;
        if ($scores['status'] === 'below') {
            $message .= ' - PIP created due to low score.';
        }

        return redirect()->route('objectives.team')->with('success', $message);
    }

    public function approve($appraisal_id)
    {
        $app = Appraisal::findOrFail($appraisal_id);
        // Authorization via policy
        $this->authorize('approve', $app);
        $app->update(['signed_by_manager' => true]);
        return redirect()->back()->with('success', 'Appraisal approved.');
    }

    public function override($appraisal_id)
    {
        $app = Appraisal::withTrashed()->findOrFail($appraisal_id);
        // HR override stub
        $app->update(['comments' => 'Overridden by HR']);
        // Simple HR override completed. More advanced override behavior can be implemented later.
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_overridden',
            'table_name' => 'appraisals',
            'record_id' => $appraisal_id,
            'details' => 'HR override performed',
        ]);

        return redirect()->route('appraisals.show', $app)->with('success', 'Appraisal overridden by HR.');
    }

    /**
     * Normalize a human-friendly rating label to the DB enum code.
     *
     * The application currently stores rating codes in the database using the
     * older enum set: ['outstanding','good','average','below'].
     * To avoid a schema migration in this change, map the new display labels
     * to the existing codes here. If you prefer to store the new labels
     * verbatim in the DB, create a migration to alter the enum and adjust
     * this method accordingly.
     *
     * @param string $label Human-friendly label (e.g. "Outstanding")
     * @return string DB-safe token (one of: outstanding, good, average, below)
     */
    private function normalizeRatingToDb(string $label)
    {
        return \App\Support\Rating::toDbToken($label);
    }

    private function missingActiveFinancialYearMessage(): string
    {
        return 'No active financial year found. Appraisal and objective setting are locked until Admin, HR Admin, or Board activates a financial year.';
    }

    /**
     * Generate PDF for midterm appraisal
     */
    public function generateMidtermPDF($appraisal_id)
    {
        $appraisal = Appraisal::with('user.department', 'user.lineManager')->findOrFail($appraisal_id);
        $this->authorize('view', $appraisal);

        $employee = $appraisal->user;
        $financialYear = $appraisal->financial_year;

        $objectives = Objective::where('user_id', $employee->id)
            ->where('financial_year', $financialYear)
            ->orderBy('id')
            ->get();

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'generate_midterm_pdf',
            'table_name' => 'appraisals',
            'record_id' => $appraisal_id,
            'details' => "Generated midterm appraisal PDF for {$employee->name} - FY: {$financialYear}",
        ]);

        $pdf = Pdf::loadView('appraisal.pdf.midterm_form', compact('employee', 'appraisal', 'objectives', 'financialYear'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = 'Midterm_Appraisal_' . str_replace(' ', '_', $employee->name) . "_{$financialYear}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Generate PDF for year-end appraisal
     */
    public function generateYearEndPDF($appraisal_id)
    {
        $appraisal = Appraisal::with('user.department', 'user.lineManager')->findOrFail($appraisal_id);
        // Authorization via policy
        $this->authorize('view', $appraisal);

        $employee = $appraisal->user;
        $financialYear = $appraisal->financial_year;

        $objectives = Objective::where('user_id', $employee->id)
            ->where('type', 'individual')
            ->where('financial_year', $financialYear)
            ->orderBy('id')
            ->get();

        // Log PDF generation
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'generate_yearend_pdf',
            'table_name' => 'appraisals',
            'record_id' => $appraisal_id,
            'details' => "Generated year-end appraisal PDF for {$employee->name} - FY: {$financialYear}",
        ]);

        // Attempt to locate any HR signature image stored for this appraisal and pass it to the PDF view.
        $hrSignaturePath = null;
        try {
            $hrDir = 'signatures/' . ($employee->id ?? $appraisal->user_id) . '/appraisal_' . ($appraisal->id ?? 'new') . '/hr';
            $files = Storage::disk('public')->files($hrDir);
            if (!empty($files)) {
                // pick the last file returned as the most recent signature for HR
                $hrSignaturePath = end($files);
            }
        } catch (\Throwable $e) {
            Log::warning('Error locating HR signature for PDF', ['error' => $e->getMessage(), 'appraisal_id' => $appraisal->id ?? $appraisal_id]);
        }

        $pdf = Pdf::loadView('appraisal.pdf.yearend_form', compact('employee', 'appraisal', 'objectives', 'financialYear', 'hrSignaturePath'));
        $pdf->setPaper('A4', 'portrait');

        $fileName = "YearEnd_Appraisal_{$employee->name}_{$financialYear}.pdf";
        $fileName = str_replace(' ', '_', $fileName);

        return $pdf->download($fileName);
    }

    /**
     * Save a signature for an appraisal (employee, manager, supervisor).
     * Expects: role (employee|manager|supervisor), name
     */
    public function saveSignature(Request $request, $appraisal_id)
    {
        $request->validate([
            'role' => 'required|string|in:employee,manager,supervisor',
            'name' => 'nullable|string|max:255',
            'image' => 'nullable|string', // base64 data URL
        ]);

        $appraisal = Appraisal::findOrFail($appraisal_id);

        $role = $request->input('role');

        // Authorization: use policy to validate that the current user may sign in this role
        $this->authorize('sign', [$appraisal, $role]);
        $name = $request->input('name');
        $imageData = $request->input('image');

        // Enforce signature order: supervisor may only sign after manager has signed
        if ($role === 'supervisor' && !$appraisal->signed_by_manager) {
            return back()->withErrors(['signature' => 'Manager must sign before supervisor can sign.']);
        }

        $storePath = null;
        if ($imageData) {
            // data:image/png;base64,....
            if (preg_match('/^data:\w+\/\w+;base64,/', $imageData)) {
                $data = substr($imageData, strpos($imageData, ',') + 1);
                $data = base64_decode($data);
                if ($data !== false) {
                    // enforce max size (200 KB) before storing; if larger, attempt to resize
                    $maxBytes = 200 * 1024; // 200 KB
                    $finalData = $data;
                    if (strlen($data) > $maxBytes) {
                        // attempt to resize via GD
                        if (function_exists('imagecreatefromstring')) {
                            $src = @imagecreatefromstring($data);
                            if ($src !== false) {
                                $w = imagesx($src);
                                $h = imagesy($src);
                                $scale = sqrt($maxBytes / strlen($data));
                                $nw = max(100, (int)($w * $scale));
                                $nh = max(40, (int)($h * $scale));
                                $dst = imagecreatetruecolor($nw, $nh);
                                // preserve transparency
                                imagealphablending($dst, false);
                                imagesavealpha($dst, true);
                                $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                                imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
                                imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                                ob_start();
                                imagepng($dst);
                                $finalData = ob_get_clean();
                                imagedestroy($dst);
                                imagedestroy($src);
                            }
                        }
                    }

                    $fileName = 'signatures/' . now()->format('Ymd') . '/' . uniqid() . '.png';
                    // Ensure directory exists and attempt to store. Log failures to help debug null-path entries.
                    try {
                        $dir = dirname($fileName);
                        if ($dir && $dir !== '.') {
                            Storage::disk('public')->makeDirectory($dir);
                        }
                        // final safeguard: cap at 500 KB
                        if (strlen($finalData) <= 500 * 1024) {
                            $ok = Storage::disk('public')->put($fileName, $finalData);
                            if ($ok) {
                                $storePath = $fileName;
                            } else {
                                Log::error('Failed to store signature image (put returned false)', ['file' => $fileName, 'length' => strlen($finalData)]);
                            }
                        } else {
                            Log::warning('Signature image exceeds max allowed size and was not stored', ['file' => $fileName, 'length' => strlen($finalData)]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Exception while storing signature image', ['file' => $fileName, 'exception' => $e->getMessage()]);
                    }
                }
            }
        }

        if ($role === 'employee') {
            $update = [
                'signed_by_employee' => true,
                'employee_signed_by_name' => $name,
                'employee_signed_at' => now(),
            ];
            if ($storePath) $update['employee_signature_path'] = $storePath;
            $appraisal->update($update);
        } elseif ($role === 'manager') {
            $update = [
                'signed_by_manager' => true,
                'manager_signed_by_name' => $name,
                'manager_signed_at' => now(),
            ];
            if ($storePath) $update['manager_signature_path'] = $storePath;
            $appraisal->update($update);
        } else {
            $update = [
                'signed_by_supervisor' => true,
                'supervisor_signed_by_name' => $name,
                'supervisor_signed_at' => now(),
            ];
            if ($storePath) $update['supervisor_signature_path'] = $storePath;
            $appraisal->update($update);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'appraisal_signed',
            'table_name' => 'appraisals',
            'record_id' => $appraisal->id,
            'details' => "{$role} signed appraisal #{$appraisal->id} by {$name}",
        ]);

        // If an image was provided but we didn't persist it, log a warning to surface the issue.
        if ($imageData && empty($storePath)) {
            Log::warning('Signature image payload received but no file was stored', [
                'appraisal_id' => $appraisal->id,
                'role' => $role,
                'user_id' => auth()->id(),
                'image_length' => is_string($imageData) ? strlen($imageData) : null,
            ]);
        }

        return redirect()->back()->with('success', 'Signature recorded.');
    }

    /**
     * Store inline signatures (base64) from a form and attach to the appraisal record when possible.
     * @param \App\Models\Appraisal $appraisal
     * @param array $mapping inputName => role ('employee'|'manager'|'supervisor'|'hr')
     * @return void
     */
    private function storeAppraisalSignatures($appraisal, array $mapping): void
    {
        foreach ($mapping as $inputName => $role) {
            $imageData = request()->input($inputName);
            if (empty($imageData) || !is_string($imageData) || strpos($imageData, 'base64,') === false) continue;
            $data = substr($imageData, strpos($imageData, ',') + 1);
            $bin = base64_decode($data);
            if ($bin === false) continue;

            // Attempt small resize if > 500 KB
            $max = 500 * 1024;
            if (strlen($bin) > $max && function_exists('imagecreatefromstring')) {
                $src = @imagecreatefromstring($bin);
                if ($src !== false) {
                    $w = imagesx($src);
                    $h = imagesy($src);
                    $scale = sqrt($max / strlen($bin));
                    $nw = max(100, (int)($w * $scale));
                    $nh = max(40, (int)($h * $scale));
                    $dst = imagecreatetruecolor($nw, $nh);
                    imagealphablending($dst, false);
                    imagesavealpha($dst, true);
                    $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                    imagefilledrectangle($dst, 0, 0, $nw, $nh, $transparent);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
                    ob_start();
                    imagepng($dst);
                    $bin = ob_get_clean();
                    imagedestroy($dst);
                    imagedestroy($src);
                }
            }

            $path = 'signatures/' . ($appraisal->user_id ?? auth()->id()) . '/appraisal_' . ($appraisal->id ?? 'new') . '/' . $role . '/' . now()->format('Ymd') . '/' . uniqid() . '.png';
            try {
                $dir = dirname($path);
                if ($dir && $dir !== '.') Storage::disk('public')->makeDirectory($dir);
                Storage::disk('public')->put($path, $bin);
                // attach to appraisal where appropriate
                if ($role === 'employee') {
                    $appraisal->update([
                        'employee_signature_path' => $path,
                        'signed_by_employee' => true,
                        'employee_signed_at' => now(),
                        'employee_signed_by_name' => auth()->user()->name,
                    ]);
                } elseif ($role === 'manager') {
                    $appraisal->update([
                        'manager_signature_path' => $path,
                        'signed_by_manager' => true,
                        'manager_signed_at' => now(),
                        'manager_signed_by_name' => auth()->user()->name,
                    ]);
                } elseif ($role === 'supervisor') {
                    $appraisal->update([
                        'supervisor_signature_path' => $path,
                        'signed_by_supervisor' => true,
                        'supervisor_signed_at' => now(),
                        'supervisor_signed_by_name' => auth()->user()->name,
                    ]);
                } else {
                    if ($role === 'hr') {
                        // Save HR signature path and metadata directly on the appraisal record
                        $appraisal->update([
                            'hr_signature_path' => $path,
                            'signed_by_hr' => true,
                            'hr_signed_at' => now(),
                            'hr_signed_by_name' => auth()->user()->name,
                        ]);

                        AuditLog::create([
                            'user_id' => auth()->id(),
                            'action' => 'hr_signature_saved',
                            'table_name' => 'appraisals',
                            'record_id' => $appraisal->id ?? null,
                            'details' => "Saved HR signature for appraisal (role={$role}) to {$path}",
                        ]);
                    } else {
                        // Other auxiliary signatures: record audit only
                        AuditLog::create([
                            'user_id' => auth()->id(),
                            'action' => 'signature_saved',
                            'table_name' => 'appraisals',
                            'record_id' => $appraisal->id ?? null,
                            'details' => "Saved auxiliary signature for role {$role} to {$path}",
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to store appraisal inline signature', ['role' => $role, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Basic HR reports dashboard.
     * Returns a lightweight view with a few aggregate metrics so the HR/HR-admin
     * route does not throw when requested. More detailed reports can be
     * implemented later and placed under a reports/ directory.
     */
    public function reports(Request $request)
    {
        $query = Appraisal::with('user');
        $q = $request->get('q');
        $fy = $request->get('fy');
        $type = $request->get('type');
        $status = $request->get('status');
        $rating = $request->get('rating');

        if (!empty($q)) {
            $query->whereHas('user', function ($uq) use ($q) {
                $uq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }
        if (!empty($fy)) {
            $query->where('financial_year', $fy);
        }
        if (!empty($type)) {
            $query->where('type', $type);
        }
        if (!empty($status)) {
            $query->where('status', $status);
        }
        if (!empty($rating)) {
            $query->where('rating', $rating);
        }

        $appraisals = $query->orderByDesc('id')->paginate(25)->appends($request->query());
        $years = FinancialYear::orderBy('start_date')->pluck('label')->toArray();
        $totalAppraisals = Appraisal::count();
        $avgScore = Appraisal::whereNotNull('total_score')->avg('total_score');

        return view('appraisal.hr_admin.reports_index', compact('appraisals', 'years', 'totalAppraisals', 'avgScore'));
    }

    // --- Departmental (Authority) Review Workflow ---

    public function departmentalObjectivesIndex()
    {
        $activeFY = FinancialYear::getActive();
        if (!$activeFY) return redirect()->route('dashboard')->with('error', 'No active financial year.');

        $assignments = \App\Models\DepartmentalObjectiveAssignment::where('financial_year_id', $activeFY->id)
            ->where('certifying_authority_user_id', auth()->id())
            ->with(['department', 'master'])
            ->get();

        return view('appraisal.authority.departmental_index', compact('assignments', 'activeFY'));
    }

    public function conductDeptMidterm($id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::with(['department', 'master'])->findOrFail($id);
        
        if ($asgn->certifying_authority_user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($asgn->midterm_status !== 'triggered' && $asgn->midterm_status !== 'completed') {
            return back()->with('error', 'Midterm review window is not open for this objective.');
        }

        $activeFY = FinancialYear::getActiveName();
        $employees = User::where('department_id', $asgn->department_id)
            ->where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('appraisal.authority.conduct_departmental_midterm', compact('asgn', 'employees', 'activeFY'));
    }

    public function storeDeptMidterm(Request $request, $id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::findOrFail($id);
        
        if ($asgn->certifying_authority_user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'notes' => 'required|string',
        ]);

        $asgn->update([
            'midterm_notes' => $request->notes,
            'midterm_status' => 'completed'
        ]);

        // PROPAGATE: Update the 'midterm_achieved' field for all matching departmental objectives
        Objective::where('department_id', $asgn->department_id)
            ->where('description', $asgn->master->title)
            ->where('financial_year', FinancialYear::getActiveName())
            ->where('is_departmental', true)
            ->update(['midterm_achieved' => $request->notes]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'dept_midterm_completed',
            'details' => "Authority completed departmental midterm review for Dept: {$asgn->department->name}, Objective: {$asgn->master->title}",
        ]);

        return redirect()->route('appraisal.dept.index')->with('success', 'Departmental midterm review saved and propagated to all employees.');
    }

    public function conductDeptFinal($id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::with(['department', 'master'])->findOrFail($id);
        
        if ($asgn->certifying_authority_user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        if ($asgn->final_status !== 'triggered' && $asgn->final_status !== 'completed') {
            return back()->with('error', 'Final evaluation window is not open for this objective.');
        }

        $activeFY = FinancialYear::getActiveName();
        $employees = User::where('department_id', $asgn->department_id)
            ->where('role', 'employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('appraisal.authority.conduct_departmental_final', compact('asgn', 'employees', 'activeFY'));
    }

    public function storeDeptFinal(Request $request, $id)
    {
        $asgn = \App\Models\DepartmentalObjectiveAssignment::findOrFail($id);
        
        if ($asgn->certifying_authority_user_id !== auth()->id() && !auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'rating' => 'required|string'
        ]);

        $asgn->update([
            'final_score' => $request->score,
            'final_rating' => $request->rating,
            'final_status' => 'completed'
        ]);

        // PROPAGATE: Update 'target_achieved' and 'final_score' for all matching departmental objectives
        Objective::where('department_id', $asgn->department_id)
            ->where('description', $asgn->master->title)
            ->where('financial_year', FinancialYear::getActiveName())
            ->where('is_departmental', true)
            ->update([
                'yearend_achieved' => $request->rating,
                'target_achieved' => $request->score,
                'final_score' => $request->score
            ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'dept_final_completed',
            'details' => "Authority completed departmental final evaluation for Dept: {$asgn->department->name}, Objective: {$asgn->master->title}",
        ]);

        return redirect()->route('appraisal.dept.index')->with('success', 'Departmental final evaluation saved and propagated.');
    }
}
