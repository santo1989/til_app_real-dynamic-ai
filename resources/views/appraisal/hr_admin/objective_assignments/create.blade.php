@extends('layouts.app')

@section('content')
    <div class="container-fluid py-4" x-data="objectiveSetting()">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
                    <h2 class="fw-bold" style="color: #1a6b3b;">Objective Setting: {{ $activeFy->label }}</h2>
                    <div class="text-muted small">Managing Performance Targets</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('departmental-objective-assignments.store') }}" @submit.prevent="submitForm">
            @csrf
            @include('components.alert')

            <!-- Two Column Layout -->
            <div class="row g-4 mb-5">
                <!-- Left Column: Selectors -->
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded shadow-sm h-100 border">
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Department</label>
                            <select x-model="deptId" @change="fetchData" class="form-select form-select-lg border-2 shadow-none" style="border-color: #e9f5ee;" required>
                                <option value="">-- Choose Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Select Team (Optional)</label>
                            <select x-model="teamId" @change="filterEmployees" class="form-select form-select-lg border-2 shadow-none" style="border-color: #e9f5ee;" :disabled="!deptId">
                                <option value="">-- Entire Department --</option>
                                <template x-for="team in teams" :key="team.id">
                                    <option :value="team.id" x-text="team.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold">Date of Creating</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" value="{{ now()->format('d F Y') }}" readonly>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Employee List -->
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded shadow-sm h-100 border">
                        <label class="form-label fw-semibold mb-3 d-flex justify-content-between">
                            Employees in Scope 
                            <span class="badge bg-success" x-text="filteredEmployees.length"></span>
                        </label>
                        <div class="employee-list-scroll" style="height: 250px; overflow-y: auto;">
                            <template x-if="filteredEmployees.length === 0">
                                <div class="text-center py-5 text-muted small italic">
                                    <i class="fas fa-users mb-2 d-block fa-2x opacity-25"></i>
                                    Select a department to view employees
                                </div>
                            </template>
                            <div class="list-group list-group-flush border-top">
                                <template x-for="employee in filteredEmployees" :key="employee.id">
                                    <div class="list-group-item d-flex align-items-center gap-3 py-2 px-0 border-bottom-0">
                                        <div class="bg-light rounded-circle p-2 text-primary" style="width: 35px; height: 35px; text-align: center;">
                                            <i class="fas fa-user small"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold small" x-text="employee.name"></div>
                                            <div class="smaller text-muted" x-text="employee.role"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Table: Excel Design -->
            <div class="bg-white rounded shadow-sm border overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 excel-table">
                        <thead style="background-color: #e9f5ee; color: #1a6b3b;">
                            <tr>
                                <th style="width: 60px;" class="text-center">SL</th>
                                <th style="width: 40%;">Objectives / Key Performance Indicator / Action Plans</th>
                                <th>Timeline</th>
                                <th style="width: 150px;">Weightage %</th>
                                <th>Certifying Authority / Department</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td class="text-center fw-bold" x-text="index + 1"></td>
                                    <td class="p-0">
                                        <select x-model="row.objective_master_id" class="form-select border-0 shadow-none h-100 excel-input" required>
                                            <option value="">-- Find & Select Objective --</option>
                                            @foreach($masters as $m)
                                                <option value="{{ $m->id }}">{{ $m->title }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-0">
                                        <input type="text" x-model="row.timeline" class="form-control border-0 shadow-none h-100 excel-input" placeholder="Enter Timeline...">
                                    </td>
                                    <td class="p-0">
                                        <input type="number" x-model.number="row.weightage" class="form-control border-0 shadow-none h-100 excel-input text-center fw-bold" placeholder="0">
                                    </td>
                                    <td class="p-0">
                                        <select x-model="row.certifying_authority_user_id" class="form-select border-0 shadow-none h-100 excel-input" required>
                                            <option value="">-- Select Authority --</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}">
                                                    {{ $u->name }} ({{ $u->department->name ?? 'No Dept' }} - {{ $u->role }})
                                                </option>
                                            @endforeach
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
                                <td colspan="3" class="text-end fw-bold py-3 text-uppercase small" style="letter-spacing: 0.1em;">Total Weightage Accumulation</td>
                                <td class="text-center py-3">
                                    <div class="h5 mb-0 fw-bold" :class="totalWeight === 30 ? 'text-success' : 'text-danger'" x-text="totalWeight + '%'"></div>
                                </td>
                                <td colspan="2" class="py-3">
                                    <template x-if="totalWeight !== 30">
                                        <span class="text-danger small fw-semibold"><i class="fas fa-info-circle me-1"></i> Targets must sum to 30%</span>
                                    </template>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="d-flex justify-content-between align-items-center mt-4 pb-5">
                <button type="button" @click="addRow" class="btn btn-light border fw-bold px-4 shadow-sm" :disabled="rows.length >= 20 || !deptId">
                    <i class="fas fa-plus me-2 text-success"></i> Add Another Row
                </button>
                <div class="d-flex gap-2">
                    <a href="{{ route('departmental-objective-assignments.index') }}" class="btn btn-light border px-5 fw-bold">Cancel</a>
                    <button type="submit" class="btn btn-success px-5 fw-bold shadow" :disabled="totalWeight !== 30">
                        <i class="fas fa-check-circle me-2"></i> Save & Deploy Objectives
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
        .excel-input {
            border-radius: 0;
            padding: 12px 15px;
            font-size: 0.95rem;
        }
        .excel-input:focus {
            background-color: #fffdf5;
            z-index: 10;
        }
        .excel-table td {
            vertical-align: middle;
            padding: 0;
        }
        .excel-table thead th {
            padding: 12px 15px;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
        }
        .employee-list-scroll::-webkit-scrollbar { width: 5px; }
        .employee-list-scroll::-webkit-scrollbar-track { background: #f1f1f1; }
        .employee-list-scroll::-webkit-scrollbar-thumb { background: #c5e1d3; border-radius: 10px; }
        .smaller { font-size: 0.7rem; }
    </style>

    <script>
        function objectiveSetting() {
            return {
                deptId: '{{ $selectedDeptId }}',
                teamId: '',
                teams: [],
                allEmployees: @json($deptEmployees),
                filteredEmployees: @json($deptEmployees),
                rows: [
                    { objective_master_id: '', timeline: '', weightage: 0, certifying_authority_user_id: '' },
                    { objective_master_id: '', timeline: '', weightage: 0, certifying_authority_user_id: '' }
                ],

                get totalWeight() {
                    return this.rows.reduce((sum, row) => sum + (parseInt(row.weightage) || 0), 0);
                },

                async fetchData() {
                    if (!this.deptId) {
                        this.teams = [];
                        this.filteredEmployees = [];
                        return;
                    }
                    
                    // Navigate to reload data from server context (handling teams/employees)
                    window.location.href = `{{ route('departmental-objective-assignments.create') }}?department_id=${this.deptId}`;
                },

                filterEmployees() {
                    if (!this.teamId) {
                        this.filteredEmployees = this.allEmployees;
                        return;
                    }
                    this.filteredEmployees = this.allEmployees.filter(e => e.team_id == this.teamId);
                },

                addRow() {
                    if (this.rows.length < 20) {
                        this.rows.push({ objective_master_id: '', timeline: '', weightage: 0, certifying_authority_user_id: '' });
                    }
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
                    formData.append('department_id', this.deptId);
                    formData.append('team_id', this.teamId || '');
                    
                    this.rows.forEach((row, index) => {
                        formData.append(`rows[${index}][objective_master_id]`, row.objective_master_id);
                        formData.append(`rows[${index}][timeline]`, row.timeline);
                        formData.append(`rows[${index}][weightage]`, row.weightage);
                        formData.append(`rows[${index}][certifying_authority_user_id]`, row.certifying_authority_user_id);
                    });

                    fetch('{{ route('departmental-objective-assignments.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(r => {
                        if (r.ok) window.location.href = '{{ route('departmental-objective-assignments.index') }}';
                        else alert('Failed to deploy objectives. Check validation rules.');
                    });
                }
            }
        }
    </script>
@endsection
