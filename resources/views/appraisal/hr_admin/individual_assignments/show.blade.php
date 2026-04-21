@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between pb-3">
                    <div>
                        <h2 class="fw-bold mb-1" style="color: #1a6b3b;">Objective Profile: {{ $activeFy->label ?? 'Current FY' }}</h2>
                        <div class="text-muted small">Comprehensive view of system and individual targets</div>
                    </div>
                    <a href="{{ route('individual-objective-assignments.index') }}" class="btn btn-light border px-4 fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Two Column Employee Information (Matching Set Dept Style) -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Employee Name</label>
                        <div class="h5 fw-bold text-dark mb-0">{{ $user->name }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Designation</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->designation ?: 'Staff' }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Date of Joining</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->date_of_joining ? $user->date_of_joining->format('d F Y') : 'N/A' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Employee ID</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->employee_id ?? 'N/A' }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Department / Team</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ ($user->department->name ?? 'N/A') }} {{ $user->team ? '('.$user->team->name.')' : '' }}</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Date of Objective Setting</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->objectives->sortBy('created_at')->first()?->created_at->format('d F Y') ?? now()->format('d F Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Combined Objectives Table: EXCEL STYLE (READ ONLY) -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 excel-view-table">
                    <thead style="background-color: #1a6b3b; color: #ffffff;">
                        <tr>
                            <th style="width: 60px;" class="text-center">Sl. #</th>
                            <th style="width: 60%;">Objectives / Action Plans</th>
                            <th style="width: 25%;">Timeline</th>
                            <th style="width: 15%;">Weightage %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- System Assigned Group (30%) -->
                        <tr class="table-group-header">
                            <td colspan="4" class="py-3 px-4 fw-bold" style="background-color: #e9f5ee; color: #1a6b3b;">
                                <i class="fas fa-layer-group me-2"></i> Departmental / Team Objectives (Total 30%)
                            </td>
                        </tr>
                        @forelse($deptObjectives as $index => $deptObj)
                            <tr class="bg-white">
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td class="px-3">{{ $deptObj->master->title }}</td>
                                <td class="px-3 italic text-muted">{{ $deptObj->timeline ?: 'N/A' }}</td>
                                <td class="text-center fw-bold">{{ $deptObj->weightage }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small italic">No departmental targets assigned yet.</td>
                            </tr>
                        @endforelse

                        <!-- Individual Assigned Group (70%) -->
                        <tr class="table-group-header">
                            <td colspan="4" class="py-3 px-4 fw-bold" style="background-color: #e9f5ee; color: #1a6b3b;">
                                <i class="fas fa-user-tag me-2"></i> Individual Objectives (Total 70%)
                            </td>
                        </tr>
                        @php $totalIndivWeight = 0; @endphp
                        @forelse($individualObjectives as $index => $indivObj)
                            @php $totalIndivWeight += $indivObj->weightage; @endphp
                            <tr class="bg-white">
                                <td class="text-center fw-bold">{{ $index + 1 }}</td>
                                <td class="px-3">{{ $indivObj->description }}</td>
                                <td class="px-3 text-muted italic">{{ $indivObj->target ?: 'N/A' }}</td>
                                <td class="text-center fw-bold">{{ $indivObj->weightage }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted small italic">
                                    <i class="fas fa-hourglass-start d-block mb-2 fa-2x opacity-25"></i>
                                    Employee has not set individual objectives yet.
                                </td>
                            </tr>
                        @endforelse

                        <!-- Summary Footer -->
                        <tr class="table-dark" style="background-color: #1a6b3b;">
                            <td colspan="3" class="text-end fw-bold py-3 text-uppercase small ls-1">Aggregate Performance Weightage</td>
                            <td class="text-center py-3">
                                <div class="h5 mb-0 fw-bold">{{ 30 + $totalIndivWeight }}%</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- IDP Preview (Read Only) -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex align-items-center gap-2 border-bottom border-success pb-2 mb-3">
                    <i class="fas fa-graduation-cap text-success"></i>
                    <h3 class="h5 fw-bold mb-0" style="color: #1a6b3b;">Individual Development Plan Setting</h3>
                </div>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden p-0">
                    <table class="table table-bordered mb-0 small small-table">
                        <thead class="table-light">
                            <tr>
                                <th>Skill Area</th>
                                <th>Development Objective</th>
                                <th>Action Plan</th>
                                <th>Timeline</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $idps = $user->idps()->where('financial_year', $activeFy?->label)->first(); @endphp
                            @if($idps && $idps->milestones->count() > 0)
                                @foreach($idps->milestones as $milestone)
                                    <tr>
                                        <td>{{ $milestone->skill_area }}</td>
                                        <td>{{ $milestone->development_objective }}</td>
                                        <td>{{ $milestone->action_plan }}</td>
                                        <td>{{ $milestone->timeline }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted italic">No IDP milestones defined.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Status Signatures Placeholder (Read Only) -->
        <div class="row mt-5 g-4">
            <div class="col-md-4">
                <div class="status-box p-3 border rounded shadow-sm bg-white text-center">
                    <div class="smaller text-muted fw-bold text-uppercase mb-2">Employee Signature</div>
                    <div class="py-3 border-bottom mb-2 bg-light rounded italic text-muted small">Digitally Captured</div>
                    <div class="fw-bold small">{{ $user->name }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="status-box p-3 border rounded shadow-sm bg-white text-center">
                    <div class="smaller text-muted fw-bold text-uppercase mb-2">Line Manager Approval</div>
                    <div class="py-3 border-bottom mb-2 bg-light rounded italic text-muted small">Pending Review</div>
                    <div class="fw-bold small">---</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="status-box p-3 border rounded shadow-sm bg-white text-center">
                    <div class="smaller text-muted fw-bold text-uppercase mb-2">HR Oversight</div>
                    <div class="py-3 border-bottom mb-2 bg-light rounded italic text-muted small">Under Review</div>
                    <div class="fw-bold small">{{ auth()->user()->name }} (Admin)</div>
                </div>
            </div>
        </div>

        <div class="footer text-center mt-5 py-4 border-top">
            <p class="text-muted small mb-0">Tosrifa Industries Limited - Performance Appraisal System &copy; {{ date('Y') }}</p>
        </div>
    </div>

    <style>
        .ls-1 { letter-spacing: 0.05em; }
        .smaller { font-size: 0.7rem; }
        .excel-view-table td { padding: 12px 15px; border-color: #dee2e6; }
        .excel-view-table th { font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase; }
        .italic { font-style: italic; }
        .bg-soft-success { background-color: rgba(45, 154, 86, 0.1); }
        .small-table td { padding: 10px 15px; }
    </style>
@endsection
