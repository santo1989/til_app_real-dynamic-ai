@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">Final Year Assessment</h2>
            <p class="text-muted mb-0">Recording marks and performance ratings (FY {{ $activeFY }})</p>
        </div>
         <div class="bg-primary text-white px-3 py-2 rounded-pill fw-medium small border border-primary border-opacity-25">
            <i class="fas fa-flag-checkered me-1"></i> Final Evaluation Phase
        </div>
    </div>

    <!-- Employee List Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase">Employee Information</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-center">Final Status</th>
                        <th class="px-4 py-3 text-secondary small fw-bold text-uppercase text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($employees as $emp)
                    @php
                        $appraisal = $emp->appraisals->first();
                        $isCompleted = ($appraisal?->status ?? null) === \App\Models\Appraisal::STATUS_FINAL_COMPLETED;
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
                            @if($isCompleted)
                                <span class="badge rounded-pill px-3 py-2 bg-success text-white border border-success border-opacity-25">
                                    <i class="fas fa-check-circle me-1"></i> Completed
                                </span>
                            @else
                                <span class="badge rounded-pill px-3 py-2 bg-primary text-white border border-primary border-opacity-25">
                                    <i class="fas fa-hourglass-half me-1"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <a href="{{ route('appraisal.final.conduct', $emp->id) }}" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                <i class="fas fa-eye me-1"></i> {{ $isCompleted ? 'View' : 'Conduct Final Marking' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5">
                            <div class="opacity-50 mb-3">
                                <i class="fas fa-hourglass-start fa-3x text-secondary"></i>
                            </div>
                            <h6 class="text-muted">No employees currently cleared for final evaluation by HR.</h6>
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
