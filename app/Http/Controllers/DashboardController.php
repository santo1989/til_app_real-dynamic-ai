<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Objective;
use App\Models\Appraisal;
use App\Models\FinancialYear;
use App\Models\Pip;
use App\Models\AuditLog;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $activeFinancialYear = FinancialYear::active();

        switch ($user->role ?? 'employee') {
            case 'super_admin':
                return view('appraisal.super_admin.dashboard', $this->buildSuperAdminDashboardData());

            case 'hr_admin':
                $stats = [
                    'total_users' => User::count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'inactive_users' => User::where('is_active', false)->count(),
                    'total_departments' => Department::count(),
                    'total_teams' => \App\Models\Team::count(),
                    'total_idps' => \App\Models\Idp::count(),
                    'open_pips' => Pip::where('status', 'open')->count(),
                ];
                
                $fyLabel = $activeFinancialYear?->label ?? 'None';

                $stats['appraisal_stats'] = [
                    'draft' => Appraisal::where('status', 'draft')->where('financial_year', $fyLabel)->count(),
                    'submitted' => Appraisal::where('status', 'submitted')->where('financial_year', $fyLabel)->count(),
                    'approved' => Appraisal::where('status', 'approved')->where('financial_year', $fyLabel)->count(),
                    'completed' => Appraisal::where('status', 'completed')->where('financial_year', $fyLabel)->count(),
                ];

                $departments = Department::with('head')->withCount(['users', 'teams'])->orderBy('name')->get();
                $recentLogs = AuditLog::with('user')->latest()->limit(5)->get();
                return view('appraisal.hr_admin.dashboard', compact('stats', 'departments', 'activeFinancialYear', 'recentLogs'));

            case 'line_manager':
                $teamSize = User::where('line_manager_id', $user->id)->count();
                $teamIdps = \App\Models\Idp::whereHas('user', function ($q) use ($user) {
                    $q->where('line_manager_id', $user->id);
                })->count();
                $pendingMidterms = Appraisal::where('type', 'midterm')
                    ->where('status', 'pending')
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('line_manager_id', $user->id);
                    })
                    ->count();
                $pendingYearend = Appraisal::where('type', 'yearend')
                    ->where('status', 'pending')
                    ->whereHas('user', function ($q) use ($user) {
                        $q->where('line_manager_id', $user->id);
                    })
                    ->count();
                $stats = [
                    'team_size' => $teamSize,
                    'pending_midterms' => $pendingMidterms,
                    'pending_yearend' => $pendingYearend,
                    'team_idps' => $teamIdps,
                ];
                
                return view('appraisal.line_manager.dashboard', compact('stats', 'activeFinancialYear'));

            case 'dept_head':
                $deptId = $user->department_id;
                $departmentObjectives = Objective::where('department_id', $deptId)->with('user')->get();
                return view('appraisal.dept_head.dashboard', compact('departmentObjectives', 'activeFinancialYear'));

            case 'board':
                $reports = Appraisal::latest()->limit(20)->get();
                return view('appraisal.board.dashboard', compact('reports', 'activeFinancialYear'));

            default:
                $myObjectivesCount = Objective::where('user_id', $user->id)->count();
                $midtermDue = Appraisal::where('user_id', $user->id)->where('type', 'midterm')->where('status', 'pending')->count();
                $yearendDue = Appraisal::where('user_id', $user->id)->where('type', 'yearend')->where('status', 'pending')->count();
                $myIdps = \App\Models\Idp::where('user_id', $user->id)->count();
                $stats = [
                    'my_objectives' => $myObjectivesCount,
                    'midterm_due' => $midtermDue,
                    'yearend_due' => $yearendDue,
                    'my_idps' => $myIdps,
                ];
                return view('appraisal.employee.dashboard', compact('stats', 'activeFinancialYear'));
        }
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->role ?? 'employee') === 'super_admin', 403);

        return response()->json($this->buildSuperAdminDashboardData());
    }

    private function buildSuperAdminDashboardData(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $totalDepartments = Department::count();
        $totalObjectives = Objective::count();
        $pendingObjectives = Objective::where('status', 'draft')->count();
        $approvedObjectives = Objective::where('status', 'set')->count();
        $totalAppraisals = Appraisal::count();
        $pendingAppraisals = Appraisal::where('status', 'pending')->count();
        $completedAppraisals = Appraisal::where('status', 'completed')->count();
        $totalIdps = \App\Models\Idp::count();
        $openPips = Pip::where('status', 'open')->count();
        $totalTeams = \App\Models\Team::count();
        $activeFinancialYear = FinancialYear::active();
        $fyLabel = $activeFinancialYear?->label ?? $activeFinancialYear?->name ?? 'None Set';

        $stats = [
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'inactive_users' => $inactiveUsers,
            'total_departments' => $totalDepartments,
            'total_teams' => $totalTeams,
            'total_objectives' => $totalObjectives,
            'pending_objectives' => $pendingObjectives,
            'approved_objectives' => $approvedObjectives,
            'total_appraisals' => $totalAppraisals,
            'pending_appraisals' => $pendingAppraisals,
            'completed_appraisals' => $completedAppraisals,
            'total_idps' => $totalIdps,
            'open_pips' => $openPips,
            'active_rate' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 1) : 0,
            'objective_approval_rate' => $totalObjectives > 0 ? round(($approvedObjectives / $totalObjectives) * 100, 1) : 0,
            'appraisal_completion_rate' => $totalAppraisals > 0 ? round(($completedAppraisals / $totalAppraisals) * 100, 1) : 0,
        ];

        $monthWindow = collect(range(5, 0))->map(function (int $offset) {
            $start = Carbon::now()->startOfMonth()->subMonthsNoOverflow($offset);

            return [
                'label' => $start->format('M y'),
                'start' => $start->copy()->startOfDay(),
                'end' => $start->copy()->endOfMonth()->endOfDay(),
            ];
        });

        $comparisonTrendLabels = [];
        $comparisonTrendObjectives = [];
        $comparisonTrendAppraisals = [];

        foreach ($monthWindow as $month) {
            $comparisonTrendLabels[] = $month['label'];
            $comparisonTrendObjectives[] = Objective::whereBetween('created_at', [$month['start'], $month['end']])->count();
            $comparisonTrendAppraisals[] = Appraisal::whereBetween('created_at', [$month['start'], $month['end']])->count();
        }

        $departments = Department::with('head')->withCount(['users', 'teams', 'assignments'])
            ->orderByDesc('users_count')
            ->limit(6)
            ->get();

        $departmentLabels = $departments->pluck('name')->values()->all();
        $departmentValues = $departments->pluck('users_count')->values()->all();

        $departmentDetails = $departments->map(function (Department $department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'code' => $department->code,
                'head' => $department->head->name ?? 'Unassigned',
                'head_title' => $department->head->role ?? 'N/A',
                'users_count' => $department->users_count ?? 0,
                'teams_count' => $department->teams_count ?? 0,
                'assignments_count' => $department->assignments_count ?? 0,
                'edit_url' => route('departments.edit', $department),
            ];
        })->values()->all();

        $statusLabels = ['Pending Objectives', 'Approved Objectives', 'Pending Appraisals', 'Completed Appraisals'];
        $statusValues = [$pendingObjectives, $approvedObjectives, $pendingAppraisals, $completedAppraisals];
        $statusColors = ['#f59e0b', '#10b981', '#6366f1', '#06b6d4'];

        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                    'status' => $user->is_active ? 'Active' : 'Inactive',
                    'status_class' => $user->is_active ? 'bg-success' : 'bg-secondary',
                    'created_at' => optional($user->created_at)->diffForHumans(),
                    'edit_url' => route('users.edit', $user),
                    'impersonate_url' => route('impersonate.start', $user),
                ];
            })
            ->values()
            ->all();

        $recentObjectives = Objective::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Objective $objective) {
                return [
                    'id' => $objective->id,
                    'title' => \Illuminate\Support\Str::limit((string) $objective->description, 72),
                    'user' => $objective->user->name ?? 'N/A',
                    'status' => ucfirst($objective->status ?? 'draft'),
                    'status_class' => match ($objective->status) {
                        'set' => 'bg-success',
                        'draft' => 'bg-warning text-dark',
                        default => 'bg-info',
                    },
                    'created_at' => optional($objective->created_at)->diffForHumans(),
                    'show_url' => route('objectives.show', $objective),
                ];
            })
            ->values()
            ->all();

        $recentAppraisals = Appraisal::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function (Appraisal $appraisal) {
                return [
                    'id' => $appraisal->id,
                    'title' => $appraisal->user->name ?? 'N/A',
                    'subtitle' => ucfirst($appraisal->type ?? ($appraisal->appraisal_type ?? 'Appraisal')),
                    'status' => ucfirst($appraisal->status ?? 'pending'),
                    'status_class' => $appraisal->status === 'completed' ? 'bg-success' : 'bg-info',
                    'created_at' => optional($appraisal->created_at)->diffForHumans(),
                    'show_url' => route('appraisals.show', $appraisal),
                ];
            })
            ->values()
            ->all();

        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(8)
            ->get()
            ->map(function (AuditLog $log) {
                $label = $log->action ?: 'Audit event';
                $metaBits = array_filter([
                    $log->user->name ?? 'System',
                    $log->table_name ? ucfirst(str_replace('_', ' ', $log->table_name)) : null,
                    $log->details,
                ]);

                return [
                    'id' => $log->id,
                    'label' => $label,
                    'meta' => implode(' · ', $metaBits),
                    'badge' => strtoupper(substr($label, 0, 1)),
                    'badge_class' => 'bg-primary',
                    'time' => optional($log->created_at)->diffForHumans(),
                    'timestamp' => optional($log->created_at)?->timestamp ?? now()->timestamp,
                    'url' => $log->table_name ? route('audit-logs.show', $log) : route('audit-logs.index'),
                ];
            })
            ->sortByDesc('timestamp')
            ->take(10)
            ->values()
            ->all();

        $quickModuleStats = [
            ['label' => 'Users', 'value' => $totalUsers, 'url' => route('users.index'), 'icon' => 'fa-users'],
            ['label' => 'Departments', 'value' => $totalDepartments, 'url' => route('departments.index'), 'icon' => 'fa-building'],
            ['label' => 'Objectives', 'value' => $totalObjectives, 'url' => route('objectives.index'), 'icon' => 'fa-bullseye'],
            ['label' => 'Appraisals', 'value' => $totalAppraisals, 'url' => route('appraisals.index'), 'icon' => 'fa-chart-line'],
            ['label' => 'IDPs', 'value' => $totalIdps, 'url' => route('idps.index'), 'icon' => 'fa-graduation-cap'],
            ['label' => 'PIPs', 'value' => $openPips, 'url' => route('pips.index'), 'icon' => 'fa-triangle-exclamation'],
            ['label' => 'Cycles', 'value' => FinancialYear::count(), 'url' => route('financial-years.index'), 'icon' => 'fa-calendar-days'],
            ['label' => 'Audit', 'value' => AuditLog::count(), 'url' => route('audit-logs.index'), 'icon' => 'fa-shield-halved'],
            ['label' => 'Reports', 'value' => Appraisal::count(), 'url' => route('reports.index'), 'icon' => 'fa-file-chart-column'],
        ];

        return [
            'stats' => $stats,
            'activeFinancialYearLabel' => $fyLabel,
            'activeFinancialYearActive' => (bool) ($activeFinancialYear?->is_active ?? false),
            'comparisonTrendLabels' => $comparisonTrendLabels,
            'comparisonTrendObjectives' => $comparisonTrendObjectives,
            'comparisonTrendAppraisals' => $comparisonTrendAppraisals,
            'departmentLabels' => $departmentLabels,
            'departmentValues' => $departmentValues,
            'departmentDetails' => $departmentDetails,
            'statusLabels' => $statusLabels,
            'statusValues' => $statusValues,
            'statusColors' => $statusColors,
            'recentUsers' => $recentUsers,
            'recentObjectives' => $recentObjectives,
            'recentAppraisals' => $recentAppraisals,
            'recentAuditLogs' => $recentAuditLogs,
            'quickModuleStats' => $quickModuleStats,
            'updatedAt' => now()->format('M d, Y h:i A'),
        ];
    }
}
