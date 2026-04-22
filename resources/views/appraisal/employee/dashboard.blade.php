@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" 
                 style="background: linear-gradient(135deg, #1a6b3b 0%, #2d9a56 100%);">
                <div class="card-body p-5 position-relative">
                    <div class="position-relative z-index-1">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-white bg-opacity-25 rounded-circle p-2">
                                <i class="fas fa-user-circle text-white fs-2"></i>
                            </div>
                            <span class="badge bg-white text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                                {{ auth()->user()->role === 'employee' ? 'Staff Member' : strtoupper(auth()->user()->role) }}
                            </span>
                        </div>
                        <h1 class="display-5 fw-bold text-white mb-2">Welcome Back, {{ explode(' ', auth()->user()->name)[0] }}!</h1>
                        <p class="text-white text-opacity-75 fs-5 mb-0 ls-1">
                            Current Cycle: <span class="fw-bold">{{ \App\Models\FinancialYear::active()->label ?? 'Active' }} Performance Appraisal</span>
                        </p>
                    </div>
                    <!-- Decorative background icon -->
                    <i class="fas fa-chart-line position-absolute bottom-0 end-0 text-white opacity-10" 
                       style="font-size: 15rem; transform: translate(10%, 20%);"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Insights -->
    <div class="row g-4 mb-5">
        <!-- Objectives Card -->
        <div class="col-md-3">
            <a href="{{ route('objectives.my') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 dashboard-card hover-lift transition-all">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-success-light p-3 rounded-4">
                            <i class="fas fa-bullseye text-success fs-4"></i>
                        </div>
                        <span class="badge bg-light text-dark fw-bold px-2 py-1 rounded">My Plan</span>
                    </div>
                    <div class="h3 fw-bold text-dark mb-1">{{ $stats['my_objectives'] ?? 0 }}</div>
                    <div class="text-muted small fw-bold text-uppercase ls-1">Objectives Set</div>
                    <div class="mt-3 d-flex align-items-center text-success fw-bold small">
                        View / Edit <i class="fas fa-arrow-right ms-2 mt-1"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Midterm Card -->
        <div class="col-md-3">
            <a href="{{ route('appraisals.midterm') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 dashboard-card hover-lift transition-all">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-warning-light p-3 rounded-4">
                            <i class="fas fa-hourglass-half text-warning fs-4"></i>
                        </div>
                        <span class="badge bg-light text-dark fw-bold px-2 py-1 rounded">Cycle 1</span>
                    </div>
                    <div class="h3 fw-bold text-dark mb-1">{{ $stats['midterm_due'] ?? 0 }}</div>
                    <div class="text-muted small fw-bold text-uppercase ls-1">Midterm Due</div>
                    <div class="mt-3 d-flex align-items-center text-warning fw-bold small">
                        Self Appraisal <i class="fas fa-arrow-right ms-2 mt-1"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- Year-End Card -->
        <div class="col-md-3">
            <a href="{{ route('appraisals.yearend') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 dashboard-card hover-lift transition-all">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary-light p-3 rounded-4">
                            <i class="fas fa-award text-primary fs-4"></i>
                        </div>
                        <span class="badge bg-light text-dark fw-bold px-2 py-1 rounded">Final</span>
                    </div>
                    <div class="h3 fw-bold text-dark mb-1">{{ $stats['yearend_due'] ?? 0 }}</div>
                    <div class="text-muted small fw-bold text-uppercase ls-1">Final Review</div>
                    <div class="mt-3 d-flex align-items-center text-primary fw-bold small">
                        Year-End Review <i class="fas fa-arrow-right ms-2 mt-1"></i>
                    </div>
                </div>
            </a>
        </div>

        <!-- IDP Card -->
        <div class="col-md-3">
            <a href="{{ route('idp.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 dashboard-card hover-lift transition-all">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-info-light p-3 rounded-4">
                            <i class="fas fa-graduation-cap text-info fs-4"></i>
                        </div>
                        <span class="badge bg-light text-dark fw-bold px-2 py-1 rounded">Growth</span>
                    </div>
                    <div class="h3 fw-bold text-dark mb-1">{{ $stats['my_idps'] ?? 0 }}</div>
                    <div class="text-muted small fw-bold text-uppercase ls-1">IDP Milestones</div>
                    <div class="mt-3 d-flex align-items-center text-info fw-bold small">
                        Your Dev Plan <i class="fas fa-arrow-right ms-2 mt-1"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Secondary Context -->
    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 border-start border-5 border-success">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-info-circle text-success"></i>
                    <h5 class="fw-bold mb-0">Employment Context</h5>
                </div>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-muted smaller fw-bold ls-1 text-uppercase mb-1">Department</div>
                        <div class="fw-bold text-dark">{{ auth()->user()->department->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted smaller fw-bold ls-1 text-uppercase mb-1">Team</div>
                        <div class="fw-bold text-dark">{{ auth()->user()->team->name ?? 'Not Assigned' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted smaller fw-bold ls-1 text-uppercase mb-1">Line Manager</div>
                        <div class="fw-bold text-dark">{{ auth()->user()->lineManager->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
             <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-light border">
                <h6 class="fw-bold mb-3"><i class="fas fa-calendar-check me-2 text-success"></i>Upcoming Deadlines</h6>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 bg-white rounded border small fw-bold">15 NOV</div>
                        <div class="small fw-semibold">Objective Setting Closes</div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2 bg-white rounded border small fw-bold">31 MAR</div>
                        <div class="small fw-semibold">Final Appraisal Submission</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.7rem; }
    .bg-success-light { background-color: rgba(26, 107, 59, 0.1); }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
    .bg-primary-light { background-color: rgba(13, 110, 253, 0.1); }
    .bg-info-light { background-color: rgba(13, 202, 240, 0.1); }
    .dashboard-card { border: 1px solid transparent !important; }
    .dashboard-card:hover { border-color: #dee2e6 !important; }
    .hover-lift:hover { transform: translateY(-5px); transition: all 0.3s ease; }
    .transition-all { transition: all 0.3s ease; }
</style>
@endsection
