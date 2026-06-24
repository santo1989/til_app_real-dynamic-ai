@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('appraisal.dept.index') }}" class="text-success-600 text-decoration-none small fw-bold mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Back to Hub
                    </a>
                     <h2 class="fw-bold text-dark mb-1">Departmental Final Evaluation</h2>
                     <p class="text-muted mb-0">Quantitative scoring for <strong>{{ $asgn->department->name }}</strong></p>
                </div>
                 <div class="bg-success text-white px-4 py-2 rounded-pill fw-bold border border-success-200">
                    <i class="fas fa-calendar-check me-1"></i> FY {{ $activeFY }}
                </div>
            </div>

            <div class="row g-4">
                <!-- Left: Form -->
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-0 py-3 px-4">
                             <h5 class="fw-bold text-dark mb-0">Final Performance Marking</h5>
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

                            <form action="{{ route('appraisal.dept.final.store', $asgn->id) }}" method="POST" 
                                  x-data="{ 
                                      score: {{ old('score', $asgn->final_score ?? 0) }},
                                      get calculatedRating() {
                                          if (this.score >= 90) return 'Outstanding';
                                          if (this.score >= 80) return 'Excellent';
                                          if (this.score >= 70) return 'Good';
                                          if (this.score >= 60) return 'Average';
                                          return 'Below Average';
                                      }
                                  }">
                                @csrf
                                <div class="row g-4 mb-4">
                                    <div class="col-md-6">
                                         <label for="score" class="form-label fw-bold text-dark">Achievement Score (0-100)</label>
                                        <div class="input-group">
                                            <input type="number" name="score" id="score" x-model.number="score" class="form-control border-slate-200 rounded-start-3 shadow-none focus-success" min="0" max="100" required>
                                             <span class="input-group-text bg-light border-slate-200 rounded-end-3 fw-bold text-muted">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                         <label for="rating" class="form-label fw-bold text-dark">Performance Rating</label>
                                        <input type="text" name="rating" id="rating" :value="calculatedRating" class="form-control border-slate-200 bg-light rounded-3 shadow-none fw-bold" readonly tabindex="-1">
                                    </div>
                                </div>

                                <div class="alert alert-info border-0 rounded-4 p-3 mb-4">
                                    <div class="d-flex">
                                        <i class="fas fa-info-circle mt-1 me-3"></i>
                                        <div>
                                            <strong>Impact:</strong> This score will determine <span x-text="score"></span>% of the achievement for this objective for every employee in this department.
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success rounded-pill py-2 fw-bold shadow-sm">
                                        <i class="fas fa-check-circle me-2"></i> Finalize & Propagate Marks
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Employee List -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 2rem;">
                        <div class="card-header bg-white border-0 py-3 px-4">
                             <h5 class="fw-bold text-dark mb-0">Employee Roster ({{ $employees->count() }})</h5>
                        </div>
                        <div class="card-body p-0 overflow-auto" style="max-height: 500px;">
                            <div class="list-group list-group-flush">
                                @foreach($employees as $emp)
                                <div class="list-group-item border-0 py-3 px-4">
                                    <div class="d-flex align-items-center">
                                         <div class="avatar-sm rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 fw-bold">
                                            {{ substr($emp->name, 0, 1) }}
                                        </div>
                                        <div>
                                             <div class="small fw-bold text-dark">{{ $emp->name }}</div>
                                             <div class="x-small text-muted">{{ $emp->employee_id }}</div>
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
    .text-success-600 { color: #198754; }
     .text-dark { color: #212529; }
    .text-success-700 { color: #1b5e20; }
    .focus-success:focus { border-color: #198754; box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
    .x-small { font-size: 0.7rem; }
    .avatar-sm { width: 32px; height: 32px; font-size: 0.8rem; }
</style>
@endsection
