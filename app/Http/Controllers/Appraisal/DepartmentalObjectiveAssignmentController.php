<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DepartmentalObjectiveAssignment;
use App\Models\DepartmentalObjectiveMaster;
use App\Models\FinancialYear;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepartmentalObjectiveAssignmentController extends Controller
{
    public function index()
    {
        $this->authorizeAccess();

        $activeFy = FinancialYear::active();
        
        $departments = Department::with(['head', 'teams'])
            ->with(['assignments' => function($q) use ($activeFy) {
                if ($activeFy) {
                    $q->where('financial_year_id', $activeFy->id)->with('master');
                }
            }])
            ->orderBy('name')
            ->get();

        return view('appraisal.hr_admin.objective_assignments.index', [
            'departments' => $departments,
            'activeFy' => $activeFy,
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeAccess();

        $activeFy = FinancialYear::active();
        if (!$activeFy) {
            return redirect()->route('departmental-objective-assignments.index')
                ->with('error', 'Please activate a Financial Year first.');
        }

        $departments = Department::orderBy('name')->get();
        $masters = DepartmentalObjectiveMaster::where('is_active', true)->orderBy('title')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'role']);

        $selectedDeptId = $request->query('department_id');
        $teams = $selectedDeptId ? Team::where('department_id', $selectedDeptId)->get() : collect();
        $deptEmployees = $selectedDeptId ? User::where('department_id', $selectedDeptId)->orderBy('name')->get() : collect();

        return view('appraisal.hr_admin.objective_assignments.create', [
            'activeFy' => $activeFy,
            'departments' => $departments,
            'masters' => $masters,
            'users' => $users,
            'teams' => $teams,
            'deptEmployees' => $deptEmployees,
            'selectedDeptId' => $selectedDeptId,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $activeFy = FinancialYear::active();
        if (!$activeFy) return back()->with('error', 'No active FY.');

        $data = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'team_id' => 'nullable|exists:teams,id',
            'rows' => 'required|array|min:1|max:20', 
            'rows.*.objective_master_id' => 'required|exists:departmental_objective_masters,id',
            'rows.*.timeline' => 'nullable|string',
            'rows.*.weightage' => 'required|integer|min:0|max:30',
            'rows.*.certifying_authority_role' => 'required|string',
        ]);

        DB::transaction(function () use ($data, $activeFy) {
            foreach ($data['rows'] as $row) {
                DepartmentalObjectiveAssignment::create([
                    'financial_year_id' => $activeFy->id,
                    'department_id' => $data['department_id'],
                    'team_id' => $data['team_id'] ?? null,
                    'objective_master_id' => $row['objective_master_id'],
                    'timeline' => $row['timeline'] ?? '',
                    'weightage' => $row['weightage'],
                    'certifying_authority_role' => $row['certifying_authority_role'],
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('departmental-objective-assignments.index')->with('success', 'Objectives assigned successfully.');
    }

    public function edit($assignment)
    {
        $this->authorizeAccess();
        $activeFy = FinancialYear::active();
        if (!$activeFy) return redirect()->route('departmental-objective-assignments.index');

        $departmentId = $assignment; // Using the department ID passed via route
        $department = Department::findOrFail($departmentId);
        $assignments = DepartmentalObjectiveAssignment::where('department_id', $departmentId)
            ->where('financial_year_id', $activeFy->id)
            ->get();
            
        $departments = Department::orderBy('name')->get();
        $masters = DepartmentalObjectiveMaster::where('is_active', true)->orderBy('title')->get();
        $users = User::orderBy('name')->get(['id', 'name', 'role']);
        $teams = Team::where('department_id', $departmentId)->get();
        $deptEmployees = User::where('department_id', $departmentId)->orderBy('name')->get();

        return view('appraisal.hr_admin.objective_assignments.edit', [
            'department' => $department,
            'assignments' => $assignments,
            'activeFy' => $activeFy,
            'departments' => $departments,
            'masters' => $masters,
            'users' => $users,
            'teams' => $teams,
            'deptEmployees' => $deptEmployees,
        ]);
    }

    public function update(Request $request, $assignment)
    {
        $this->authorizeAccess();
        $activeFy = FinancialYear::active();
        $departmentId = $assignment;
        
        $data = $request->validate([
            'team_id' => 'nullable|exists:teams,id',
            'rows' => 'required|array|min:1',
            'rows.*.objective_master_id' => 'required|exists:departmental_objective_masters,id',
            'rows.*.timeline' => 'nullable|string',
            'rows.*.weightage' => 'required|integer|min:0|max:30',
            'rows.*.certifying_authority_role' => 'required|string',
        ]);

        DB::transaction(function () use ($departmentId, $activeFy, $data) {
            // Delete only assignments within this specific scope (either current team or dept-wide)
            DepartmentalObjectiveAssignment::where('department_id', $departmentId)
                ->where('financial_year_id', $activeFy->id)
                ->where('team_id', $data['team_id'] ?: null)
                ->delete();

            foreach ($data['rows'] as $row) {
                DepartmentalObjectiveAssignment::create([
                    'financial_year_id' => $activeFy->id,
                    'department_id' => $departmentId,
                    'team_id' => $data['team_id'] ?? null,
                    'objective_master_id' => $row['objective_master_id'],
                    'timeline' => $row['timeline'] ?? '',
                    'weightage' => $row['weightage'],
                    'certifying_authority_role' => $row['certifying_authority_role'],
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('departmental-objective-assignments.index')->with('success', 'Assignments updated.');
    }

    public function destroy($assignment)
    {
        $this->authorizeAccess();
        DepartmentalObjectiveAssignment::destroy($assignment);
        return back()->with('success', 'Row deleted.');
    }

    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->isHrAdmin() && !$user->isSuperAdmin())) {
            abort(403);
        }
    }
}
