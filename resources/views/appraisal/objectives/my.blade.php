@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="objectiveSelfService()">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between pb-3">
                <div>
                    <h2 class="fw-bold mb-1" style="color: #1a6b3b;">My Performance Objectives: {{ $activeFy->label }}</h2>
                    <div class="text-muted small">Fulfill your 70% individual targets to complete your appraisal profile</div>
                </div>
            </div>
        </div>
    </div>

    @include('components.alert')

    <form method="POST" action="{{ route('objectives.submit') }}" @submit.prevent="submitForm">
        @csrf
        <!-- Employee Context (Two Column) -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Employee Name</label>
                        <div class="h5 fw-bold text-dark mb-0">{{ $user->name }}</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Designation / ID</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->designation ?: 'Staff' }} ({{ $user->employee_id }})</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted text-uppercase">Department / Team</label>
                        <div class="h6 fw-bold text-dark mb-0">{{ $user->department->name ?? 'N/A' }} {{ $user->team ? '('.$user->team->name.')' : '' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-4 bg-white rounded shadow-sm h-100 border">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted text-uppercase">Cycle Progress</label>
                        <div class="progress mb-2" style="height: 10px; border-radius: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" :style="'width: ' + currentTotalWeight + '%'" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="d-flex justify-content-between small fw-bold">
                            <span class="text-muted">Total Weightage</span>
                            <span :class="currentTotalWeight === 100 ? 'text-success' : 'text-danger'" x-text="currentTotalWeight + '% / 100%'"></span>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded border text-center">
                        <span class="text-muted smaller fw-bold ls-1 me-3 uppercase">Status</span>
                        <span class="badge" :class="currentTotalWeight === 100 ? 'bg-success' : 'bg-warning'" x-text="currentTotalWeight === 100 ? 'Ready to Deploy' : 'Pending Individual Targets'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Table: Combined View -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5 border">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 excel-table">
                    <thead style="background-color: #f8fbff; color: #1a6b3b;">
                        <tr>
                            <th style="width: 60px;" class="text-center">SL</th>
                            <th style="width: 35%;">Objectives / KPI / Action Plans</th>
                            <th style="width: 25%;">Certifying Authority / Department</th>
                            <th>Timeline</th>
                            <th style="width: 150px;" class="text-center">Weightage %</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Section 1: Departmental Targets (Read Only - 30%) -->
                        <tr class="bg-light">
                            <td colspan="6" class="py-3 px-4 fw-bold" style="color: #1a6b3b;">
                                <i class="fas fa-building me-2"></i> System Assigned Departmental Targets (Fixed 30%)
                            </td>
                        </tr>
                        @foreach($deptObjectives as $index => $dept)
                         <tr class="bg-light text-muted opacity-75">
                            <td class="text-center small fw-bold">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 h6 mb-0">{{ $dept->master->title }}</td>
                            <td class="px-4 py-3 small fw-bold">{{ $dept->certifyingAuthorityUser->name ?? ($dept->department->name ?? 'SYSTEM') }}</td>
                            <td class="px-4 small italic">{{ $dept->timeline ?: 'N/A' }}</td>
                            <td class="text-center fw-bold">{{ $dept->weightage }}%</td>
                            <td></td>
                        </tr>
                        @endforeach

                        <!-- Section 2: Individual Targets (Editable - 70%) -->
                        <tr class="table-group-header">
                            <td colspan="6" class="py-3 px-4 fw-bold" style="background-color: #f0f7f3; color: #1a6b3b;">
                                <i class="fas fa-user-edit me-2"></i> My Individual Targets (Fulfill 70%)
                            </td>
                        </tr>
                        <template x-for="(row, index) in rows" :key="index">
                            @if($isApproved)
                            <tr class="bg-white">
                                <td class="text-center fw-bold align-middle text-muted" x-text="index + 1"></td>
                                <td class="px-4 py-3 fw-bold text-dark" colspan="2" x-text="row.description"></td>
                                <td class="px-4 py-3 text-dark text-center fw-bold" x-text="row.target || 'N/A'"></td>
                                <td class="text-center fw-bold text-dark align-middle" x-text="row.weightage + '%'"></td>
                                <td class="text-center align-middle"></td>
                            </tr>
                            @else
                            <tr>
                                <td class="text-center fw-bold align-middle" x-text="index + 1"></td>
                                <td class="p-0 position-relative" colspan="2">
                                    <input type="text" list="objective-masters-list" x-model="row.description" class="form-control border-0 shadow-none h-100 excel-input" placeholder="Type or select an objective..." required autocomplete="off">
                                    <datalist id="objective-masters-list">
                                        @foreach($masters as $m)
                                            <option value="{{ $m->title }}"></option>
                                        @endforeach
                                    </datalist>
                                </td>
                                <td class="p-0">
                                    <input type="text" x-model="row.target" class="form-control border-0 shadow-none h-100 excel-input" placeholder="e.g. Q3 2026">
                                </td>
                                <td class="p-0">
                                    <input type="number" x-model.number="row.weightage" class="form-control border-0 shadow-none h-100 excel-input text-center fw-bold" min="1" max="70">
                                </td>
                                <td class="text-center align-middle">
                                    <button type="button" @click="removeRow(index)" class="btn btn-link text-danger p-0 shadow-none" :disabled="rows.length <= 1">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            @endif
                        </template>
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <td colspan="4" class="text-end fw-bold py-3 text-uppercase small text-muted">Individual Target Subtotal</td>
                            <td class="text-center py-3">
                                <div class="h5 mb-0 fw-bold" :class="totalIndivWeight === 70 ? 'text-success' : 'text-danger'" x-text="totalIndivWeight + '%'"></div>
                            </td>
                            <td></td>
                        </tr>
                        <tr style="background-color: #1a6b3b; color: white;">
                            <td colspan="4" class="text-end fw-bold py-3 text-uppercase small">Grand Total Weightage (Goal: 100%)</td>
                            <td class="text-center py-3">
                                <div class="h4 mb-0 fw-bold" x-text="currentTotalWeight + '%'"></div>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Submission Section -->
        <div class="d-flex justify-content-between align-items-center pb-5">
            <div>
                @if(!$isApproved)
                <button type="button" @click="addRow" class="btn btn-light border fw-bold px-4 shadow-sm" :disabled="totalIndivWeight >= 70">
                    <i class="fas fa-plus me-2 text-success"></i> Add Objective
                </button>
                @endif
            </div>
            <div class="d-flex gap-3">
                <a href="{{ route('dashboard') }}" class="btn btn-light border px-5 fw-bold">Back to Dashboard</a>
                @if(!$isApproved)
                <button type="submit" class="btn btn-dark px-5 fw-bold shadow-sm" :disabled="totalIndivWeight !== 70">
                    <i class="fas fa-cloud-upload-alt me-2"></i> Submit Plan for Approval
                </button>
                @endif
            </div>
        </div>
    </form>
</div>

<style>
    .ls-1 { letter-spacing: 0.05em; }
    .smaller { font-size: 0.75rem; }
    .excel-input { border-radius: 0; padding: 15px 20px; font-size: 0.95rem; }
    .excel-input:focus { background-color: #f8fbff; outline: 3px solid rgba(26, 107, 59, 0.15); z-index: 10; }
    .excel-table td { vertical-align: middle; }
    .excel-table thead th { border-bottom: 2px solid #1a6b3b !important; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; }
    .italic { font-style: italic; }
</style>

<script>
@php
    $initialRows = $individualObjectives->count() > 0 
        ? $individualObjectives->map(function($o) {
            return ['description' => strtoupper(trim($o->description)), 'target' => $o->timeline ?? $o->target, 'weightage' => $o->weightage];
          }) 
        : [['description' => '', 'target' => '', 'weightage' => 10]];
@endphp

function objectiveSelfService() {
    return {
        rows: @json($initialRows),
        
        deptTotal: 30, // Fixed based on your business rule

        get totalIndivWeight() {
            return this.rows.reduce((sum, row) => sum + (parseInt(row.weightage) || 0), 0);
        },

        get currentTotalWeight() {
            return this.deptTotal + this.totalIndivWeight;
        },

        addRow() {
            if (this.totalIndivWeight < 70) {
                this.rows.push({ description: '', target: '', weightage: 10 });
            }
        },

        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },

        submitForm() {
            if (this.totalIndivWeight !== 70) {
                alert('Your individual targets must total exactly 70%');
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            
            this.rows.forEach((row, index) => {
                formData.append(`objectives[${index}][type]`, 'individual');
                formData.append(`objectives[${index}][description]`, row.description);
                formData.append(`objectives[${index}][target]`, row.target || '');
                formData.append(`objectives[${index}][weightage]`, row.weightage);
                formData.append(`objectives[${index}][financial_year]`, '{{ $activeFy->label }}');
            });

            fetch('{{ route('objectives.submit') }}', {
                method: 'POST',
                body: formData,
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }).then(async r => {
                if (r.ok) {
                    window.location.href = "{{ route('dashboard') }}";
                } else {
                    const data = await r.json();
                    let errorMsg = 'Submission failed:\n';
                    if (data.errors) {
                        Object.keys(data.errors).forEach(key => {
                            errorMsg += `- ${data.errors[key][0]}\n`;
                        });
                    } else {
                        errorMsg += data.message || 'Unknown error';
                    }
                    alert(errorMsg);
                    console.error('Validation errors:', data.errors);
                }
            });
        }
    }
}
</script>
@endsection
