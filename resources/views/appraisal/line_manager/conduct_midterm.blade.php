@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                     <a href="{{ route('appraisal.midterm.list') }}" class="text-success text-decoration-none small fw-bold mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                     <h2 class="fw-bold text-dark mb-1">Midterm Evaluation</h2>
                     <p class="text-muted mb-0">Recording qualitative notes for {{ $employee->name }}</p>
                </div>
                 <div class="bg-primary text-white px-4 py-2 rounded-pill fw-bold border border-primary border-opacity-25">
                    <i class="fas fa-calendar-check me-1"></i> FY {{ $activeFY }}
                </div>
            </div>

            <!-- Employee Bio Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto mb-3 mb-md-0 text-center">
                             <div class="avatar-xl rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 800;">
                                {{ substr($employee->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                     <label class="small text-secondary text-uppercase fw-bold mb-0">Employee Name</label>
                                    <div class="fw-bold text-dark">{{ $employee->name }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                     <label class="small text-secondary text-uppercase fw-bold mb-0">Designation</label>
                                    <div class="fw-bold text-dark">{{ $employee->designation }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                     <label class="small text-secondary text-uppercase fw-bold mb-0">Department</label>
                                    <div class="fw-bold text-dark">{{ $employee->department->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                     <label class="small text-secondary text-uppercase fw-bold mb-0">Employee ID</label>
                                    <div class="fw-bold text-dark">{{ $employee->employee_id }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluation Form -->
            <form action="{{ route('appraisal.midterm.store') }}" method="POST">
                @csrf
                <input type="hidden" name="appraisal_id" value="{{ $appraisal->id }}">

                <!-- Objectives Table Card -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3 px-4">
                         <h5 class="fw-bold text-dark mb-0">Qualitative Progress Review</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                     <th class="px-4 py-3 text-secondary small fw-bold text-uppercase" style="width: 5%">SL</th>
                                     <th class="px-4 py-3 text-secondary small fw-bold text-uppercase" style="width: 40%">Objectives / Action Plans</th>
                                     <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-center">Weight %</th>
                                     <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Progress / Action Points for 2nd Half</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dept Objectives -->
                                <tr class="bg-mint-50">
                                     <td colspan="4" class="px-4 py-2 text-dark fw-bold small">DEPARTMENTAL OBJECTIVES (30%)</td>
                                </tr>
                                @foreach($deptObjectives as $index => $deptObj)
                                <tr>
                                     <td class="px-4 py-4 text-muted fw-medium text-center">{{ $loop->iteration }}</td>
                                     <td class="px-4 py-4">
                                         <div class="fw-bold text-dark mb-1">{{ $deptObj->master->title }}</div>
                                         <div class="small text-muted"><i class="far fa-clock me-1"></i> Timeline: {{ $deptObj->timeline }}</div>
                                     </td>
                                     <td class="px-4 py-4 text-center fw-bold text-dark">{{ $deptObj->weightage }}%</td>
                                     <td class="px-4 py-4">
                                         <div class="p-3 bg-light rounded-3 text-dark border border-slate-200">
                                             @if($deptObj->midterm_status === 'completed')
                                                 {{ $deptObj->midterm_notes }}
                                             @else
                                                 <em class="text-muted">Awaiting Authority Review</em>
                                             @endif
                                         </div>
                                         <div class="small text-success mt-1 text-center fw-bold" style="font-size: 0.7rem;"><i class="fas fa-lock me-1"></i> Evaluated by Authority</div>
                                     </td>
                                </tr>
                                @endforeach
                                <!-- Individual Objectives -->
                                 <tr class="bg-light">
                                     <td colspan="4" class="px-4 py-2 text-dark fw-bold small">INDIVIDUAL OBJECTIVES (70%)</td>
                                </tr>
                                @foreach($objectives->where('is_departmental', false) as $index => $obj)
                                <tr>
                                    <td class="px-4 py-4 text-muted fw-medium">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4">
                                         <div class="fw-bold text-dark mb-1">{{ $obj->description }}</div>
                                         <div class="small text-muted"><i class="far fa-clock me-1"></i> Timeline: {{ $obj->timeline }}</div>
                                    </td>
                                     <td class="px-4 py-4 text-center fw-bold text-dark">{{ $obj->weightage }}%</td>
                                    <td class="px-4 py-4">
                                        <textarea name="notes[{{ $obj->id }}]" class="form-control border-slate-200 rounded-3 shadow-none focus-mint" rows="2" placeholder="Describe progress and 2nd half action points...">{{ $appraisal->ratings['notes'][$obj->id] ?? '' }}</textarea>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="text-end mb-5">
                    <button type="submit" class="btn btn-lg px-5 rounded-pill fw-bold text-white shadow-lg transition-all" style="background-color: #1a6b3b;">
                        <i class="fas fa-paper-plane me-2"></i> Submit Midterm Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

            <style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }
     .text-mint-50 { background-color: #f0fdfa; }
    .text-mint-600 { color: #0d9488; }
    .text-mint-700 { color: #0f766e; }
    .text-mint-800 { color: #115e59; }
    .focus-mint:focus { border-color: #1a6b3b !important; box-shadow: 0 0 0 0.25rem rgba(26, 107, 59, 0.15) !important; }
    .rounded-4 { border-radius: 1rem !important; }
    .transition-all { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
    .text-dark { color: #212529; }
    .border-mint-200 { border-color: #a7f3d0; }
    .transition-all:hover { transform: translateY(-2px); filter: brightness(1.1); }
</style>
@endsection
