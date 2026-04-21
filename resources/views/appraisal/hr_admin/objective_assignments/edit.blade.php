@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4" x-data="objectiveSetting()">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                    <h2 class="fw-bold" style="color: #4b6cb7;">Edit Objectives: {{ $department->name }}</h2>
                    <div class="text-muted small">Modifying {{ $activeFy->label }} Targets</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('departmental-objective-assignments.update', $department->id) }}" @submit.prevent="submitForm">
            @csrf
            @method('PUT')
            @include('components.alert')

            <!-- Two Column Layout -->
            <div class="row g-4 mb-5">
                <!-- Left Column: Selectors (Read-only Context) -->
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded shadow-sm h-100 border">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Department</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" value="{{ $department->name }}" readonly>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Target Scope</label>
                            @php
                                $firstAssignment = $assignments->first();
                                $currentTeamId = $firstAssignment?->team_id;
                                $currentTeamName = $currentTeamId ? ($teams->firstWhere('id', $currentTeamId)->name ?? 'Unknown Team') : 'Entire Department';
                            @endphp
                            <input type="text" class="form-control form-control-lg bg-light border-0" value="{{ $currentTeamName }}" readonly>
                            <input type="hidden" x-model="teamId" value="{{ $currentTeamId }}">
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Last Modified</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" value="{{ $firstAssignment?->updated_at->format('d F Y') ?? 'N/A' }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Employee List -->
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded shadow-sm h-100 border">
                        <label class="form-label fw-semibold mb-3">Employees impacted by this update</label>
                        <div class="employee-list-scroll" style="height: 250px; overflow-y: auto;">
                            <div class="list-group list-group-flush border-top">
                                @foreach($deptEmployees as $employee)
                                    @if(!$currentTeamId || $employee->team_id == $currentTeamId)
                                        <div class="list-group-item d-flex align-items-center gap-3 py-2 px-0 border-bottom-0">
                                            <div class="bg-light rounded-circle p-2 text-primary" style="width: 35px; height: 35px; text-align: center;">
                                                <i class="fas fa-user small"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $employee->name }}</div>
                                                <div class="smaller text-muted">{{ $employee->role ?? 'Staff' }}</div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Table: Excel Design -->
            <div class="bg-white rounded shadow-sm border overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 excel-table">
                        <thead style="background-color: #f4f7fa; color: #4b6cb7;">
                            <tr>
                                <th style="width: 60px;" class="text-center">SL</th>
                                <th style="width: 40%;">Objectives / KPI / Action Plans</th>
                                <th>Timeline</th>
                                <th style="width: 150px;">Weightage %</th>
                                <th>Certifying Authority / Department</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td class="text-center align-middle fw-bold" x-text="index + 1"></td>
                                    <td class="p-0">
                                        <select x-model="row.objective_master_id" class="form-select border-0 shadow-none h-100 excel-input" required>
                                            <option value="">-- Choose Objective --</option>
                                            @foreach($masters as $m)
                                                <option value="{{ $m->id }}">{{ $m->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-0">
                                        <input type="text" x-model="row.timeline" class="form-control border-0 shadow-none h-100 excel-input">
                                    </td>
                                    <td class="p-0">
                                        <input type="number" x-model.number="row.weightage" class="form-control border-0 shadow-none h-100 excel-input text-center fw-bold">
                                    </td>
                                    <td class="p-0">
                                        <select x-model="row.certifying_authority_role" class="form-select border-0 shadow-none h-100 excel-input">
                                            <option value="line_manager">Immediate Line Manager</option>
                                            <option value="dept_head">Department Head</option>
                                            <option value="hr_manager">HR Manager</option>
                                        </select>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button type="button" @click="removeRow(index)" class="btn btn-link text-danger p-0 shadow-none" :disabled="rows.length <= 1">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="3" class="text-end fw-bold py-3 text-uppercase small">Deployment Total Weight</td>
                                <td class="text-center py-3">
                                    <div class="h5 mb-0 fw-bold" :class="totalWeight === 30 ? 'text-success' : 'text-danger'" x-text="totalWeight + '%'"></div>
                                </td>
                                <td colspan="2" class="py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4 pb-5">
                <button type="button" @click="addRow" class="btn btn-light border fw-bold px-4 shadow-sm">
                    <i class="fas fa-plus me-2 text-primary"></i> Add Row
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('departmental-objective-assignments.index') }}" class="btn btn-light border px-5 fw-bold">Discard Changes</a>
                    <button type="submit" class="btn btn-dark px-5 fw-bold shadow-sm" :disabled="totalWeight !== 30">
                        <i class="fas fa-save me-2"></i> Update Deployment
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .excel-input { border-radius: 0; padding: 12px 15px; font-size: 0.95rem; }
        .excel-input:focus { background-color: #f8fbff; outline: 3px solid rgba(75, 108, 183, 0.2); z-index: 10; }
        .excel-table td { vertical-align: middle; padding: 0; }
        .excel-table thead th { padding: 12px 15px; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        .employee-list-scroll::-webkit-scrollbar { width: 5px; }
        .employee-list-scroll::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 10px; }
        .smaller { font-size: 0.7rem; }
    </style>

    <script>
        @php
            $initialRows = $assignments->map(function($a) {
                return [
                    'objective_master_id' => $a->objective_master_id,
                    'timeline' => $a->timeline,
                    'weightage' => $a->weightage,
                    'certifying_authority_role' => $a->certifying_authority_role,
                ];
            });
        @endphp

        function objectiveSetting() {
            return {
                teamId: '{{ $currentTeamId }}',
                rows: @json($initialRows),

                get totalWeight() {
                    return this.rows.reduce((sum, row) => sum + (parseInt(row.weightage) || 0), 0);
                },

                addRow() {
                    this.rows.push({ objective_master_id: '', timeline: '', weightage: 0, certifying_authority_role: 'line_manager' });
                },

                removeRow(index) {
                    if (this.rows.length > 1) {
                        this.rows.splice(index, 1);
                    }
                },

                submitForm() {
                    if (this.totalWeight !== 30) {
                        alert('Total weightage must be exactly 30%');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('_method', 'PUT');
                    formData.append('team_id', this.teamId || '');
                    
                    this.rows.forEach((row, index) => {
                        formData.append(`rows[${index}][objective_master_id]`, row.objective_master_id);
                        formData.append(`rows[${index}][timeline]`, row.timeline);
                        formData.append(`rows[${index}][weightage]`, row.weightage);
                        formData.append(`rows[${index}][certifying_authority_role]`, row.certifying_authority_role);
                    });

                    fetch('{{ route('departmental-objective-assignments.update', $department->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => {
                        if (r.ok) window.location.href = '{{ route('departmental-objective-assignments.index') }}';
                        else alert('Update failed. Ensure constraints are met.');
                    });
                }
            }
        }
    </script>
@endsection
