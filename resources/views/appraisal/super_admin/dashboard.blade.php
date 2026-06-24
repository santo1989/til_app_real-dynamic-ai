@extends('layouts.app')

@section('content')
    @php
        $stats = $stats ?? [];
        $fyLabel = $activeFinancialYearLabel ?? 'None Set';
        $isCycleOpen = $activeFinancialYearActive ?? false;
        $updatedAt = $updatedAt ?? now()->format('M d, Y h:i A');
        $summaryUrl = route('dashboard.summary');

        $dashboardData = [
            'stats' => $stats,
            'activeFinancialYearLabel' => $fyLabel,
            'activeFinancialYearActive' => $isCycleOpen,
            'comparisonTrendLabels' => $comparisonTrendLabels ?? [],
            'comparisonTrendObjectives' => $comparisonTrendObjectives ?? [],
            'comparisonTrendAppraisals' => $comparisonTrendAppraisals ?? [],
            'departmentLabels' => $departmentLabels ?? [],
            'departmentValues' => $departmentValues ?? [],
            'statusLabels' => $statusLabels ?? [],
            'statusValues' => $statusValues ?? [],
            'statusColors' => $statusColors ?? [],
            'recentUsers' => $recentUsers ?? [],
            'recentObjectives' => $recentObjectives ?? [],
            'recentAppraisals' => $recentAppraisals ?? [],
            'recentAuditLogs' => $recentAuditLogs ?? [],
            'quickModuleStats' => $quickModuleStats ?? [],
            'departmentDetails' => $departmentDetails ?? [],
            'updatedAt' => $updatedAt,
        ];
    @endphp

    <div class="super-admin-dashboard">
        <div class="dashboard-hero card border-0 shadow-lg overflow-hidden mb-4">
            <div class="card-body p-4 p-xl-5 position-relative">
                <div class="row align-items-center g-4 position-relative z-1">
                    <div class="col-xl-8">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <span class="badge rounded-pill px-3 py-2 hero-badge">
                                <i class="fas fa-user-shield me-1"></i> Super Admin
                            </span>
                            <span class="badge rounded-pill px-3 py-2 bg-light text-dark border">Real-time operations</span>
                            <span
                                class="badge rounded-pill px-3 py-2 bg-success-subtle text-success border border-success-subtle">
                                <span class="live-dot me-1" id="dashboard-live-dot"></span>
                                Live sync
                            </span>
                        </div>
                        <h1 class="display-6 fw-bold text-white mb-2">Command Center</h1>
                        <p class="hero-copy mb-3 mb-xl-0">
                            Monitor workforce health, appraisal progress, and departmental balance from a single control
                            room.
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a href="#performance-charts" class="btn btn-light btn-sm rounded-pill px-3">
                                <i class="fas fa-chart-line me-1"></i> Performance charts
                            </a>
                            <a href="#settings-panel" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                <i class="fas fa-sliders-h me-1"></i> Functional settings
                            </a>
                            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3"
                                onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print dashboard
                            </button>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="cycle-summary-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <div class="text-uppercase small text-muted fw-semibold">Active cycle</div>
                                    <div class="h4 fw-bold mb-0">{{ $fyLabel }}</div>
                                </div>
                                <span class="badge rounded-pill {{ $isCycleOpen ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $isCycleOpen ? 'Open' : 'Locked' }}
                                </span>
                            </div>
                            <div class="d-grid gap-2">
                                <div class="cycle-stat">
                                    <span>Updated</span>
                                    <strong id="dashboard-updated-at">{{ $updatedAt }}</strong>
                                </div>
                                <div class="cycle-stat">
                                    <span>Teams</span>
                                    <strong id="metric-total-teams">{{ number_format($stats['total_teams'] ?? 0) }}</strong>
                                </div>
                                <div class="cycle-stat">
                                    <span>IDPs</span>
                                    <strong id="metric-total-idps">{{ number_format($stats['total_idps'] ?? 0) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="hero-orb hero-orb-a"></div>
                <div class="hero-orb hero-orb-b"></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-2">
                <a href="{{ route('users.index') }}" class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-primary-soft text-primary"><i class="fas fa-users"></i></div>
                        <div class="metric-value" id="metric-total-users">{{ number_format($stats['total_users'] ?? 0) }}
                        </div>
                        <div class="metric-label">Users</div>
                        <div class="metric-subtext"><span id="metric-active-rate">{{ $stats['active_rate'] ?? 0 }}%</span>
                            active</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-2">
                <a href="{{ route('departments.index') }}"
                    class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-info-soft text-info"><i class="fas fa-building"></i></div>
                        <div class="metric-value" id="metric-total-departments">
                            {{ number_format($stats['total_departments'] ?? 0) }}</div>
                        <div class="metric-label">Departments</div>
                        <div class="metric-subtext"><span
                                id="metric-total-teams-mini">{{ number_format($stats['total_teams'] ?? 0) }}</span> teams
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-2">
                <a href="{{ route('objectives.index') }}"
                    class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-warning-soft text-warning"><i class="fas fa-bullseye"></i></div>
                        <div class="metric-value" id="metric-total-objectives">
                            {{ number_format($stats['total_objectives'] ?? 0) }}</div>
                        <div class="metric-label">Objectives</div>
                        <div class="metric-subtext"><span
                                id="metric-objective-approval-rate">{{ $stats['objective_approval_rate'] ?? 0 }}%</span>
                            approved</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-2">
                <a href="{{ route('appraisals.index') }}"
                    class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-success-soft text-success"><i class="fas fa-chart-column"></i></div>
                        <div class="metric-value" id="metric-total-appraisals">
                            {{ number_format($stats['total_appraisals'] ?? 0) }}</div>
                        <div class="metric-label">Appraisals</div>
                        <div class="metric-subtext"><span
                                id="metric-appraisal-completion-rate">{{ $stats['appraisal_completion_rate'] ?? 0 }}%</span>
                            completed</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-2">
                <a href="{{ route('idps.index') }}" class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-secondary-soft text-secondary"><i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="metric-value" id="metric-total-idps-card">
                            {{ number_format($stats['total_idps'] ?? 0) }}</div>
                        <div class="metric-label">IDPs</div>
                        <div class="metric-subtext">Development plans</div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-xl-2">
                <a href="{{ route('pips.index') }}" class="metric-card card h-100 text-decoration-none border-0 shadow-sm">
                    <div class="card-body">
                        <div class="metric-icon bg-danger-soft text-danger"><i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="metric-value" id="metric-open-pips">{{ number_format($stats['open_pips'] ?? 0) }}
                        </div>
                        <div class="metric-label">Open PIPs</div>
                        <div class="metric-subtext">Immediate attention</div>
                    </div>
                </a>
            </div>
        </div>

        <div id="performance-charts" class="row g-4 mb-4">
            <div class="col-xl-7">
                <div class="card border-0 shadow-sm h-100 chart-card">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Objectives vs Appraisals</h5>
                            <p class="text-muted small mb-0">Six-month comparison of created records</p>
                        </div>
                        <span class="badge rounded-pill bg-light text-dark border">Trend</span>
                    </div>
                    <div class="card-body px-3 px-md-4 pb-4">
                        <div class="chart-wrap">
                            <canvas id="comparisonTrendChart" height="140"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-5">
                <div class="card border-0 shadow-sm h-100 chart-card mb-4 mb-xl-0">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Workload Mix</h5>
                            <p class="text-muted small mb-0">Status distribution across key records</p>
                        </div>
                        <span class="badge rounded-pill bg-light text-dark border">Live</span>
                    </div>
                    <div class="card-body px-3 px-md-4 pb-4">
                        <div class="chart-wrap chart-wrap-sm">
                            <canvas id="statusBreakdownChart"></canvas>
                        </div>
                        <div class="status-legend mt-3" id="status-legend"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm h-100 chart-card">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Department Headcount</h5>
                            <p class="text-muted small mb-0">Top departments ranked by employee count</p>
                        </div>
                        <a href="{{ route('departments.index') }}"
                            class="btn btn-sm btn-outline-success rounded-pill px-3">Manage</a>
                    </div>
                    <div class="card-body px-3 px-md-4 pb-4">
                        <div class="chart-wrap chart-wrap-lg">
                            <canvas id="departmentComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm h-100 settings-card" id="settings-panel">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Functional Settings</h5>
                            <p class="text-muted small mb-0">Quick access to all core modules</p>
                        </div>
                        <span class="badge rounded-pill bg-success text-white">Ready</span>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="settings-grid">
                            @foreach ($quickModuleStats ?? [] as $module)
                                <a href="{{ $module['url'] }}" class="setting-tile">
                                    <i class="fas {{ $module['icon'] }}"></i>
                                    <span>{{ $module['label'] }}</span>
                                    <strong class="setting-count">{{ number_format($module['value'] ?? 0) }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm overflow-hidden">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1">Live Activity Stream</h5>
                            <p class="text-muted small mb-0">Recent changes from users, objectives, and appraisals</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="tab"
                                data-bs-target="#activity-users" type="button">Users</button>
                            <button class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="tab"
                                data-bs-target="#activity-objectives" type="button">Objectives</button>
                            <button class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="tab"
                                data-bs-target="#activity-appraisals" type="button">Appraisals</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content p-3 p-md-4">
                            <div class="tab-pane fade show active" id="activity-users">
                                <div class="activity-list" id="recent-users-list">
                                    @forelse($recentUsers as $item)
                                        <a href="{{ $item['edit_url'] }}" class="activity-item text-decoration-none">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $item['name'] }}</div>
                                                    <div class="text-muted small">{{ $item['email'] }} ·
                                                        {{ $item['role'] }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <span
                                                        class="badge {{ $item['status_class'] }}">{{ $item['status'] }}</span>
                                                    <div class="text-muted small mt-1">{{ $item['created_at'] }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center text-muted py-4">No recent users found.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="activity-objectives">
                                <div class="activity-list" id="recent-objectives-list">
                                    @forelse($recentObjectives as $item)
                                        <a href="{{ $item['show_url'] }}" class="activity-item text-decoration-none">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $item['title'] }}</div>
                                                    <div class="text-muted small">{{ $item['user'] }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <span
                                                        class="badge {{ $item['status_class'] }}">{{ $item['status'] }}</span>
                                                    <div class="text-muted small mt-1">{{ $item['created_at'] }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center text-muted py-4">No recent objectives found.</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="tab-pane fade" id="activity-appraisals">
                                <div class="activity-list" id="recent-appraisals-list">
                                    @forelse($recentAppraisals as $item)
                                        <a href="{{ $item['show_url'] }}" class="activity-item text-decoration-none">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $item['title'] }}</div>
                                                    <div class="text-muted small">{{ $item['subtitle'] }}</div>
                                                </div>
                                                <div class="text-end">
                                                    <span
                                                        class="badge {{ $item['status_class'] }}">{{ $item['status'] }}</span>
                                                    <div class="text-muted small mt-1">{{ $item['created_at'] }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="text-center text-muted py-4">No recent appraisals found.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                        <h5 class="fw-bold mb-1">Activity Feed</h5>
                        <p class="text-muted small mb-0">Unified feed from the latest system events</p>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="mini-feed" id="recent-activity-list">
                            @forelse($recentAuditLogs as $activity)
                                <a href="{{ $activity['url'] }}" class="mini-feed-item text-decoration-none">
                                    <div class="d-flex align-items-start gap-3">
                                        <span
                                            class="feed-badge {{ $activity['badge_class'] }}">{{ $activity['badge'] }}</span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold text-dark">{{ $activity['label'] }}</div>
                                            <div class="text-muted small">{{ $activity['meta'] }}</div>
                                            <div class="text-muted small mt-1">{{ $activity['time'] }}</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center text-muted py-4">No recent activity available.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div
                        class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Department Comparison</h5>
                            <p class="text-muted small mb-0">Employee load across the largest departments</p>
                        </div>
                        <a href="{{ route('audit-logs.index') }}"
                            class="btn btn-sm btn-outline-dark rounded-pill px-3">Audit logs</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Code</th>
                                        <th>Employees</th>
                                        <th>Manager</th>
                                        <th>Teams</th>
                                        <th>Assignments</th>
                                    </tr>
                                </thead>
                                <tbody id="department-table-body">
                                    @forelse(($departmentDetails ?? []) as $department)
                                        <tr>
                                            <td class="fw-semibold">{{ $department['name'] }}</td>
                                            <td><span class="badge bg-secondary">{{ $department['code'] ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ number_format($department['users_count'] ?? 0) }}</td>
                                            <td>
                                                <div class="fw-semibold small">{{ $department['head'] }}</div>
                                                <div class="text-muted small">{{ $department['head_title'] }}</div>
                                            </td>
                                            <td>{{ number_format($department['teams_count'] ?? 0) }}</td>
                                            <td>{{ number_format($department['assignments_count'] ?? 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No department data
                                                found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        :root {
            --theme-primary: #1a6b3b;
            --theme-secondary: #2d9a56;
            --theme-accent: #e9f5ee;
            --theme-surface: #f7fbf8;
            --theme-border: rgba(26, 107, 59, 0.12);
        }

        .super-admin-dashboard {
            --card-radius: 22px;
        }

        .dashboard-hero {
            border-radius: var(--card-radius);
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.22), transparent 26%),
                linear-gradient(135deg, var(--theme-primary) 0%, var(--theme-secondary) 100%);
            color: #fff;
        }

        .hero-copy {
            max-width: 720px;
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.02rem;
        }

        .hero-badge {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.22);
        }

        .cycle-summary-card {
            background: rgba(255, 255, 255, 0.96);
            color: #173224;
            border-radius: 18px;
            padding: 1.2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.12);
            backdrop-filter: blur(10px);
        }

        .cycle-stat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.8rem 1rem;
            border-radius: 14px;
            background: var(--theme-surface);
        }

        .live-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
            background: #4ade80;
            box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.6);
            animation: livePulse 1.8s infinite;
        }

        .hero-orb {
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(2px);
        }

        .hero-orb-a {
            width: 180px;
            height: 180px;
            background: rgba(255, 255, 255, 0.08);
            right: -40px;
            top: -60px;
        }

        .hero-orb-b {
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.06);
            left: 28%;
            bottom: -50px;
        }

        .metric-card {
            border-radius: 18px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            color: inherit;
            background: #fff;
        }

        .metric-card:hover,
        .setting-tile:hover,
        .activity-item:hover,
        .mini-feed-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 34px rgba(17, 24, 39, 0.12) !important;
        }

        .metric-card .card-body {
            padding: 1.2rem;
        }

        .metric-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 0.9rem;
        }

        .metric-value {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
            color: #0f172a;
        }

        .metric-label {
            font-size: 0.92rem;
            font-weight: 700;
            color: #334155;
            margin-top: 0.35rem;
        }

        .metric-subtext {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 0.2rem;
        }

        .chart-card,
        .settings-card {
            border-radius: var(--card-radius);
        }

        .chart-wrap {
            position: relative;
            min-height: 300px;
        }

        .chart-wrap-sm {
            min-height: 260px;
        }

        .chart-wrap-lg {
            min-height: 360px;
        }

        .status-legend {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.6rem;
        }

        .status-pill {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            background: var(--theme-surface);
            border-radius: 999px;
            padding: 0.65rem 0.85rem;
            font-size: 0.86rem;
            color: #334155;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            display: inline-block;
            flex: 0 0 auto;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .setting-tile {
            min-height: 94px;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fcf8 100%);
            border: 1px solid var(--theme-border);
            color: #173224;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            padding: 0.9rem;
        }

        .setting-tile i {
            font-size: 1.15rem;
            color: var(--theme-primary);
        }

        .setting-tile span {
            font-weight: 700;
            font-size: 0.85rem;
        }

        .setting-count {
            font-size: 0.9rem;
            color: var(--theme-primary);
        }

        .activity-list,
        .mini-feed {
            display: grid;
            gap: 0.75rem;
        }

        .activity-item,
        .mini-feed-item {
            display: block;
            border: 1px solid var(--theme-border);
            border-radius: 16px;
            background: #fff;
            padding: 1rem;
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .feed-badge {
            min-width: 74px;
            text-align: center;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            margin-top: 0.12rem;
        }

        .bg-primary-soft {
            background: rgba(26, 107, 59, 0.1);
        }

        .bg-info-soft {
            background: rgba(59, 130, 246, 0.1);
        }

        .bg-warning-soft {
            background: rgba(245, 158, 11, 0.1);
        }

        .bg-success-soft {
            background: rgba(16, 185, 129, 0.1);
        }

        .bg-secondary-soft {
            background: rgba(100, 116, 139, 0.1);
        }

        .bg-danger-soft {
            background: rgba(239, 68, 68, 0.1);
        }

        @keyframes livePulse {
            0% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.55);
            }

            70% {
                box-shadow: 0 0 0 12px rgba(74, 222, 128, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
            }
        }

        @media (max-width: 991.98px) {
            .settings-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 767.98px) {

            .dashboard-hero .card-body,
            .chart-card .card-body,
            .settings-card .card-body {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            .settings-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .status-legend {
                grid-template-columns: 1fr;
            }

            .chart-wrap,
            .chart-wrap-lg {
                min-height: 280px;
            }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const summaryUrl = @json($summaryUrl);
            const initialPayload = @json($dashboardData);
            const numberFormatter = new Intl.NumberFormat();
            const charts = {};

            function makeGradient(ctx, topColor, bottomColor) {
                const gradient = ctx.createLinearGradient(0, 0, 0, 320);
                gradient.addColorStop(0, topColor);
                gradient.addColorStop(1, bottomColor);
                return gradient;
            }

            function renderStatusLegend(payload) {
                const legend = document.getElementById('status-legend');
                if (!legend) {
                    return;
                }

                const labels = payload.statusLabels || [];
                const values = payload.statusValues || [];
                const colors = payload.statusColors || [];

                legend.innerHTML = labels.map(function(label, index) {
                    return `
                        <div class="status-pill">
                            <span class="d-flex align-items-center gap-2">
                                <span class="status-dot" style="background:${colors[index] || '#94a3b8'}"></span>
                                <span>${label}</span>
                            </span>
                            <strong>${numberFormatter.format(values[index] || 0)}</strong>
                        </div>
                    `;
                }).join('');
            }

            function renderRecentList(container, items, type) {
                if (!container) {
                    return;
                }

                if (!items || !items.length) {
                    container.innerHTML = '<div class="text-center text-muted py-4">No records found.</div>';
                    return;
                }

                if (type === 'users') {
                    container.innerHTML = items.map(function(item) {
                        return `
                            <a href="${item.edit_url}" class="activity-item text-decoration-none">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">${item.name}</div>
                                        <div class="text-muted small">${item.email} · ${item.role}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge ${item.status_class}">${item.status}</span>
                                        <div class="text-muted small mt-1">${item.created_at || ''}</div>
                                    </div>
                                </div>
                            </a>
                        `;
                    }).join('');
                }

                if (type === 'objectives') {
                    container.innerHTML = items.map(function(item) {
                        return `
                            <a href="${item.show_url}" class="activity-item text-decoration-none">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">${item.title}</div>
                                        <div class="text-muted small">${item.user}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge ${item.status_class}">${item.status}</span>
                                        <div class="text-muted small mt-1">${item.created_at || ''}</div>
                                    </div>
                                </div>
                            </a>
                        `;
                    }).join('');
                }

                if (type === 'appraisals') {
                    container.innerHTML = items.map(function(item) {
                        return `
                            <a href="${item.show_url}" class="activity-item text-decoration-none">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">${item.title}</div>
                                        <div class="text-muted small">${item.subtitle}</div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge ${item.status_class}">${item.status}</span>
                                        <div class="text-muted small mt-1">${item.created_at || ''}</div>
                                    </div>
                                </div>
                            </a>
                        `;
                    }).join('');
                }
            }

            function renderActivityFeed(items) {
                const container = document.getElementById('recent-activity-list');
                if (!container) {
                    return;
                }

                if (!items || !items.length) {
                    container.innerHTML =
                        '<div class="text-center text-muted py-4">No recent activity available.</div>';
                    return;
                }

                container.innerHTML = items.map(function(item) {
                    return `
                        <a href="${item.url}" class="mini-feed-item text-decoration-none">
                            <div class="d-flex align-items-start gap-3">
                                <span class="feed-badge ${item.badge_class}">${item.badge}</span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold text-dark">${item.label}</div>
                                    <div class="text-muted small">${item.meta}</div>
                                    <div class="text-muted small mt-1">${item.time}</div>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');
            }

            function renderDepartmentTable(details) {
                const body = document.getElementById('department-table-body');
                if (!body) {
                    return;
                }

                if (!details || !details.length) {
                    body.innerHTML =
                        '<tr><td colspan="6" class="text-center text-muted py-4">No department data found.</td></tr>';
                    return;
                }

                body.innerHTML = details.map(function(item) {
                    return `
                        <tr>
                            <td class="fw-semibold">${item.name}</td>
                            <td><span class="badge bg-secondary">${item.code || 'N/A'}</span></td>
                            <td>${numberFormatter.format(item.users_count || 0)}</td>
                            <td>
                                <div class="fw-semibold small">${item.head}</div>
                                <div class="text-muted small">${item.head_title}</div>
                            </td>
                            <td>${numberFormatter.format(item.teams_count || 0)}</td>
                            <td>${numberFormatter.format(item.assignments_count || 0)}</td>
                        </tr>
                    `;
                }).join('');
            }

            function renderQuickModules(items) {
                const container = document.querySelector('.settings-grid');
                if (!container || !items || !items.length) {
                    return;
                }

                container.innerHTML = items.map(function(item) {
                    return `
                        <a href="${item.url}" class="setting-tile">
                            <i class="fas ${item.icon}"></i>
                            <span>${item.label}</span>
                            <strong class="setting-count">${numberFormatter.format(item.value || 0)}</strong>
                        </a>
                    `;
                }).join('');
            }

            function updateMetrics(payload) {
                const stats = payload.stats || {};
                const mappings = {
                    'metric-total-users': stats.total_users,
                    'metric-active-rate': `${stats.active_rate ?? 0}%`,
                    'metric-total-departments': stats.total_departments,
                    'metric-total-teams-mini': stats.total_teams,
                    'metric-total-objectives': stats.total_objectives,
                    'metric-objective-approval-rate': `${stats.objective_approval_rate ?? 0}%`,
                    'metric-total-appraisals': stats.total_appraisals,
                    'metric-appraisal-completion-rate': `${stats.appraisal_completion_rate ?? 0}%`,
                    'metric-total-idps-card': stats.total_idps,
                    'metric-total-idps': stats.total_idps,
                    'metric-open-pips': stats.open_pips,
                };

                Object.entries(mappings).forEach(function([id, value]) {
                    const element = document.getElementById(id);
                    if (element !== null && value !== undefined && value !== null) {
                        element.textContent = typeof value === 'number' ? numberFormatter.format(value) :
                            value;
                    }
                });

                const updatedAt = document.getElementById('dashboard-updated-at');
                if (updatedAt && payload.updatedAt) {
                    updatedAt.textContent = payload.updatedAt;
                }
            }

            function applyPayload(payload) {
                if (!payload) {
                    return;
                }

                updateMetrics(payload);
                renderStatusLegend(payload);
                renderRecentList(document.getElementById('recent-users-list'), payload.recentUsers || [], 'users');
                renderRecentList(document.getElementById('recent-objectives-list'), payload.recentObjectives || [],
                    'objectives');
                renderRecentList(document.getElementById('recent-appraisals-list'), payload.recentAppraisals || [],
                    'appraisals');
                renderActivityFeed(payload.recentAuditLogs || []);
                renderDepartmentTable(payload.departmentDetails || []);
                renderQuickModules(payload.quickModuleStats || []);

                if (charts.trend) {
                    charts.trend.data.labels = payload.comparisonTrendLabels || [];
                    charts.trend.data.datasets[0].data = payload.comparisonTrendObjectives || [];
                    charts.trend.data.datasets[1].data = payload.comparisonTrendAppraisals || [];
                    charts.trend.update();
                }

                if (charts.status) {
                    charts.status.data.labels = payload.statusLabels || [];
                    charts.status.data.datasets[0].data = payload.statusValues || [];
                    charts.status.data.datasets[0].backgroundColor = payload.statusColors || [];
                    charts.status.update();
                }

                if (charts.department) {
                    charts.department.data.labels = payload.departmentLabels || [];
                    charts.department.data.datasets[0].data = payload.departmentValues || [];
                    charts.department.update();
                }
            }

            const trendCanvas = document.getElementById('comparisonTrendChart');
            if (trendCanvas) {
                const trendCtx = trendCanvas.getContext('2d');
                charts.trend = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: initialPayload.comparisonTrendLabels || [],
                        datasets: [{
                                label: 'Objectives',
                                data: initialPayload.comparisonTrendObjectives || [],
                                borderColor: '#1a6b3b',
                                backgroundColor: makeGradient(trendCtx, 'rgba(26, 107, 59, 0.28)',
                                    'rgba(26, 107, 59, 0.02)'),
                                tension: 0.35,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                            {
                                label: 'Appraisals',
                                data: initialPayload.comparisonTrendAppraisals || [],
                                borderColor: '#2d9a56',
                                backgroundColor: 'rgba(45, 154, 86, 0.12)',
                                tension: 0.35,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                padding: 12,
                                displayColors: true
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                        },
                    },
                });
            }

            const statusCanvas = document.getElementById('statusBreakdownChart');
            if (statusCanvas) {
                const statusCtx = statusCanvas.getContext('2d');
                charts.status = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: initialPayload.statusLabels || [],
                        datasets: [{
                            data: initialPayload.statusValues || [],
                            backgroundColor: initialPayload.statusColors || [],
                            borderWidth: 0,
                            hoverOffset: 8,
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false
                            },
                        },
                    },
                });
            }

            const departmentCanvas = document.getElementById('departmentComparisonChart');
            if (departmentCanvas) {
                const departmentCtx = departmentCanvas.getContext('2d');
                charts.department = new Chart(departmentCtx, {
                    type: 'bar',
                    data: {
                        labels: initialPayload.departmentLabels || [],
                        datasets: [{
                            label: 'Employees',
                            data: initialPayload.departmentValues || [],
                            backgroundColor: 'rgba(26, 107, 59, 0.85)',
                            borderRadius: 12,
                            maxBarThickness: 48,
                        }, ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            },
                        },
                    },
                });
            }

            applyPayload(initialPayload);

            async function refreshDashboard() {
                try {
                    const response = await fetch(summaryUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    applyPayload(payload);
                } catch (error) {
                    console.warn('Dashboard refresh failed', error);
                }
            }

            setInterval(refreshDashboard, 30000);
            window.addEventListener('focus', refreshDashboard);
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    refreshDashboard();
                }
            });
        });
    </script>
@endsection
