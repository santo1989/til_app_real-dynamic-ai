<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Department;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index()
    {
        $teams = Team::with(['department', 'teamLead'])->withCount('users')->get();
        return view('appraisal.hr_admin.teams_index', compact('teams'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        // Get potential team leads (managers/heads/admins)
        $potentialLeads = User::whereIn('role', ['line_manager', 'dept_head', 'hr_admin', 'super_admin'])
            ->orderBy('name')
            ->get();
        
        // We'll also need all users for the department-based selection (passed as JSON to views for Alpine)
        $allUsers = User::orderBy('name')->get(['id', 'name', 'department_id', 'employee_id']);

        return view('appraisal.hr_admin.team_create', compact('departments', 'potentialLeads', 'allUsers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'team_lead_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $team = Team::create([
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'team_lead_id' => $data['team_lead_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            if (!empty($data['member_ids'])) {
                User::whereIn('id', $data['member_ids'])->update(['team_id' => $team->id]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'team_created',
                'table_name' => 'teams',
                'record_id' => $team->id,
                'details' => "Team created: {$team->name} with " . count($data['member_ids'] ?? []) . " members.",
            ]);

            DB::commit();
            return redirect()->route('teams.index')->with('success', 'Team created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create team: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit(Team $team)
    {
        $departments = Department::orderBy('name')->get();
        $potentialLeads = User::whereIn('role', ['line_manager', 'dept_head', 'hr_admin', 'super_admin'])
            ->orderBy('name')
            ->get();
        
        $allUsers = User::orderBy('name')->get(['id', 'name', 'department_id', 'employee_id']);
        $currentMemberIds = User::where('team_id', $team->id)->pluck('id')->toArray();

        return view('appraisal.hr_admin.team_edit', compact('team', 'departments', 'potentialLeads', 'allUsers', 'currentMemberIds'));
    }

    public function update(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'team_lead_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $team->update([
                'name' => $data['name'],
                'department_id' => $data['department_id'],
                'team_lead_id' => $data['team_lead_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // Reset members: remove team_id from users who were in this team
            User::where('team_id', $team->id)->update(['team_id' => null]);

            // Assign new members
            if (!empty($data['member_ids'])) {
                User::whereIn('id', $data['member_ids'])->update(['team_id' => $team->id]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'team_updated',
                'table_name' => 'teams',
                'record_id' => $team->id,
                'details' => "Team updated: {$team->name}.",
            ]);

            DB::commit();
            return redirect()->route('teams.index')->with('success', 'Team updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update team: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Team $team)
    {
        $teamId = $team->id;
        $teamName = $team->name;

        DB::beginTransaction();
        try {
            // Detach users
            User::where('team_id', $team->id)->update(['team_id' => null]);
            
            $team->delete();

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'team_deleted',
                'table_name' => 'teams',
                'record_id' => $teamId,
                'details' => "Team deleted: {$teamName}.",
            ]);

            DB::commit();
            return redirect()->route('teams.index')->with('success', 'Team deleted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete team: ' . $e->getMessage()]);
        }
    }
}
