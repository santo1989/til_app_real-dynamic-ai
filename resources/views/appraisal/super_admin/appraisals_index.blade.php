@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="hrAppraisalHub()">
    <!-- Header -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #1e293b;">Strategic Appraisal Lifecycle</h2>
                    <p class="text-muted small mb-0">Central oversight for Cycle: <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3">{{ $activeFY ?: 'No Active FY' }}</span></p>
                </div>
                <div class="d-flex gap-3">
                    <div class="bg-white border rounded-pill px-4 py-2 shadow-sm d-flex align-items-center">
                        <span class="status-indicator-pulse me-2"></span>
                        <span class="small fw-bold text-success">HR Administration Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <!-- 1. Midterm Appraisal Oversight -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border mb-5">
        <div class="card-header bg-white py-4 px-4 border-0 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold mb-0 text-dark opacity-75">
                <i class="fas fa-hourglass-half me-2 text-warning"></i> Midterm Progress Oversight
            </h5>
            <div class="d-flex align-items-center gap-3">
                @if(count($midtermList) > 0)
                    <button type="button" @click="confirmAll()" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover border-success text-success bg-white">
                        <i class="fas fa-layer-group me-1"></i> Send All for Note
                    </button>
                @endif

                @if($isMidtermWindow)
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning border-opacity-25 small">
                        <i class="fas fa-calendar-check me-1"></i> Midterm Window Open
                    </span>
                @else
                    <span class="badge bg-light text-muted px-3 py-2 rounded-pill border small">
                        <i class="fas fa-lock me-1"></i> Midterm Window Starts: {{ $midtermThreshold->format('M d, Y') }}
                    </span>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fbff; color: #1e293b;">
                    <tr>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Member Info</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Department</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-center">Lifecycle Status</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-end">Operations</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($midtermList as $item)
                        <tr class="transition-hover">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success bg-opacity-10 text-success rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 0.9rem;">
                                        {{ substr($item['user']->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $item['user']->name }}</div>
                                        <div class="text-muted smaller">ID: {{ $item['user']->employee_id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="small fw-medium text-slate-600">{{ $item['user']->department->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item['status'] === 'midterm_triggered')
                                    <span class="badge rounded-pill px-3 py-2 bg-info bg-opacity-10 text-info border border-info border-opacity-25">
                                        <i class="fas fa-paper-plane me-1"></i> Triggered: Awaiting Note
                                    </span>
                                @elseif($item['status'] === 'in_progress')
                                    <span class="badge rounded-pill px-3 py-2 bg-primary bg-opacity-10 text-white border border-primary border-opacity-25">
                                        <i class="fas fa-pen-nib me-1"></i> Manager Recording Progress
                                    </span>
                                @elseif($item['status'] === 'eligible' || $item['status'] === 'pending_hr')
                                    <span class="badge rounded-pill px-3 py-2 bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                        <i class="fas fa-clock me-1"></i> Pending HR Trigger
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-3 py-2 bg-secondary bg-opacity-10 text-secondary border">
                                        {{ ucfirst(str_replace('_', ' ', $item['status'])) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                @if($item['status'] === 'eligible' || $item['status'] === 'pending_hr' || $item['status'] === 'draft')
                                    <button type="button" @click="confirmSingle({{ $item['user']->id }}, '{{ $item['user']->name }}')" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                        <i class="fas fa-bolt-lightning me-1"></i> Send for Note
                                    </button>
                                @else
                                    <button class="btn btn-sm px-4 rounded-pill fw-bold border bg-light text-muted" disabled>
                                        <i class="fas fa-check me-1"></i> {{ $item['status'] === 'in_progress' ? 'Active' : 'Sent' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                                    <p class="mb-0 fw-bold text-muted">No employees eligible for midterm trigger at this time.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Final Year Assessment Readiness -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border">
        <div class="card-header bg-white py-4 px-4 border-0">
            <h5 class="fw-bold mb-0 text-dark opacity-75">
                <i class="fas fa-check-double me-2 text-primary"></i> Final Year Assessment Readiness
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: #f8fbff; color: #1e293b;">
                    <tr>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Member Info</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1">Performance Track</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-center">Lifecycle State</th>
                        <th class="px-4 py-3 text-uppercase smaller fw-bold ls-1 text-end">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($finalYearList as $item)
                        <tr class="transition-hover">
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; font-size: 0.9rem;">
                                        {{ substr($item['user']->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ $item['user']->name }}</div>
                                        <div class="text-muted smaller">{{ $item['user']->designation ?: 'Staff' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-3" style="height: 6px;">
                                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                                    </div>
                                    <span class="smaller fw-bold text-primary">Midterm OK</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $appraisal = $item['user']->appraisals()
                                        ->where('type', 'midterm')
                                        ->where('financial_year', $activeFY)
                                        ->first();
                                    $status = $appraisal ? $appraisal->status : 'unknown';
                                @endphp

                                @if($status === 'ready_for_final')
                                    <span class="badge rounded-pill px-3 py-2 bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                        <i class="fas fa-check-double me-1"></i> Ready for Final
                                    </span>
                                @elseif($status === 'final_completed')
                                    <span class="badge rounded-pill px-3 py-2 bg-slate-100 text-slate-700 border border-slate-200">
                                        <i class="fas fa-lock me-1"></i> Assessment Done
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-3 py-2 bg-blue-100 text-blue-700 border border-blue-200">
                                        <i class="fas fa-check me-1"></i> Midterm OK
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                @if($status === 'midterm_completed')
                                    <form action="{{ route('appraisals.trigger_final', $appraisal->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm px-4 rounded-pill fw-bold transition-all shadow-sm-hover text-white" style="background-color: #1a6b3b;">
                                            <i class="fas fa-bolt-lightning me-1"></i> Send for Final Marking
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm px-4 rounded-pill fw-bold border bg-light text-muted" disabled>
                                        <i class="fas fa-clock me-1"></i> {{ $status === 'ready_for_final' ? 'With Manager' : 'Completed' }}
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="opacity-50">
                                    <i class="fas fa-layer-group fa-3x mb-3 text-muted"></i>
                                    <p class="mb-0 fw-bold text-muted">No employees are currently ready for the final year assessment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden Bulk Form -->
    <form id="bulkTriggerForm" action="{{ route('appraisals.trigger_all_midterms') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Hidden Single Form -->
    <form id="singleTriggerForm" action="" method="POST" style="display: none;">
        @csrf
    </form>

    <!-- Premium Modal Container -->
    <div x-show="showModal" 
         class="custom-modal-container" 
         :class="showModal ? 'show-now' : ''"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        <div class="custom-modal-overlay" @click="showModal = false"></div>
        <div class="custom-modal-content">
            <div class="bg-success-soft text-success rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(26, 107, 59, 0.1);">
                <i class="fas fa-paper-plane fa-2x"></i>
            </div>
            <h3 class="fw-bold text-dark mb-3" x-text="modalTitle">Request Midterm Notes?</h3>
            <p class="text-muted mb-5 px-4" x-text="modalDesc">This will notify the Line Manager to provide midterm progress reviews for the selected employee(s).</p>
            <div class="d-flex flex-column gap-3">
                <button @click="submitAction()" class="btn btn-success py-3 rounded-3 fw-bold text-white w-100 shadow-sm" style="background-color: #1a6b3b;">Initiate Request Now</button>
                <button @click="showModal = false" class="btn btn-light py-3 rounded-3 fw-bold text-muted w-100">Cancel & Go Back</button>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.7rem; }
    .text-slate-600 { color: #475569; }
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

    /* Modal Priority Styling */
    .custom-modal-container {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .custom-modal-container.show-now {
        display: flex !important;
    }
    .custom-modal-overlay {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
    }
    .custom-modal-content {
        position: relative;
        background: white;
        width: 100%;
        max-width: 440px;
        padding: 40px;
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        text-align: center;
        z-index: 10000;
    }
</style>

<script>
function hrAppraisalHub() {
    return {
        showModal: false,
        modalTitle: '',
        modalDesc: '',
        currentAction: '',
        currentId: null,

        confirmSingle(userId, userName) {
            this.currentId = userId;
            this.currentAction = 'single';
            this.modalTitle = 'Initiate Review for ' + userName + '?';
            this.modalDesc = 'This will officially notify the Line Manager to record midterm progress notes for this specific member.';
            this.showModal = true;
        },

        confirmAll() {
            this.currentAction = 'bulk';
            this.modalTitle = 'Initiate ALL Midterm Reviews?';
            this.modalDesc = 'You are about to trigger midterm review requests for all eligible active staff members in the organization.';
            this.showModal = true;
        },

        submitAction() {
            if (this.currentAction === 'bulk') {
                document.getElementById('bulkTriggerForm').submit();
            } else if (this.currentAction === 'single') {
                const form = document.getElementById('singleTriggerForm');
                form.action = '/appraisals/trigger-midterm/' + this.currentId;
                form.submit();
            }
        }
    }
}
</script>
@endsection
