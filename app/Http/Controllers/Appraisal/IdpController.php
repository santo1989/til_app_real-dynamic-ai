<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Idp;
use App\Models\Objective;
use App\Models\AuditLog;
use App\Models\FinancialYear;
use App\Models\IdpDevelopmentObjective;
use App\Models\User;
use App\Models\Department;
use App\Models\DesignationMaster;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use App\Models\IndividualObjectiveMaster;
use Illuminate\Support\Str;

class IdpController extends Controller
{
    public function __construct()
    {
        // Wire the Idp policy to this controller so policy methods are applied
        // for resource actions and explicit checks.
        // Note: switched to explicit in-method authorization to allow
        // better debugging and to avoid early middleware 403 that prevented
        // our debug logs from running.
    }
    // Resource CRUD for super admin/HR admin
    public function index()
    {
        $user = auth()->user();

        // Use policy to decide whether the actor should see the admin index
        if (Gate::allows('viewAny', Idp::class)) {
            $idps = Idp::with('user')->orderByDesc('id')->get();
            return view('appraisal.super_admin.idps_index', compact('idps'));
        }

        // Line managers can view/create IDPs for their direct reports.
        if ($user && $user->role === 'line_manager') {
            $activeFY = FinancialYear::getActiveName();
            $teamUserIds = User::query()
                ->where('line_manager_id', $user->id)
                ->pluck('id');

            $selectedUserId = (int) request('user_id', 0);

            $query = Idp::with('user')
                ->whereIn('user_id', $teamUserIds)
                ->orderByDesc('id');

            if (Schema::hasColumn('idps', 'financial_year') && !empty($activeFY)) {
                $query->where('financial_year', $activeFY);
            }

            if ($selectedUserId > 0) {
                $query->where('user_id', $selectedUserId);
            }

            $idps = $query->get();
            $teamUsers = User::query()
                ->whereIn('id', $teamUserIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            return view('appraisal.super_admin.idps_index', compact('idps', 'teamUsers', 'selectedUserId', 'activeFY'));
        }

        // For regular users, show only their own IDPs
        $activeFY = FinancialYear::active();
        $fyLabel = $activeFY?->label;
        $profileUser = $user;
        
        $idpsQuery = Idp::where('user_id', $user->id)->orderByDesc('id');
        if (!empty($fyLabel)) {
            $idpsQuery->where('financial_year', $fyLabel);
        }
        $idps = $idpsQuery->get();

        $skillAreaOptions = $this->activeSkillAreaOptions();

        return view('appraisal.idp.index', compact('idps', 'activeFY', 'profileUser', 'skillAreaOptions'));
    }

    public function lineManagerList(Request $request)
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'line_manager') {
            abort(403);
        }

        $activeFY = FinancialYear::getActiveName();

        // Fetch team members who have at least one IDP in the active FY
        $teamUsers = User::query()
            ->with(['department', 'idps' => function($q) use ($activeFY) {
                if ($activeFY) $q->where('financial_year', $activeFY);
                $q->limit(1);
            }])
            ->where('line_manager_id', $user->id)
            ->whereHas('idps', function ($q) use ($activeFY) {
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            })
            ->withCount(['idps' => function ($q) use ($activeFY) {
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            }])
            ->withCount(['idps as approved_count' => function ($q) use ($activeFY) {
                $q->where('is_approved', true);
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            }])
            ->orderBy('name')
            ->get();

