@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Midterm Oversight</h2>
            <p class="text-muted mb-0">Record progress notes for department staff (FY {{ $activeFY }})</p>
        </div>
          <div class="bg-primary text-white px-3 py-2 rounded-pill fw-medium small border border-primary">
            <i class="fas fa-calendar-check me-1"></i> Midterm Window Active
        </div>
    </div>

    <!-- Dept stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
            <div class="text-dark small fw-bold text-uppercase mb-1">Eligible Staff</div>
            <div class="h3 fw-bold text-dark mb-0">{{ $employees->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Employee List Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Employee Information</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-center">Status</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-end">Action</th>
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
                                 <div class="avatar-circle me-3 bg-primary text-white fw-bold">
                                    {{ substr($emp->name, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $emp->name }}</h6>
                                    <span class="small text-muted">{{ $emp->designation }} | {{ $emp->employee_id }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($appraisal->status === \App\Models\Appraisal::STATUS_MIDTERM_COMPLETED)
                                  <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-white border border-success border-opacity-25">
                                    <i class="fas fa-check-circle me-1"></i> Completed
                                </span>
                            @elseif($appraisal->status === 'in_progress')
                                  <span class="badge rounded-pill px-3 py-2 bg-primary bg-opacity-10 text-white border border-primary border-opacity-25">
                                    <i class="fas fa-pen-nib me-1"></i> Notes in Progress
                                </span>
                            @else
                                  <span class="badge rounded-pill px-3 py-2 bg-info bg-opacity-10 text-dark border border-info border-opacity-25">
                                    <i class="fas fa-bolt-lightning me-1"></i> Ready for Review
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            @if($appraisal->status === \App\Models\Appraisal::STATUS_MIDTERM_COMPLETED)
                                <a href="{{ route('appraisal.midterm.conduct', $emp->id) }}" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover btn-outline-secondary">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            @else
                                <a href="{{ route('appraisal.midterm.conduct', $emp->id) }}" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                    <i class="fas fa-edit me-1"></i> {{ $appraisal->status === 'in_progress' ? 'Continue' : 'Give Comment' }}
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="fas fa-clipboard-list fa-3x text-secondary"></i>
                            </div>
                            <h6 class="text-muted">No employees currently pending midterm reviews.</h6>
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
    .text-dark { color: #212529; }
    .text-secondary { color: #6c757d; }
</style>
@endsection
