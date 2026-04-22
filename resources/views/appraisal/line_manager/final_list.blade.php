@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-slate-800 mb-1">Final Year Assessment</h2>
            <p class="text-slate-500 mb-0">Recording marks and performance ratings (FY {{ $activeFY }})</p>
        </div>
        <div class="bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-medium small border border-primary border-opacity-25">
            <i class="fas fa-flag-checkered me-1"></i> Final Evaluation Phase
        </div>
    </div>

    <!-- Employee List Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase">Employee Information</th>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-center">Midterm Status</th>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($employees as $emp)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-primary bg-opacity-10 text-primary fw-bold">
                                    {{ substr($emp->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-slate-800">{{ $emp->name }}</h6>
                                    <span class="small text-slate-500">{{ $emp->designation }} | {{ $emp->employee_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                <i class="fas fa-check-circle me-1"></i> Midterm Completed
                            </span>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('appraisal.final.conduct', $emp->id) }}" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                <i class="fas fa-chart-bar me-1"></i> Conduct Final Marking
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="fas fa-hourglass-start fa-3x text-slate-300"></i>
                            </div>
                            <h6 class="text-slate-400">No employees currently cleared for final evaluation by HR.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
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
    .text-slate-800 { color: #1e293b; }
    .text-slate-600 { color: #475569; }
    .text-slate-500 { color: #64748b; }
    .shadow-sm-hover:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
</style>
@endsection
