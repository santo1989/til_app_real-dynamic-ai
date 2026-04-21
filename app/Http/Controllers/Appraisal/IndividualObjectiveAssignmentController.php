<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\DepartmentalObjectiveAssignment;
use App\Models\FinancialYear;
use App\Models\Objective;
use App\Models\User;
use Illuminate\Http\Request;

class IndividualObjectiveAssignmentController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();

        $activeFy = FinancialYear::active();
        $users = User::with(['department'])
            ->withCount(['objectives as individual_count' => function($q) use ($activeFy) {
                if ($activeFy) $q->where('financial_year', $activeFy->label);
            }])
            ->withSum(['objectives as individual_weight' => function($q) use ($activeFy) {
                if ($activeFy) $q->where('financial_year', $activeFy->label);
            }], 'weightage')
            ->orderBy('name')
            ->paginate(15);

        return view('appraisal.hr_admin.individual_assignments.index', [
            'users' => $users,
            'activeFy' => $activeFy,
        ]);
    }

    public function show($id)
    {
        $this->authorizeAccess();

        $user = User::with(['department', 'team'])->findOrFail($id);
        $activeFy = FinancialYear::active();

        // 1. Inherited Departmental/Team Objectives (The 30%)
        $deptObjectives = DepartmentalObjectiveAssignment::where('financial_year_id', $activeFy?->id)
            ->where('department_id', $user->department_id)
            ->where(function($q) use ($user) {
                $q->whereNull('team_id')->orWhere('team_id', $user->team_id);
            })
            ->with('master')
            ->get();

        // 2. Individual Objectives (The 70%)
        $individualObjectives = Objective::where('user_id', $user->id)
            ->where('financial_year', $activeFy?->label)
            ->get();

        return view('appraisal.hr_admin.individual_assignments.show', [
            'user' => $user,
            'activeFy' => $activeFy,
            'deptObjectives' => $deptObjectives,
            'individualObjectives' => $individualObjectives,
        ]);
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isHrAdmin() && !$user->isSuperAdmin())) {
            abort(403);
        }
    }
}
