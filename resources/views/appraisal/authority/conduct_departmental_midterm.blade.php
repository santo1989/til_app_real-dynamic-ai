@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                     <a href="{{ route('appraisal.dept.index') }}" class="text-success text-decoration-none small fw-bold mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Back to Hub
                    </a>
                     <h2 class="fw-bold text-dark mb-1">Departmental Midterm Review</h2>
                     <p class="text-muted mb-0">Providing progress notes for the department of <strong>{{ $asgn->department->name }}</strong></p>
                </div>
                 <div class="bg-primary text-white px-4 py-2 rounded-pill fw-bold border border-mint-200">
                    <i class="fas fa-calendar-check me-1"></i> FY {{ $activeFY }}
                </div>
            </div>

            <div class="row g-4">
                <!-- Left: Form -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3 px-4">
                             <h5 class="fw-bold text-dark mb-0">Objective Progress Review</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                 <label class="small text-secondary text-uppercase fw-bold mb-1">Objective Description</label>
                                <div class="p-3 bg-light rounded-3 fw-bold text-dark border">
                                    {{ $asgn->master->title }}
                                </div>
                                 <div class="mt-2 d-flex gap-3 small text-muted">
                                    <span><i class="fas fa-weight-hanging me-1"></i> Weightage: {{ $asgn->weightage }}%</span>
                                    <span><i class="fas fa-clock me-1"></i> Timeline: {{ $asgn->timeline }}</span>
                                </div>
                            </div>

                            <form action="{{ route('appraisal.dept.midterm.store', $asgn->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                     <label for="notes" class="form-label fw-bold text-dark">Qualitative Progress / Action Points for 2nd Half</label>
                                     <p class="small text-muted mb-2 italic">Note: Your comments here will be automatically propagated to all employees in this department who are assigned this objective.</p>
                                    <textarea name="notes" id="notes" class="form-control border-slate-200 rounded-3 shadow-none focus-mint" rows="6" placeholder="Describe the overall departmental progress and specific action points for the second half..." required>{{ old('notes', $asgn->midterm_notes) }}</textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-mint rounded-pill py-2 fw-bold shadow-sm">
                                        <i class="fas fa-save me-2"></i> Save & Propagate Review
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Employee List for Context -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 2rem;">
                        <div class="card-header bg-white border-0 py-3 px-4">
                             <h5 class="fw-bold text-dark mb-0">Employees ({{ $employees->count() }})</h5>
                             <small class="text-muted">Who will receive this review</small>
                        </div>
                        <div class="card-body p-0 overflow-auto" style="max-height: 500px;">
                            <div class="list-group list-group-flush">
                                @foreach($employees as $emp)
                                <div class="list-group-item border-0 py-3 px-4">
                                    <div class="d-flex align-items-center">
                                         <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 fw-bold">
                                            {{ substr($emp->name, 0, 1) }}
                                        </div>
                                        <div>
                                             <div class="small fw-bold text-dark">{{ $emp->name }}</div>
                                             <div class="x-small text-muted">{{ $emp->designation }}</div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-mint { background-color: #1a6b3b; color: white; }
    .btn-mint:hover { background-color: #14552e; color: white; }
    .text-mint-600 { color: #1a6b3b; }
     .text-dark { color: #212529; }
    .border-mint-200 { border-color: #a7f3d0; }
    .focus-mint:focus { border-color: #1a6b3b; box-shadow: 0 0 0 0.25rem rgba(26, 107, 59, 0.25); }
    .x-small { font-size: 0.7rem; }
    .avatar-sm { width: 32px; height: 32px; font-size: 0.8rem; }
</style>
@endsection