        return view('appraisal.line_manager.idp_list', compact('teamUsers', 'activeFY'));
    }

    public function lineManagerReview(Idp $idp)
    {
        $actor = auth()->user();
        if (!$actor || $actor->role !== 'line_manager') {
            abort(403);
        }

        $idp->load(['user.department', 'user.lineManager']);
        $employee = $idp->user;
        if (!$employee || (int) $employee->line_manager_id !== (int) $actor->id) {
            abort(403);
        }

        $activeFY = FinancialYear::getActiveName();
        $idps = Idp::query()
            ->where('user_id', $employee->id)
            ->where('financial_year', $activeFY)
            ->orderBy('id')
            ->get();

        $idpData = $idps->map(function($i) {
            return [
                'id' => $i->id,
                'skill_area' => $i->skill_area,
                'description' => $i->description,
                'expected_benefits' => $i->expected_benefits,
                'action_plan' => $i->action_plan,
                'resources_required' => $i->resources_required,
                'timeline' => $i->review_date ? \Carbon\Carbon::parse($i->review_date)->format('Y-m-d') : ($i->timeline ?? ''),
                'attainment' => is_null($i->attainment) ? '' : ($i->attainment ? '1' : '0'),
                'visible_demonstration' => $i->visible_demonstration,
                'hr_input' => $i->hr_input,
                'is_approved' => (bool)$i->is_approved
            ];
        });

        $skillAreaOptions = $this->activeSkillAreaOptions();

        return view('appraisal.line_manager.idp_review', compact('employee', 'idps', 'idpData', 'activeFY', 'skillAreaOptions'));
    }

    public function lineManagerUpdate(Request $request, Idp $idp)
    {
        $actor = auth()->user();
        if (!$actor || $actor->role !== 'line_manager') {
            abort(403);
        }

        $idp->load('user');
        $employee = $idp->user;
        if (!$employee || (int) $employee->line_manager_id !== (int) $actor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'idps' => 'required|array|min:1',
            'idps.*.id' => 'required|exists:idps,id',
            'idps.*.skill_area' => 'nullable|string|max:255',
            'idps.*.description' => 'required|string',
            'idps.*.expected_benefits' => 'nullable|string',
            'idps.*.action_plan' => 'nullable|string',
            'idps.*.resources_required' => 'nullable|string',
            'idps.*.review_date' => 'nullable|date_format:Y-m-d',
            'idps.*.attainment' => 'nullable|string',
            'idps.*.visible_demonstration' => 'nullable|string',
            'idps.*.is_approved' => 'nullable|boolean'
        ]);

        foreach ($validated['idps'] as $row) {
            $existing = Idp::findOrFail($row['id']);
            $isRowApproved = isset($row['is_approved']) ? (bool)$row['is_approved'] : false;
            
            // Cast attainment from string to nullable boolean
            $attainment = null;
            if (isset($row['attainment']) && $row['attainment'] !== '') {
                $attainment = (bool)$row['attainment'];
            }

            $updateData = [
                'skill_area' => $this->normalizeText($row['skill_area'] ?? null),
                'description' => $this->normalizeText($row['description'] ?? null),
                'expected_benefits' => $this->normalizeText($row['expected_benefits'] ?? null),
                'action_plan' => $this->normalizeText($row['action_plan'] ?? null),
                'resources_required' => $this->normalizeText($row['resources_required'] ?? null),
                'review_date' => (!empty($row['review_date'])) ? $row['review_date'] : null,
                'attainment' => $attainment,
                'visible_demonstration' => $this->normalizeText($row['visible_demonstration'] ?? null),
                'is_approved' => $isRowApproved,
            ];

            if ($isRowApproved) {
                $updateData['approved_by_id'] = $actor->id;
                $updateData['approved_at'] = now();
                $updateData['approved_by_role'] = 'line_manager';
                $updateData['signed_by_manager'] = true;
                $updateData['manager_signed_by_name'] = $actor->name;
                $updateData['manager_signed_at'] = now();
            } else {
                $updateData['approved_by_id'] = null;
                $updateData['approved_at'] = null;
                $updateData['approved_by_role'] = null;
                $updateData['signed_by_manager'] = false;
                $updateData['manager_signed_by_name'] = null;
                $updateData['manager_signed_at'] = null;
            }

            $existing->update($updateData);

            if (!empty($updateData['skill_area'])) {
                $this->upsertSkillAreaMaster($updateData['skill_area'], $actor->id);
            }
        }

        AuditLog::create([
            'user_id' => $actor->id,
            'action' => 'idp_team_reviewed',
            'table_name' => 'idps',
            'record_id' => $employee->id,
            'details' => "LM reviewed all IDPs for employee {$employee->name}",
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team IDPs updated successfully.',
                'redirect' => route('idp.team.list')
            ]);
        }

        return redirect()->route('idp.team.list')->with('success', 'Team IDPs updated successfully.');
    }

    public function hrList(Request $request)
    {
        $actor = $request->user();
        if (!$actor || !in_array(($actor->role ?? null), ['hr_admin', 'super_admin'], true)) {
            abort(403);
        }

        $activeFY = FinancialYear::getActiveName();

        // Fetch all users who have at least one IDP in the active FY
        $users = User::query()
            ->with(['department', 'lineManager', 'idps' => function($q) use ($activeFY) {
                if ($activeFY) $q->where('financial_year', $activeFY);
                $q->limit(1);
            }])
            ->whereHas('idps', function ($q) use ($activeFY) {
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            })
            ->withCount(['idps' => function ($q) use ($activeFY) {
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            }])
            ->withCount(['idps as approved_count' => function ($q) use ($activeFY) {
                $q->where('is_approved', true);
                if ($activeFY) {
                    $q->where('financial_year', $activeFY);
                }
            }])
            ->orderBy('name')
            ->get();

        return view('appraisal.hr_admin.idp_list', compact('users', 'activeFY'));
    }

    public function hrReview(Idp $idp)
    {
        $actor = auth()->user();
        if (!$actor || !in_array(($actor->role ?? null), ['hr_admin', 'super_admin'], true)) {
            abort(403);
        }

        $idp->load(['user.department', 'user.lineManager']);
        $employee = $idp->user;
        if (!$employee) {
            abort(404);
        }

        $activeFY = FinancialYear::getActiveName();
        $idps = Idp::query()
            ->where('user_id', $employee->id)
            ->where('financial_year', $activeFY)
            ->orderBy('id')
            ->get();

        // Data for Alpine.js table
        $idpData = $idps->map(function($i) {
            return [
                'id' => $i->id,
                'skill_area' => $i->skill_area,
                'description' => $i->description,
                'expected_benefits' => $i->expected_benefits,
                'action_plan' => $i->action_plan,
                'resources_required' => $i->resources_required,
                'timeline' => $i->review_date ? \Carbon\Carbon::parse($i->review_date)->format('Y-m-d') : ($i->timeline ?? ''),
                'attainment' => is_null($i->attainment) ? '' : ($i->attainment ? '1' : '0'),
                'visible_demonstration' => $i->visible_demonstration,
                'hr_input' => $i->hr_input,
                'is_approved' => (bool)$i->is_approved
            ];
        });

        $skillAreaOptions = $this->activeSkillAreaOptions();

        return view('appraisal.hr_admin.idp_review', compact('employee', 'idps', 'idpData', 'activeFY', 'skillAreaOptions'));
    }

    public function hrUpdate(Request $request, Idp $idp)
    {
        $actor = auth()->user();
        if (!$actor || !in_array(($actor->role ?? null), ['hr_admin', 'super_admin'], true)) {
            abort(403);
        }

        $employee = $idp->user;
        if (!$employee) {
            abort(404);
        }

        $validated = $request->validate([
            'idps' => 'required|array|min:1',
            'idps.*.id' => 'required|exists:idps,id',
            'idps.*.skill_area' => 'nullable|string|max:255',
            'idps.*.description' => 'required|string',
            'idps.*.expected_benefits' => 'nullable|string',
            'idps.*.action_plan' => 'nullable|string',
            'idps.*.resources_required' => 'nullable|string',
            'idps.*.review_date' => 'nullable|date_format:Y-m-d',
            'idps.*.attainment' => 'nullable|string',
            'idps.*.visible_demonstration' => 'nullable|string',
            'idps.*.hr_input' => 'nullable|string',
            'idps.*.is_approved' => 'nullable|boolean'
        ]);

        foreach ($validated['idps'] as $row) {
            $existing = Idp::findOrFail($row['id']);
            
            $attainment = null;
            if (isset($row['attainment']) && $row['attainment'] !== '') {
                $attainment = (bool)$row['attainment'];
            }

            $updateData = [
                'skill_area' => $this->normalizeText($row['skill_area'] ?? null),
                'description' => $this->normalizeText($row['description'] ?? null),
                'expected_benefits' => $this->normalizeText($row['expected_benefits'] ?? null),
                'action_plan' => $this->normalizeText($row['action_plan'] ?? null),
                'resources_required' => $this->normalizeText($row['resources_required'] ?? null),
                'review_date' => (!empty($row['review_date'])) ? $row['review_date'] : null,
                'attainment' => $attainment,
                'visible_demonstration' => $this->normalizeText($row['visible_demonstration'] ?? null),
                'hr_input' => $this->normalizeText($row['hr_input'] ?? null),
                'is_approved' => isset($row['is_approved']) ? (bool)$row['is_approved'] : $existing->is_approved,
            ];

            $existing->update($updateData);

            if (!empty($updateData['skill_area'])) {
                $this->upsertSkillAreaMaster($updateData['skill_area'], $actor->id);
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team IDPs updated by HR successfully.',
                'redirect' => route('idp.hr.list')
            ]);
        }

        return redirect()->route('idp.hr.list')->with('success', 'IDP updated successfully.');
    }

    public function create()
    {
        // Explicit authorization: ensure actor can create an IDP
        $this->authorize('create', Idp::class);

        $user = auth()->user();

        // HR/Super Admin and Line Manager use employee-selection create form.
        if (Gate::allows('viewAny', Idp::class) || ($user && $user->role === 'line_manager')) {
            if ($user && $user->role === 'line_manager') {
                $users = User::query()
                    ->where('line_manager_id', $user->id)
                    ->orderBy('name')
                    ->get();
            } else {
                $users = User::query()->orderBy('name')->get();
            }
            return view('appraisal.super_admin.idps_create', compact('users'));
        }

        return view('appraisal.idp.create');
    }

    public function store(Request $request)
    {
        // Explicit authorization: ensure actor can create an IDP
        $this->authorize('create', Idp::class);

        // Ensure revisions are allowed for the active financial year
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP revisions are closed for the active financial year.');
        }

        $user = auth()->user();
        $activeFY = FinancialYear::getActiveName();
        if (Schema::hasColumn('idps', 'financial_year') && empty($activeFY)) {
            return back()->withErrors(['financial_year' => 'No active financial year found. IDP setting is locked until Admin, HR Admin, or Board activates a financial year.'])->withInput();
        }

        $rules = [
            'user_id' => 'required|exists:users,id',
            'idps' => 'nullable|array|min:1',
            'idps.*.skill_area' => 'nullable|string|max:255',
            'idps.*.description' => 'nullable|string',
            'idps.*.expected_benefits' => 'nullable|string',
            'idps.*.action_plan' => 'nullable|string',
            'idps.*.resources_required' => 'nullable|string',
            'idps.*.review_date' => 'nullable|string',
            'description' => 'nullable|string',
            'review_date' => 'nullable|string',
            'progress_till_dec' => 'nullable|string',
            'revised_description' => 'nullable|string',
            'accomplishment' => 'nullable|string',
            'status' => 'nullable|string',
            'signed_by_employee' => 'nullable|boolean',
            'employee_signed_by_name' => 'nullable|string',
            'employee_signed_at' => 'nullable|date',
            'employee_signature_path' => 'nullable|string',
            'signed_by_manager' => 'nullable|boolean',
            'manager_signed_by_name' => 'nullable|string',
            'manager_signed_at' => 'nullable|date',
            'manager_signature_path' => 'nullable|string',
        ];
        $validated = $request->validate($rules);

        // Line managers may only create IDPs for their direct reports.
        if ($user && $user->role === 'line_manager') {
            $targetUserId = (int) ($validated['user_id'] ?? 0);
            $allowed = User::query()
                ->where('id', $targetUserId)
                ->where('line_manager_id', $user->id)
                ->exists();
            if (!$allowed) {
                abort(403, 'Line manager can only create IDPs for direct reports.');
            }
        }

        // Employee can only submit skill areas configured in the skill-area master table.
        $allowedSkillAreas = [];
        if ($user && $user->role === 'employee') {
            $allowedSkillAreas = $this->activeSkillAreaOptions()->toArray();
        }

        if (is_array($request->input('idps')) && count($request->input('idps')) > 0) {
            $created = 0;
            $seenDescriptions = [];
            foreach ($request->input('idps') as $row) {
                $description = $this->normalizeText($row['description'] ?? null);
                $skillArea = $this->normalizeText($row['skill_area'] ?? null);
                if ($description === null && $skillArea === null) {
                    continue;
                }

                if ($description === null || $skillArea === null) {
                    return back()->withErrors(['idps' => 'Both Skill Area and Development Objective are required for each IDP row.'])->withInput();
                }

                if ($user && $user->role === 'employee') {
                    if (!in_array($skillArea, $allowedSkillAreas, true)) {
                        return back()->withErrors(['idps' => 'Selected Skill Area is not allowed.'])->withInput();
                    }
                } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'], true)) {
                    $this->upsertSkillAreaMaster($skillArea, (int) $user->id);
                }

                if ($description !== null) {
                    $descKey = ((int) $validated['user_id']) . '|' . ((string) ($activeFY ?? '')) . '|' . $description;
                    if (isset($seenDescriptions[$descKey])) {
                        return back()->withErrors(['idps' => 'Duplicate IDP description found in submitted rows.'])->withInput();
                    }
                    $seenDescriptions[$descKey] = true;

                    if ($this->hasDuplicateDescription((int) $validated['user_id'], $description, $activeFY)) {
                        return back()->withErrors(['idps' => 'An IDP with the same description already exists for this user in the active financial year.'])->withInput();
                    }
                }

                $data = [
                    'user_id' => (int)$validated['user_id'],
                    'skill_area' => $skillArea,
                    'description' => $description,
                    'expected_benefits' => $this->normalizeText($row['expected_benefits'] ?? null),
                    'action_plan' => $this->normalizeText($row['action_plan'] ?? null),
                    'resources_required' => $this->normalizeText($row['resources_required'] ?? null),
                    'review_date' => $row['review_date'] ?? null,
                    'status' => 'open',
                ];
                if (Schema::hasColumn('idps', 'financial_year')) {
                    $data['financial_year'] = $activeFY;
                }

                if ($user->role === 'employee') {
                    if ((int)$data['user_id'] !== (int)$user->id) {
                        abort(403, 'Employees may only create their own IDPs.');
                    }
                    $data['is_approved'] = false;
                    $data['approved_by_id'] = null;
                    $data['approved_at'] = null;
                    $data['approved_by_role'] = null;
                } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'])) {
                    $data['is_approved'] = true;
                    $data['approved_by_id'] = $user->id;
                    $data['approved_at'] = now();
                    $data['approved_by_role'] = $user->role;
                }

                $idp = Idp::create($data);
                $created++;
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'idp_created',
                    'table_name' => 'idps',
                    'record_id' => $idp->id,
                    'details' => "IDP created for user_id {$idp->user_id} (ID {$idp->id})",
                ]);
            }

            if ($created === 0) {
                return back()->withErrors(['idps' => 'Please fill at least one IDP row before saving.'])->withInput();
            }

            return redirect()->route('idp.index')->with('success', 'IDP entries saved successfully.');
        }

        $data = $request->only([
            'user_id',
            'skill_area',
            'description',
            'expected_benefits',
            'action_plan',
            'resources_required',
            'review_date',
            'progress_till_dec',
            'revised_description',
            'accomplishment',
            'status',
            'signed_by_employee',
            'employee_signed_by_name',
            'employee_signed_at',
            'employee_signature_path',
            'signed_by_manager',
            'manager_signed_by_name',
            'manager_signed_at',
            'manager_signature_path'
        ]);
        $data = $this->normalizeIdpTextFields($data);

        if (empty($data['skill_area']) || empty($data['description'])) {
            return back()->withErrors(['idps' => 'Skill Area and Development Objective are required.'])->withInput();
        }

        if ($user && $user->role === 'employee') {
            if (!in_array($data['skill_area'], $allowedSkillAreas, true)) {
                return back()->withErrors(['skill_area' => 'Selected Skill Area is not allowed.'])->withInput();
            }
        } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'], true)) {
            $this->upsertSkillAreaMaster($data['skill_area'], (int) $user->id);
        }

        if (Schema::hasColumn('idps', 'financial_year')) {
            $data['financial_year'] = $activeFY;
        }

        if (!empty($data['description']) && $this->hasDuplicateDescription((int) $data['user_id'], $data['description'], $activeFY)) {
            return back()->withErrors(['description' => 'An IDP with this description already exists for this user in the active financial year.'])->withInput();
        }

        // Role-based default approval behavior
        if ($user->role === 'employee') {
            // employees may only create for themselves
            if ((int)$data['user_id'] !== $user->id) {
                abort(403, 'Employees may only create their own IDPs.');
            }
            $data['is_approved'] = false;
            $data['approved_by_id'] = null;
            $data['approved_at'] = null;
            $data['approved_by_role'] = null;
        } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'])) {
            // these roles can create and auto-approve
            $data['is_approved'] = true;
            $data['approved_by_id'] = $user->id;
            $data['approved_at'] = now();
            $data['approved_by_role'] = $user->role;
        }

        $idp = Idp::create($data);
        // Debug log: capture who created and whether they have admin-level IDP view permissions
        try {
            Log::info('idp.store.debug', [
                'auth_id' => auth()->id(),
                'gate_viewAny' => Gate::forUser(auth()->user())->allows('viewAny', Idp::class),
                'idp_id' => $idp->id,
            ]);
        } catch (\Throwable $e) {
            // Non-fatal: keep creating even if logging fails
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_created',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP created for user_id {$idp->user_id} (ID {$idp->id})",
        ]);
        // Redirect based on policy (admins see idps.*, regular users see idp.*)
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.index')->with('success', 'IDP created successfully');
        }
        return redirect()->route('idp.index')->with('success', 'IDP created successfully');
    }

    public function show(Idp $idp)
    {
        // Ensure the current user is authorized to view this IDP
        $this->authorize('view', $idp);
        if (Gate::allows('viewAny', Idp::class)) {
            return view('appraisal.super_admin.idps_show', compact('idp'));
        }
        return view('appraisal.idp.show', compact('idp'));
    }

    public function update(Request $request, Idp $idp)
    {
        // Ensure the current user is authorized to update this IDP
        $this->authorize('update', $idp);

        // Prevent update if already approved
        if ($idp->is_approved) {
            return redirect()->route('idp.index')->with('error', 'Approved IDPs cannot be updated.');
        }

        // Ensure revisions are allowed for the active financial year
        $fy = \App\Models\FinancialYear::active();
        if ($fy && !$fy->isRevisionAllowed()) {
            return redirect()->back()->with('error', 'IDP revisions are closed for the active financial year.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'skill_area' => 'nullable|string|max:255',
            'description' => 'required|string',
            'expected_benefits' => 'nullable|string',
            'action_plan' => 'nullable|string',
            'resources_required' => 'nullable|string',
            'review_date' => 'required|date',
            'progress_till_dec' => 'nullable|string',
            'revised_description' => 'nullable|string',
            'accomplishment' => 'nullable|string',
            'status' => 'nullable|string',
            'signed_by_employee' => 'nullable|boolean',
            'employee_signed_by_name' => 'nullable|string',
            'employee_signed_at' => 'nullable|date',
            'employee_signature_path' => 'nullable|string',
            'signed_by_manager' => 'nullable|boolean',
            'manager_signed_by_name' => 'nullable|string',
            'manager_signed_at' => 'nullable|date',
            'manager_signature_path' => 'nullable|string',
        ]);
        // Record revision history: capture fields that changed
        // Note: 'status' is not a column on the idps table in current schema — exclude it
        $fields = ['user_id', 'skill_area', 'description', 'expected_benefits', 'action_plan', 'resources_required', 'review_date', 'progress_till_dec', 'revised_description', 'accomplishment'];
        $original = $idp->only($fields);
        $new = $this->normalizeIdpTextFields($request->only($fields));

        if (!empty($new['description']) && $this->hasDuplicateDescription((int) ($new['user_id'] ?? $idp->user_id), $new['description'], $idp->financial_year ?? null, $idp->id)) {
            return back()->withErrors(['description' => 'An IDP with this description already exists for this user in the same financial year.'])->withInput();
        }

        $changes = [];
        foreach ($fields as $f) {
            $origVal = $original[$f] ?? null;
            $newVal = $new[$f] ?? null;
            if ((string)$origVal !== (string)$newVal) {
                $changes[$f] = ['old' => $origVal, 'new' => $newVal];
            }
        }
        if (!empty($changes)) {
            \App\Models\IdpRevision::create([
                'idp_id' => $idp->id,
                'changes' => $changes,
                'changed_by' => auth()->id(),
            ]);
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'idp_revision_created',
                'table_name' => 'idp_revisions',
                'record_id' => $idp->id,
                'details' => "IDP revision created for IDP {$idp->id}",
            ]);
        }

        // Merge signature fields into update data if present
        $updateData = array_merge($new, $this->normalizeIdpTextFields($request->only([
            'signed_by_employee',
            'employee_signed_by_name',
            'employee_signed_at',
            'employee_signature_path',
            'signed_by_manager',
            'manager_signed_by_name',
            'manager_signed_at',
            'manager_signature_path'
        ])));
        // Role-based approvals/clearing: if employee edits their own IDP, reset approval
        $user = auth()->user();
        if ($user->role === 'employee' && $idp->user_id === $user->id) {
            $updateData['is_approved'] = false;
            $updateData['approved_by_id'] = null;
            $updateData['approved_at'] = null;
            $updateData['approved_by_role'] = null;
        } elseif (in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'])) {
            // line_manager, hr_admin and super_admin edits are treated as approvals
            $updateData['is_approved'] = true;
            $updateData['approved_by_id'] = $user->id;
            $updateData['approved_at'] = now();
            $updateData['approved_by_role'] = $user->role;
        }

        $idp->update($updateData);

        if (
            in_array($user->role, ['line_manager', 'hr_admin', 'super_admin'], true)
            && !empty($updateData['skill_area'])
            && !empty($updateData['description'])
        ) {
            $this->upsertSkillAreaMaster($updateData['skill_area'], (int) $user->id);
        }
        // Debug log: capture who updated and whether they have admin-level IDP view permissions
        try {
            Log::info('idp.update.debug', [
                'auth_id' => auth()->id(),
                'gate_viewAny' => Gate::forUser(auth()->user())->allows('viewAny', Idp::class),
                'idp_id' => $idp->id,
            ]);
        } catch (\Throwable $e) {
        }
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_updated',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP updated for IDP {$idp->id}",
        ]);
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.show', $idp)->with('success', 'IDP updated successfully');
        }
        return redirect()->route('idp.index')->with('success', 'IDP updated successfully');
    }

    public function destroy(Idp $idp)
    {
        $this->authorize('delete', $idp);
        $idpId = $idp->id;
        $idp->delete();
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'idp_deleted',
            'table_name' => 'idps',
            'record_id' => $idpId,
            'details' => "IDP deleted: ID {$idpId}",
        ]);
        if (Gate::allows('viewAny', Idp::class)) {
            return redirect()->route('idps.index')->with('success', 'IDP deleted.');
        }
        return redirect()->route('idp.index')->with('success', 'IDP deleted.');
    }

    public function edit(Idp $idp)
    {
        $this->authorize('update', $idp);

        // Prevent edit if already approved
        if ($idp->is_approved) {
            return redirect()->route('idp.index')->with('error', 'Approved IDPs cannot be edited.');
        }
        if (Gate::allows('viewAny', Idp::class)) {
            $users = \App\Models\User::all();
            return view('appraisal.super_admin.idps_edit', compact('idp', 'users'));
        }
        $idp->load(['user.department', 'user.lineManager']);
        $activeFY = FinancialYear::getActiveName();
        $departments = Department::orderBy('name')->get(['id', 'name']);
        if (Schema::hasTable('designation_masters')) {
            $designationOptions = DesignationMaster::query()
                ->where('is_active', true)
                ->orderBy('title')
                ->pluck('title')
                ->values();
        } else {
            $designationOptions = User::query()
                ->whereNotNull('designation')
                ->where('designation', '!=', '')
                ->orderBy('designation')
                ->pluck('designation')
                ->unique()
                ->values();
        }
        $idpSkillAreaOptions = $this->activeSkillAreaOptions();
        $idpDescriptionOptions = $this->employeeIdpDescriptionOptions((int) $idp->user_id, $activeFY);

        if ($idpSkillAreaOptions->isEmpty()) {
            $idpSkillAreaOptions = $this->employeeIdpSkillAreaOptions((int) $idp->user_id, $activeFY);
        }
        return view('appraisal.idp.edit', compact('idp', 'idpDescriptionOptions', 'idpSkillAreaOptions', 'departments', 'designationOptions'));
    }

    /**
     * Approve an IDP. Can be called by line_manager (for their reports), hr_admin, or super_admin.
     */
    public function approve(Request $request, Idp $idp)
    {
        $this->authorize('approve', $idp);
        $user = auth()->user();
        $idp->is_approved = true;
        $idp->approved_by_id = $user->id;
        $idp->approved_at = now();
        $idp->approved_by_role = $user->role;
        $idp->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'idp_approved',
            'table_name' => 'idps',
            'record_id' => $idp->id,
            'details' => "IDP approved by {$user->id} ({$user->role}) for IDP {$idp->id}",
        ]);

        return redirect()->back()->with('success', 'IDP approved');
    }

    // Legacy methods for compatibility
    public function adminIndex()
    {
        $idps = Idp::with('user')->orderByDesc('id')->get();
        return view('appraisal.super_admin.idps_index', compact('idps'));
    }

    public function revise(Request $request, $user_id)
    {
        // manager revises employee IDP
        return redirect()->back()->with('success', 'IDP revised.');
    }

    private function normalizeText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return null;
        }
        return Str::upper($trimmed);
    }

    private function normalizeIdpTextFields(array $data): array
    {
        $textFields = [
            'skill_area',
            'description',
            'expected_benefits',
            'action_plan',
            'resources_required',
            'progress_till_dec',
            'revised_description',
            'accomplishment',
            'employee_signed_by_name',
            'manager_signed_by_name',
        ];

        foreach ($textFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->normalizeText($data[$field]);
            }
        }

        return $data;
    }

    private function hasDuplicateDescription(int $userId, string $normalizedDescription, ?string $financialYear = null, ?int $ignoreId = null): bool
    {
        $query = Idp::query()
            ->where('user_id', $userId)
            ->whereRaw('UPPER(description) = ?', [$normalizedDescription]);

        if (Schema::hasColumn('idps', 'financial_year') && !empty($financialYear)) {
            $query->where('financial_year', $financialYear);
        }

        if (!empty($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function employeeIdpDescriptionOptions(int $userId, ?string $financialYear = null)
    {
        $objectiveQuery = Objective::query()
            ->where('user_id', $userId)
            ->whereNotNull('description')
            ->where('description', '!=', '');

        if (!empty($financialYear) && Schema::hasColumn('objectives', 'financial_year')) {
            $objectiveQuery->where('financial_year', $financialYear);
        }

        $objectiveDescriptions = $objectiveQuery->pluck('description');

        $idpQuery = Idp::query()
            ->where('user_id', $userId)
            ->whereNotNull('description')
            ->where('description', '!=', '');

        if (!empty($financialYear) && Schema::hasColumn('idps', 'financial_year')) {
            $idpQuery->where('financial_year', $financialYear);
        }

        $idpDescriptions = $idpQuery->pluck('description');

        return $objectiveDescriptions
            ->merge($idpDescriptions)
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function employeeIdpSkillAreaOptions(int $userId, ?string $financialYear = null)
    {
        $query = Idp::query()
            ->where('user_id', $userId)
            ->whereNotNull('skill_area')
            ->where('skill_area', '!=', '');

        if (!empty($financialYear) && Schema::hasColumn('idps', 'financial_year')) {
            $query->where('financial_year', $financialYear);
        }

        return $query->pluck('skill_area')
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function activeSkillAreaOptions()
    {
        if (!Schema::hasTable('idp_development_objectives')) {
            return collect();
        }

        return IdpDevelopmentObjective::query()
            ->where('is_active', true)
            ->orderBy('skill_area')
            ->pluck('skill_area')
            ->filter()
            ->unique()
            ->values();
    }

    private function upsertSkillAreaMaster(string $skillArea, int $createdBy): void
    {
        if (!Schema::hasTable('idp_development_objectives')) {
            return;
        }

        IdpDevelopmentObjective::query()->updateOrCreate(
            [
                'skill_area' => $skillArea,
            ],
            [
                'is_active' => true,
                'created_by' => $createdBy,
            ]
        );
    }
}
