@extends('layouts.app')

@section('content')
@php
    $fyLabel = $activeFinancialYear?->label ?? 'N/A';
    $teamSize = $stats['team_size'] ?? 0;
    $pendingApprovals = $stats['pending_approvals'] ?? 0;
    $pendingMidterms = $stats['pending_midterms'] ?? 0;
    $pendingYearend = $stats['pending_yearend'] ?? 0;
    $teamIdps = $stats['team_idps'] ?? 0;
    
    // Dummy data for premium look (replace with actual data when available)
    $dummyPendingTasks = [
        ['type' => 'approval', 'title' => 'Objectives Pending Approval', 'count' => 3, 'priority' => 'high', 'time' => '2h ago'],
        ['type' => 'midterm', 'title' => 'Midterm Reviews Due', 'count' => 2, 'priority' => 'medium', 'time' => '5h ago'],
        ['type' => 'idp', 'title' => 'IDPs Ready for Review', 'count' => 4, 'priority' => 'low', 'time' => '1d ago'],
    ];
    
    $dummyRecentActivity = [
        ['action' => 'Objective Approved', 'user' => 'Karim Miah', 'time' => '10 min ago'],
        ['action' => 'IDP Submitted', 'user' => 'Rashida Begum', 'time' => '1h ago'],
        ['action' => 'Self Assessment Received', 'user' => 'Mostafa Kamal', 'time' => '3h ago'],
    ];
@endphp

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" 
                 style="background: linear-gradient(135deg, #1a6b3b 0%, #2d9a56 100%);">
                <div class="card-body p-5 position-relative">
                    <div class="position-relative z-index-1">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                <i class="fas fa-user-tie text-white fs-2"></i>
                            </div>
                             <span class="badge bg-white text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                                Line Manager
                            </span>
                        </div>
                        <h1 class="display-5 fw-bold text-white mb-2">Welcome Back, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
                        <p class="text-white text-opacity-75 fs-5 mb-0 ls-1">
                            Leading <span class="fw-bold text-white">{{ $teamSize }}</span> team members | 
                            Cycle: <span class="fw-bold">{{ $fyLabel }}</span> Performance Appraisal
                        </p>
                    </div>
                    <!-- Decorative background icon -->
                    <i class="fas fa-users-cog position-absolute bottom-0 end-0 text-white opacity-10" 
                       style="font-size: 12rem; transform: translate(10%, 20%);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-primary-soft p-3 rounded-circle">
                            <i class="fas fa-users text-primary fs-5"></i>
                        </div>
                          <span class="badge bg-light text-dark">Team</span>
                    </div>
                    <div class="fs-2 fw-bold text-dark">{{ $teamSize }}</div>
                    <div class="small text-muted">Team Members</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <a href="{{ route('objectives.approvals') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 rounded-4 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="bg-warning-soft p-3 rounded-circle">
                                <i class="fas fa-check-circle text-warning fs-5"></i>
                            </div>
                             <span class="badge bg-warning text-dark">Action</span>
                        </div>
                        <div class="fs-2 fw-bold text-dark">{{ $pendingApprovals }}</div>
                        <div class="small text-muted">Pending Approvals</div>
                    </div>
                </div>
            </a>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-info-soft p-3 rounded-circle">
                            <i class="fas fa-hourglass-half text-info fs-5"></i>
                        </div>
                          <span class="badge bg-light text-dark">Review</span>
                    </div>
                    <div class="fs-2 fw-bold text-dark">{{ $pendingMidterms }}</div>
                    <div class="small text-muted">Midterm Reviews</div>
                </div>
            </div>
        </div>
        
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="bg-success-soft p-3 rounded-circle">
                            <i class="fas fa-graduation-cap text-success fs-5"></i>
                        </div>
                          <span class="badge bg-light text-dark">Growth</span>
                    </div>
                    <div class="fs-2 fw-bold text-dark">{{ $teamIdps }}</div>
                    <div class="small text-muted">Team IDPs</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Action Hub -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0"><i class="fas fa-tasks me-2 text-success"></i>Action Hub</h6>
                         <span class="badge bg-success text-white rounded-pill">{{ count($dummyPendingTasks) }} Pending</span>
                    </div>
                </div>
                <div class="card-body px-4 py-3">
                    @foreach($dummyPendingTasks as $task)
                    @php
                        if ($task['priority'] === 'high') {
                            $borderColor = '#ef4444';
                        } elseif ($task['priority'] === 'medium') {
                            $borderColor = '#f59e0b';
                        } elseif ($task['priority'] === 'low') {
                            $borderColor = '#10b981';
                        } else {
                            $borderColor = '#6b7280';
                        }
                    @endphp
                    <div class="p-3 mb-3 border rounded-4 bg-white hover-lift" style="border-left: 4px solid {{ $borderColor }} !important;">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-bold small text-dark">{{ $task['title'] }}</div>
                                <div class="text-muted small">{{ $task['count'] }} items</div>
                            </div>
                            <span class="text-xs text-muted">{{ $task['time'] }}</span>
                        </div>
                        <div class="mt-2">
                            <a href="#" class="btn btn-success btn-sm py-1 px-3 rounded-pill fw-bold">
                                View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-success"></i>Recent Activity</h6>
                    </div>
                </div>
                <div class="card-body px-4 py-2">
                    <div class="list-group list-group-flush">
                        @foreach($dummyRecentActivity as $activity)
                        <div class="list-group-item border-0 px-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-success-soft rounded-circle p-2 me-3">
                                    <i class="fas fa-check text-success small"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small">{{ $activity['action'] }}</div>
                                    <div class="text-muted smaller">{{ $activity['user'] }}</div>
                                </div>
                                <span class="text-muted small">{{ $activity['time'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Row -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <span class="fw-bold text-muted me-2"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions:</span>
                        
                        <a href="{{ route('objectives.team') }}" class="btn btn-success btn-sm rounded-pill px-4 hover-lift">
                            <i class="fas fa-users me-1"></i> Team Objectives
                        </a>
                        
                        <a href="{{ route('objectives.approvals') }}" class="btn btn-outline-success btn-sm rounded-pill px-4 hover-lift">
                            <i class="fas fa-check-double me-1"></i> Approvals
                            @if($pendingApprovals > 0)
                            <span class="badge bg-danger ms-1">{{ $pendingApprovals }}</span>
                            @endif
                        </a>
                        
                        <a href="{{ route('team.objectives.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-4 hover-lift">
                            <i class="fas fa-building me-1"></i> Dept Objectives
                        </a>
                        
                        <a href="{{ route('idp-development-objectives.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-4 hover-lift">
                            <i class="fas fa-sitemap me-1"></i> IDP Mappings
                        </a>
                        
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-4 hover-lift">
                            <i class="fas fa-chart-bar me-1"></i> Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(37, 99, 235, 0.1); }
    .bg-warning-soft { background-color: rgba(245, 158, 11, 0.1); }
    .bg-info-soft { background-color: rgba(59, 130, 246, 0.1); }
    .bg-success-soft { background-color: rgba(16, 185, 129, 0.1); }
    
    .hover-lift:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .rounded-4 { border-radius: 1rem !important; }
</style>
@endsection