@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="managerObjectiveHub()">
    <!-- Header/Navigation -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom">
                <div class="d-flex align-items-center">
                    <a href="{{ route('objectives.approvals') }}" class="btn btn-link text-muted p-0 me-4 shadow-none">
                        <i class="fas fa-arrow-left fa-lg"></i>
                    </a>
                    <div>
                        <h2 class="fw-bold mb-1" style="color: #1a6b3b;">Performance Plan Management</h2>
                        <div class="text-muted small">Managing individual objectives for <span class="fw-bold text-dark">{{ $employee->name }}</span> ({{ $activeFY->label }})</div>
                    </div>
                </div>
                <div>
                    @if($isApproved)
                           <div class="bg-success text-dark border border-success border-opacity-25 rounded-pill px-4 py-2 shadow-sm d-flex align-items-center">
                            <i class="fas fa-lock me-2"></i>
                            <span class="small fw-bold uppercase">Record Verified & Locked</span>
                        </div>
                    @else
                         <div class="badge bg-secondary text-dark px-4 py-2 rounded-pill border">
                            <i class="fas fa-edit me-2"></i> System: Edit Mode
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <!-- Context Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-7">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100 transition-all hover-prime">
                <div class="d-flex align-items-center mb-4">
                         <div class="bg-success text-white rounded-circle me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 52px; height: 52px; font-size: 1.2rem;">
                        {{ substr($employee->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="h5 fw-bold text-dark mb-0">{{ $employee->name }}</div>
                        <div class="small text-muted text-uppercase fw-bold ls-1" style="font-size: 0.65rem;">Member ID: {{ $employee->employee_id }}</div>
                    </div>
                </div>
                <div class="row pt-2 g-3">
                    <div class="col-6">
                        <div class="small text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.6rem;">Current Role</div>
                        <div class="fw-bold text-dark">{{ $employee->designation ?: 'Team Member' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted text-uppercase fw-bold ls-1 mb-1" style="font-size: 0.6rem;">Department</div>
                        <div class="fw-bold text-dark">{{ $employee->department->name ?? 'Direct' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="p-4 bg-white rounded-4 shadow-sm border h-100 transition-all hover-prime">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted fw-bold text-uppercase ls-1" style="font-size: 0.65rem;">Total Calculation</span>
                        <span class="h4 mb-0 fw-bold" :class="currentTotalWeight >= 100 ? 'text-success' : 'text-primary'" x-text="currentTotalWeight + '% / 100%'"></span>
                    </div>
                    <div class="progress" style="height: 10px; border-radius: 10px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success transition-all" :style="'width: ' + Math.min(currentTotalWeight, 100) + '%'"></div>
                    </div>
                </div>
                <div class="bg-light p-3 rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-muted uppercase">Individual Share (Target 70%)</span>
                        <span class="fw-bold text-dark" x-text="totalIndivWeight + '%'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Objectives Spreadsheet -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden border mb-5">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0 excel-theme">
                <thead style="background-color: #f8fbff; color: #1a6b3b;">
                    <tr>
                        <th class="text-center" style="width: 60px;">#</th>
                        <th style="width: 45%;">Strategic Perspective & Key Action Plan</th>
                        <th class="text-center">Timeline</th>
                        <th class="text-center" style="width: 140px;">Weightage %</th>
                        @if(!$isApproved) <th style="width: 50px;"></th> @endif
                    </tr>
                </thead>
                <tbody>
                    <!-- Section A: 30% -->
                    <tr class="bg-light border-bottom">
                        <td colspan="{{ $isApproved ? 4 : 5 }}" class="py-3 px-4 fw-bold" style="color: #475569;">
                            <i class="fas fa-building me-2 text-primary opacity-50"></i> Organization/Departmental Baseline (Fixed 30%)
                        </td>
                    </tr>
                    @foreach($deptObjectives as $index => $dept)
                    <tr class="bg-light opacity-60">
                        <td class="text-center small fw-bold">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 fw-bold text-dark">{{ $dept->master->title }}</td>
                        <td class="px-4 text-center text-muted small italic">{{ $dept->timeline ?: 'Active Cycle' }}</td>
                        <td class="text-center fw-bold">{{ $dept->weightage }}%</td>
                        @if(!$isApproved) <td></td> @endif
                    </tr>
                    @endforeach

                    <!-- Section B: 70% -->
                     <tr class="bg-success border-bottom">
                         <td colspan="{{ $isApproved ? 4 : 5 }}" class="py-3 px-4 fw-bold text-dark">
                            <i class="fas fa-user-check me-2"></i> Individual Performance Objectives (Target 70%)
                        </td>
                    </tr>
                    <template x-for="(row, index) in rows" :key="index">
                        @if($isApproved)
                        <tr class="bg-white">
                            <td class="text-center small fw-bold text-muted" x-text="index + 1 + {{ count($deptObjectives) }}"></td>
                            <td class="px-4 py-3 fw-bold text-dark" x-text="row.description"></td>
                            <td class="text-center text-muted small" x-text="row.target || 'N/A'"></td>
                            <td class="text-center fw-bold text-dark" x-text="row.weightage + '%'"></td>
                        </tr>
                        @else
                        <tr>
                            <td class="text-center fw-bold" x-text="index + 1 + {{ count($deptObjectives) }}"></td>
                            <td class="p-0 position-relative">
                                <input type="text" list="objective-masters-list-mgr" x-model="row.description" class="form-control border-0 shadow-none h-100 py-3 px-4 excel-select" placeholder="Type or select an objective..." required autocomplete="off">
                                <datalist id="objective-masters-list-mgr">
                                    @foreach($masters as $m)
                                        <option value="{{ $m->title }}"></option>
                                    @endforeach
                                </datalist>
                            </td>
                            <td class="p-0 text-center">
                                <input type="text" x-model="row.target" class="form-control border-0 shadow-none h-100 py-3 text-center" placeholder="e.g. Q4">
                            </td>
                            <td class="p-0">
                                <input type="number" x-model.number="row.weightage" class="form-control border-0 shadow-none h-100 py-3 text-center fw-bold" min="1" max="100">
                            </td>
                            <td class="text-center">
                                <button type="button" @click="removeRow(index)" class="btn btn-link text-danger p-0 shadow-none"><i class="fas fa-times-circle"></i></button>
                            </td>
                        </tr>
                        @endif
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- UI Actions -->
    <div class="d-flex justify-content-between align-items-center pb-5">
        <div>
            @if(!$isApproved)
            <button type="button" @click="addRow" class="btn btn-light border px-4 rounded-pill shadow-sm fw-bold hover-prime transition-all">
                <i class="fas fa-plus-circle me-2 text-success"></i> Add Individual Entry
            </button>
            @endif
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('objectives.approvals') }}" class="btn btn-light border px-5 fw-bold rounded-pill shadow-sm">Back to List</a>
            @if(!$isApproved)
                <button type="button" @click="submitPlan('draft')" class="btn btn-outline-success px-5 fw-bold rounded-pill shadow-sm bg-white" :disabled="isSubmitting">
                    <span x-show="!isSubmitting">Save Draft</span>
                    <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                </button>
                <button type="button" @click="showConfirm = true" class="btn btn-success px-5 fw-bold rounded-pill shadow-sm text-white transition-all hover-grow" style="background-color: #1a6b3b;" :disabled="isSubmitting">
                    <span x-show="!isSubmitting"><i class="fas fa-certificate me-2"></i> Finalize & Approve</span>
                    <i class="fas fa-spinner fa-spin" x-show="isSubmitting"></i>
                </button>
            @endif
        </div>
    </div>

    <!-- Premium Approval Modal -->
    <div x-show="showConfirm" 
         class="custom-modal-container" 
         :class="showConfirm ? 'show-now' : ''"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak>
        <div class="custom-modal-overlay" @click="showConfirm = false"></div>
        <div class="custom-modal-content">
             <div class="bg-success text-white rounded-circle mb-4 mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: rgba(26, 107, 59, 0.1);">
                <i class="fas fa-shield-check fa-2x"></i>
            </div>
            <h3 class="fw-bold text-dark mb-3">Authorize & Lock Record?</h3>
            <p class="text-muted mb-5 px-4">You are about to verify this performance plan. Once approved, the record will be <strong>permanently locked</strong> for this cycle. Only HR Admins can reverse this action.</p>
            <div class="d-flex flex-column gap-3">
                <button @click="submitPlan('set')" class="btn btn-success py-3 rounded-3 fw-bold text-white w-100 shadow-sm" style="background-color: #1a6b3b;">Confirm & Lock Record</button>
                <button @click="showConfirm = false" class="btn btn-light py-3 rounded-3 fw-bold text-muted w-100">Cancel & Return</button>
            </div>
        </div>
    </div>
</div>

<style>
    .ls-1 { letter-spacing: 0.05em; }
    .excel-theme thead th { border-bottom: 2px solid #1a6b3b !important; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 18px; }
    .excel-select:focus { background-color: #f8fbff; outline: none; }
    [x-cloak] { display: none !important; }
    .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .hover-prime:hover { border-color: #1a6b3b !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; }
    .hover-grow:hover { transform: scale(1.02); }

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
        max-width: 480px;
        padding: 48px;
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        text-align: center;
        z-index: 10000;
    }
</style>

<script>
function managerObjectiveHub() {
    return {
        rows: @json($individualObjectives->map(fn($o) => ['description' => $o->description, 'target' => $o->target, 'weightage' => $o->weightage])),
        isApproved: @json($isApproved),
        isSubmitting: false,
        showConfirm: false,

        init() {
            if (this.rows.length === 0 && !this.isApproved) {
                this.rows.push({ description: '', target: '', weightage: 10 });
            }
        },

        get totalIndivWeight() {
            return this.rows.reduce((sum, row) => sum + (parseInt(row.weightage) || 0), 0);
        },

        get currentTotalWeight() {
            return 30 + this.totalIndivWeight;
        },

        addRow() {
            this.rows.push({ description: '', target: '', weightage: 10 });
        },

        removeRow(index) {
            if (this.rows.length > 1) this.rows.splice(index, 1);
        },

        async submitPlan(targetStatus) {
            this.isSubmitting = true;
            this.showConfirm = false;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('status', targetStatus);
            
            this.rows.forEach((row, index) => {
                formData.append(`objectives[${index}][type]`, 'individual');
                formData.append(`objectives[${index}][description]`, row.description);
                formData.append(`objectives[${index}][target]`, row.target || '');
                formData.append(`objectives[${index}][weightage]`, row.weightage);
                formData.append(`objectives[${index}][financial_year]`, '{{ $activeFY->label }}');
            });

            try {
                const r = await fetch('{{ route('objectives.set_for_user', $employee->id) }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await r.json();
                if (r.ok) {
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Submission failed'));
                }
            } catch (e) {
                alert('System error occurred during save.');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endsection
