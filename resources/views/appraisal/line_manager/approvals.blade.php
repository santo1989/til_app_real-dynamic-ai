@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #1e293b;">Departmental Approvals & Oversight</h2>
                     <p class="text-muted small mb-0">Managing team performance plans for cycle: <span class="badge bg-success text-white fw-bold px-3">{{ $activeFY }}</span></p>
                </div>
                <div class="d-flex gap-2">
                    <div class="bg-white border rounded-pill px-3 py-2 shadow-sm d-flex align-items-center">
                        <span class="status-indicator-pulse me-2"></span>
                        <span class="small fw-bold text-success">Live Cycle</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <!-- Team Management Hub -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border mb-5">
        <div class="card-header bg-white py-4 px-4 border-0">
            <h5 class="fw-bold mb-0 text-dark opacity-75"><i class="fas fa-users-viewfinder me-2 text-success"></i> Department Staff Performance Status</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fbff; color: #1e293b;">
                    <tr>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Member Info</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Current Role</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-center">Lifecycle Status</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-center">Weightage</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-end">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($team as $employee)
                    <tr class="transition-hover">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                 <div class="bg-success text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 0.9rem;">
                                    {{ substr($employee->name, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $employee->name }}</div>
                                    <div class="text-muted smaller">ID: {{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                             <span class="small fw-medium text-muted">{{ $employee->designation ?: 'Team Member' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($employee->performance_status === 'approved')
                                <span class="badge rounded-pill px-3 py-2 shadow-sm" style="background-color: #1a6b3b; color: #fff;">
                                    <i class="fas fa-check-double me-1"></i> Plan Verified
                                </span>
                            @elseif($employee->performance_status === 'draft')
                                  <span class="badge rounded-pill px-3 py-2 bg-warning-soft text-dark border-warning border-opacity-25" style="background-color: rgba(245, 158, 11, 0.1);">
                                    <i class="fas fa-pen-nib me-1"></i> Draft Submitted
                                </span>
                            @else
                                  <span class="badge rounded-pill px-3 py-2 bg-light text-dark border">
                                    <i class="fas fa-hourglass-start me-1"></i> Not Started
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php 
                                $totalWeight = $employee->objectives->sum('weightage') + 30; // 30 is fixed for dept
                            @endphp
                            <div class="small fw-bold {{ $totalWeight == 100 ? 'text-success' : 'text-danger' }}">
                                {{ $totalWeight }}% / 100%
                            </div>
                            <div class="progress mt-1 mx-auto" style="height: 4px; width: 60px;">
                                <div class="progress-bar bg-{{ $totalWeight == 100 ? 'success' : 'warning' }}" style="width: {{ $totalWeight }}%"></div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('objectives.show_set_for_user', $employee->id) }}" class="btn btn-sm px-3 rounded-pill fw-bold transition-all shadow-sm-hover {{ $employee->performance_status === 'approved' ? 'btn-outline-success border-success' : 'text-white' }}" style="{{ $employee->performance_status === 'approved' ? '' : 'background-color: #1a6b3b;' }}">
                                @if($employee->performance_status === 'not_started')
                                    <i class="fas fa-plus-circle me-1"></i> Set Objectives
                                @elseif($employee->performance_status === 'approved')
                                    <i class="fas fa-eye me-1"></i> View Plan
                                @else
                                    <i class="fas fa-edit me-1"></i> Manage Plan
                                @endif
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white py-4 border-0 text-center opacity-50">
            <span class="smaller fw-bold ls-1 text-muted text-uppercase">End of departmental list</span>
        </div>
    </div>
</div>

<style>
     .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.7rem; }
    .transition-hover:hover { background-color: #f8fbff; }
    .shadow-sm-hover:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); transform: translateY(-1px); }
    .transition-all { transition: all 0.3s ease; }
    
    .status-indicator-pulse {
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
        display: inline-block;
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
