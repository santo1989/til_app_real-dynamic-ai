@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="finalAppraisal()">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('appraisal.final.list') }}" class="text-mint-600 text-decoration-none small fw-bold mb-2 d-inline-block">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                    <h2 class="fw-bold text-slate-800 mb-1">Final Performance Assessment</h2>
                    <p class="text-slate-500 mb-0">Recording marks and ratings for {{ $employee->name }}</p>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary px-4 py-2 rounded-pill fw-bold border border-primary border-opacity-25 text-uppercase">
                    <i class="fas fa-flag-checkered me-1"></i> Year Final Review
                </div>
            </div>

            <!-- Employee Bio Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-auto mb-3 mb-md-0 text-center">
                            <div class="avatar-xl rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 800;">
                                {{ substr($employee->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="col-md">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="small text-slate-400 text-uppercase fw-bold mb-0">Employee</label>
                                    <div class="fw-bold text-slate-800">{{ $employee->name }}</div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small text-slate-400 text-uppercase fw-bold mb-0">ID</label>
                                    <div class="fw-bold text-slate-800">{{ $employee->employee_id }}</div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small text-slate-400 text-uppercase fw-bold mb-0">Dept</label>
                                    <div class="fw-bold text-slate-800">{{ $employee->department->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small text-slate-400 text-uppercase fw-bold mb-0">Financial Year</label>
                                    <div class="fw-bold text-slate-800">{{ $activeFY }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Assessment Form -->
            <form action="{{ route('appraisal.final.store') }}" method="POST">
                @csrf
                <input type="hidden" name="appraisal_id" value="{{ $appraisal->id }}">

                <!-- Quantitative Marking Table -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold text-slate-800 mb-0">Performance Scoring</h5>
                        <div class="small text-slate-500 fw-medium">Score = (Weight * % TA) / 100</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase" style="width: 5%">SL</th>
                                    <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase" style="width: 35%">Objectives / Action Plans</th>
                                    <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-center">Weight %</th>
                                    <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-center" style="width: 15%">% Target Achieved (TA)</th>
                                    <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-end" style="width: 15%">Final Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dept Objectives -->
                                <tr class="bg-mint-50">
                                    <td colspan="5" class="px-4 py-2 text-mint-800 fw-bold small uppercase">Departmental Objectives (30%)</td>
                                </tr>
                                @foreach($objectives->where('is_departmental', true) as $obj)
                                <tr>
                                    <td class="px-4 py-4 text-slate-500 fw-medium text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4">
                                        <div class="fw-bold text-slate-800 mb-1">{{ $obj->description }}</div>
                                        @if(isset($appraisal->ratings['notes'][$obj->id]))
                                            <div class="p-2 bg-light rounded-3 small text-slate-600 border border-slate-100 mt-2">
                                                <i class="fas fa-comment-dots me-1 text-mint-500"></i> 
                                                <strong>Midterm Note:</strong> {{ $appraisal->ratings['notes'][$obj->id] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center fw-bold text-slate-700">{{ $obj->weightage }}%</td>
                                    <td class="px-4 py-4">
                                        <input type="number" name="scores[{{ $obj->id }}]" 
                                               x-model.number="data[{{ $obj->id }}].ta"
                                               class="form-control text-center rounded-3 border-slate-200 focus-primary h-px-45 fw-bold" 
                                               min="0" max="100" placeholder="0-100">
                                    </td>
                                    <td class="px-4 py-4 text-end">
                                        <div class="h5 mb-0 fw-bold text-slate-800" x-text="calculateScore({{ $obj->id }}, {{ $obj->weightage }})">0.00</div>
                                    </td>
                                </tr>
                                @endforeach

                                <!-- Individual Objectives -->
                                <tr class="bg-slate-50">
                                    <td colspan="5" class="px-4 py-2 text-slate-800 fw-bold small uppercase">Individual Objectives (70%)</td>
                                </tr>
                                @foreach($objectives->where('is_departmental', false) as $obj)
                                <tr>
                                    <td class="px-4 py-4 text-slate-500 fw-medium text-center">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-4">
                                        <div class="fw-bold text-slate-800 mb-1">{{ $obj->description }}</div>
                                        @if(isset($appraisal->ratings['notes'][$obj->id]))
                                            <div class="p-2 bg-light rounded-3 small text-slate-600 border border-slate-100 mt-2">
                                                <i class="fas fa-comment-dots me-1 text-mint-500"></i> 
                                                <strong>Midterm Note:</strong> {{ $appraisal->ratings['notes'][$obj->id] }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center fw-bold text-slate-700">{{ $obj->weightage }}%</td>
                                    <td class="px-4 py-4">
                                        <input type="number" name="scores[{{ $obj->id }}]" 
                                               x-model.number="data[{{ $obj->id }}].ta"
                                               class="form-control text-center rounded-3 border-slate-200 focus-primary h-px-45 fw-bold" 
                                               min="0" max="100" placeholder="0-100">
                                    </td>
                                    <td class="px-4 py-4 text-end">
                                        <div class="h5 mb-0 fw-bold text-slate-800" x-text="calculateScore({{ $obj->id }}, {{ $obj->weightage }})">0.00</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Summary & Rating Display -->
                <div class="row g-4 mb-5">
                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-body p-4">
                                <h5 class="fw-bold text-slate-800 mb-4">Rating Interpretation</h5>
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center">
                                        <div class="px-3 py-1 rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 small fw-bold me-3" style="width: 120px; text-align: center;">Outstanding</div>
                                        <span class="text-slate-500 small">Score >= 95 | Excellence & Innovation</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="px-3 py-1 rounded-pill bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 small fw-bold me-3" style="width: 120px; text-align: center;">Very Good</div>
                                        <span class="text-slate-500 small">Score 85 - 94 | Consistency Beyond Expectation</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="px-3 py-1 rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 small fw-bold me-3" style="width: 120px; text-align: center;">Good</div>
                                        <span class="text-slate-500 small">Score 70 - 84 | Meets Full Objectives</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="px-3 py-1 rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 small fw-bold me-3" style="width: 120px; text-align: center;">Below</div>
                                        <span class="text-slate-500 small">Score < 70 | PIP Required</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card border-0 shadow-lg rounded-4 h-100 overflow-hidden bg-white">
                            <div class="p-4 bg-slate-800 text-white text-center">
                                <span class="small opacity-75 text-uppercase fw-bold tracking-wider">Final Year Summary</span>
                                <div class="h1 fw-bold mt-2 mb-0" x-text="totalScore.toFixed(2)">0.00</div>
                            </div>
                            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                                <label class="small text-slate-400 text-uppercase fw-bold mb-2">Assigned Rating</label>
                                <div class="h3 fw-bold mb-4" :class="getRatingClass()" x-text="getRating()">Good</div>
                                <button type="submit" class="btn btn-lg w-100 rounded-pill fw-bold text-white shadow-sm transition-all py-3" style="background-color: #1a6b3b;">
                                    <i class="fas fa-check-double me-2"></i> Submit Final Assessment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-mint-50 { background-color: #f0fdfa; }
    .text-mint-600 { color: #0d9488; }
    .text-mint-800 { color: #115e59; }
    .focus-primary:focus { border-color: #1a6b3b !important; box-shadow: 0 0 0 0.25rem rgba(26, 107, 59, 0.15) !important; }
    .h-px-45 { height: 45px; }
    .tracking-wider { letter-spacing: 0.1em; }
    .transition-all:hover { filter: brightness(1.1); transform: translateY(-2px); }
</style>

<script>
    function finalAppraisal() {
        return {
            data: {},
            totalScore: 0,
            init() {
                // Pre-populate data structure
                @foreach($objectives as $obj)
                    this.data[{{ $obj->id }}] = { ta: 0, weight: {{ $obj->weightage }}, score: 0 };
                @endforeach
            },
            calculateScore(id, weight) {
                const ta = this.data[id].ta || 0;
                const score = (weight * ta / 100);
                this.data[id].score = score;
                this.updateTotal();
                return score.toFixed(2);
            },
            updateTotal() {
                this.totalScore = Object.values(this.data).reduce((acc, curr) => acc + curr.score, 0);
            },
            getRating() {
                if (this.totalScore >= 95) return 'Outstanding';
                if (this.totalScore >= 85) return 'Very Good';
                if (this.totalScore >= 70) return 'Good';
                return 'Below Expectation';
            },
            getRatingClass() {
                if (this.totalScore >= 95) return 'text-success';
                if (this.totalScore >= 85) return 'text-primary';
                if (this.totalScore >= 70) return 'text-info';
                return 'text-danger';
            }
        }
    }
</script>
@endsection
