@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-slate-800 mb-1">Midterm Oversight</h2>
            <p class="text-slate-500 mb-0">Record progress notes for department staff (FY {{ $activeFY }})</p>
        </div>
        <div class="bg-mint-100 text-mint-700 px-3 py-2 rounded-pill fw-medium small border border-mint-200">
            <i class="fas fa-calendar-check me-1"></i> Midterm Window Active
        </div>
    </div>

    <!-- Dept stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <div class="text-mint-600 small fw-bold text-uppercase mb-1">Eligible Staff</div>
                <div class="h3 fw-bold text-mint-900 mb-0">{{ $employees->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Employee List Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase">Employee Information</th>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-center">Status</th>
                        <th class="px-4 py-3 text-slate-600 small fw-bold text-uppercase text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($employees as $emp)
                    @php
                        $appraisal = $emp->appraisals()->where('type', 'midterm')->where('financial_year', $activeFY)->first();
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3 bg-mint-100 text-mint-700 fw-bold">
                                    {{ substr($emp->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-slate-800">{{ $emp->name }}</h6>
                                    <span class="small text-slate-500">{{ $emp->designation }} | {{ $emp->employee_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($appraisal->status === 'in_progress')
                                <span class="badge rounded-pill px-3 py-2 bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25">
                                    <i class="fas fa-pen-nib me-1"></i> Notes in Progress
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-2 bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                    <i class="fas fa-bolt-lightning me-1"></i> Ready for Review
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('appraisal.midterm.conduct', $emp->id) }}" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                <i class="fas fa-edit me-1"></i> {{ $appraisal->status === 'in_progress' ? 'Continue' : 'Give Comment' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="fas fa-clipboard-list fa-3x text-slate-300"></i>
                            </div>
                            <h6 class="text-slate-400">No employees currently pending midterm reviews.</h6>
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
    .bg-mint-100 { background-color: #ecfdf5; }
    .text-mint-700 { color: #047857; }
    .border-mint-200 { border-color: #a7f3d0; }
    .shadow-sm-hover:hover { transform: translateY(-1px); box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
</style>
@endsection
