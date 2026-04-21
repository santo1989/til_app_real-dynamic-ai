@extends('layouts.app')

@section('content')
    @php
        $fyLabel = $activeFinancialYear?->label ?? $activeFinancialYear?->name ?? 'None Set';
        $isCycleOpen = $activeFinancialYear?->is_active ?? false;
        
        // Colors and Icons mapping for appraisal statuses
        $statusMap = [
            'draft' => ['icon' => 'fa-pen-to-square', 'color' => '#6366f1', 'bg' => 'rgba(99, 102, 241, 0.1)'],
            'submitted' => ['icon' => 'fa-paper-plane', 'color' => '#f59e0b', 'bg' => 'rgba(245, 158, 11, 0.1)'],
            'approved' => ['icon' => 'fa-check-double', 'color' => '#10b981', 'bg' => 'rgba(16, 185, 129, 0.1)'],
            'completed' => ['icon' => 'fa-circle-check', 'color' => '#06b6d4', 'bg' => 'rgba(6, 182, 212, 0.1)'],
        ];

        $appraisalStats = $stats['appraisal_stats'] ?? [];
        $totalAppraisals = array_sum($appraisalStats);
    @endphp

    <div class="mb-4">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h4 class="fw-bold mb-1">HR Executive Dashboard</h4>
                <p class="text-muted mb-0">Tosrifa Industries Limited Performance Management Overview</p>
            </div>
            <div class="col-md-5 text-md-end mt-2 mt-md-0">
                <div class="d-inline-flex align-items-center p-2 px-3 bg-white border rounded-pill shadow-sm">
                    <span class="small text-muted me-2">Active Cycle:</span>
                    <span class="badge bg-primary rounded-pill me-2">{{ $fyLabel }}</span>
                    @if($isCycleOpen)
                        <span class="status-indicator-pulse"></span>
                        <span class="small fw-semibold text-success">Active</span>
                    @else
                        <span class="small fw-semibold text-danger">Locked</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-primary-soft p-3 rounded-circle text-primary">
                            <i class="fas fa-users-viewfinder fa-xl"></i>
                        </div>
                        <div class="text-end">
                            <div class="stat-value fs-3 fw-bold">{{ $stats['total_users'] }}</div>
                            <div class="small text-muted">Total Workforce</div>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <div class="progress" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ ($stats['active_users'] / max(1, $stats['total_users'])) * 100 }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between mt-2 small">
                            <span class="text-success">{{ $stats['active_users'] }} Active</span>
                            <span class="text-muted">{{ $stats['inactive_users'] }} Inactive</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-info-soft p-3 rounded-circle text-info">
                            <i class="fas fa-sitemap fa-xl"></i>
                        </div>
                        <div class="text-end">
                            <div class="stat-value fs-3 fw-bold">{{ $stats['total_departments'] }}</div>
                            <div class="small text-muted">Departments</div>
                        </div>
                    </div>
                    <div class="pt-2 text-muted small">
                        Monitoring <strong>{{ $stats['total_teams'] }}</strong> specialized teams
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-warning-soft p-3 rounded-circle text-warning">
                            <i class="fas fa-graduation-cap fa-xl"></i>
                        </div>
                        <div class="text-end">
                            <div class="stat-value fs-3 fw-bold">{{ $stats['total_idps'] }}</div>
                            <div class="small text-muted">Active IDPs</div>
                        </div>
                    </div>
                    <div class="pt-2 text-muted small">
                        Supporting professional growth
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm overflow-hidden h-100 bg-danger-soft">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-danger p-3 rounded-circle text-white">
                            <i class="fas fa-triangle-exclamation fa-xl"></i>
                        </div>
                        <div class="text-end">
                            <div class="stat-value fs-3 fw-bold text-danger">{{ $stats['open_pips'] }}</div>
                            <div class="small text-danger opacity-75">Open PIPs</div>
                        </div>
                    </div>
                    <div class="pt-2 text-danger small">
                         Requires immediate monitoring
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Appraisal Life Cycle Progress -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0">Appraisal Lifecycle Status <span class="small text-muted fw-normal">({{ $fyLabel }})</span></h6>
                        <span class="small text-muted">Total: {{ $totalAppraisals }}</span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        @foreach(['draft', 'submitted', 'approved', 'completed'] as $status)
                            @php 
                                $count = $appraisalStats[$status] ?? 0;
                                $percent = $totalAppraisals > 0 ? ($count / $totalAppraisals) * 100 : 0;
                                $config = $statusMap[$status];
                            @endphp
                            <div class="col-6 col-md-3">
                                <div class="p-3 rounded-4 border text-center h-100 transition-hover" style="background: white;">
                                    <div class="mb-2 mx-auto d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: {{ $config['bg'] }}; color: {{ $config['color'] }}; font-size: 1.25rem;">
                                        <i class="fas {{ $config['icon'] }}"></i>
                                    </div>
                                    <div class="fs-4 fw-bold mb-0">{{ $count }}</div>
                                    <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">{{ $status }}</div>
                                    <div class="progress mt-3" style="height: 4px;">
                                        <div class="progress-bar" style="width: {{ $percent }}%; background-color: {{ $config['color'] }};"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h6 class="fw-bold mb-0">Quick Operations</h6>
                </div>
                <div class="card-body p-4 pt-2">
                    <div class="d-grid gap-2">
                        <a href="{{ route('users.create') }}" class="btn btn-light border-0 text-start p-3 rounded-3 transition-hover d-flex align-items-center">
                            <div class="bg-primary-soft p-2 rounded me-3 text-primary"><i class="fas fa-plus"></i></div>
                            <div>
                                <div class="fw-bold small">Onboard Employee</div>
                                <div class="text-muted smaller">Add new user to system</div>
                            </div>
                        </a>
                        <a href="{{ route('individual-objective-masters.index') }}" class="btn btn-light border-0 text-start p-3 rounded-3 transition-hover d-flex align-items-center">
                            <div class="bg-info-soft p-2 rounded me-3 text-info"><i class="fas fa-bullseye"></i></div>
                            <div>
                                <div class="fw-bold small">Manage Masters</div>
                                <div class="text-muted smaller">Update objective library</div>
                            </div>
                        </a>
                        <a href="{{ route('reports.index') }}" class="btn btn-light border-0 text-start p-3 rounded-3 transition-hover d-flex align-items-center">
                            <div class="bg-success-soft p-2 rounded me-3 text-success"><i class="fas fa-file-chart-column"></i></div>
                            <div>
                                <div class="fw-bold small">Audit Reports</div>
                                <div class="text-muted smaller">Export cycle compliance data</div>
                            </div>
                        </a>
                        <a href="{{ route('financial-years.index') }}" class="btn btn-light border-0 text-start p-3 rounded-3 transition-hover d-flex align-items-center">
                            <div class="bg-warning-soft p-2 rounded me-3 text-warning"><i class="fas fa-clock"></i></div>
                            <div>
                                <div class="fw-bold small">Cycle Management</div>
                                <div class="text-muted smaller">Open or lock financial years</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0">System Activity</h6>
                </div>
                <div class="card-body p-4">
                    <div class="list-group list-group-flush small">
                        @forelse($recentLogs as $log)
                            <div class="list-group-item px-0 border-0 mb-2">
                                <div class="d-flex align-items-start">
                                    <div class="bg-light p-2 rounded me-3 text-muted">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ ucwords(str_replace('_', ' ', $log->action)) }}</div>
                                        <div class="text-muted" style="font-size: 0.8rem;">{{ $log->details }}</div>
                                        <div class="smaller opacity-75 mt-1">{{ $log->created_at->diffForHumans() }} by {{ $log->user->name ?? 'System' }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No recent activity</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Departments Overview -->
    <x-ui.datatable-card title="Organizational Units" subtitle="Heads and structure across departments" icon="fa-building-columns">
        <div class="table-responsive-custom">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Department</th>
                        <th class="text-center">Teams</th>
                        <th>Department Head</th>
                        <th class="text-end">Staff Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($departments as $department)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $department->name }}</div>
                                <div class="small text-muted">{{ $department->code ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $department->teams_count }} Teams</span>
                            </td>
                            <td>
                                @if($department->head)
                                    <div class="d-flex align-items-center">
                                        <div class="small">{{ $department->head->name }}</div>
                                    </div>
                                @else
                                    <span class="text-muted small italic">Vacant</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-semibold">{{ $department->users_count }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.datatable-card>

    <style>
        .bg-primary-soft { background-color: rgba(67, 97, 238, 0.1); }
        .bg-info-soft { background-color: rgba(72, 149, 239, 0.1); }
        .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }
        .bg-danger-soft { background-color: rgba(239, 35, 60, 0.1); }
        .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); }
        
        .transition-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .smaller { font-size: 0.75rem; }
        
        .status-indicator-pulse {
            width: 10px;
            height: 10px;
            background-color: #10b981;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 1);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
@endsection
