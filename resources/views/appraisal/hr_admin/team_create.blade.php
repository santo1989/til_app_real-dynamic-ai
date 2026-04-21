@extends('layouts.app')

@section('content')
    <x-ui.datatable-card title="Create Team" subtitle="Define a new team and assign members" icon="fa-people-group" body-class="p-4">
        <x-slot name="actions">
            <x-ui.button variant="secondary" href="{{ route('teams.index') }}" class="btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back
            </x-ui.button>
        </x-slot>

        <form method="POST" action="{{ route('teams.store') }}" x-data="teamManager()">
            @csrf
            @include('components.alert')

            <div class="row g-4">
                <!-- Left Column: Team Details -->
                <div class="col-12 col-lg-5 border-end">
                    <div class="fw-semibold mb-1">Team Information</div>
                    <p class="text-muted small mb-3">Set basic details and certifying authority.</p>

                    <div class="mb-3">
                        <x-ui.form-field name="name" label="Team Name" placeholder="e.g., Digital Transformation Team" required="true" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                        <select name="department_id" x-model="selectedDept" required class="form-select rounded-0" @change="updateEmployees()">
                            <option value="">-- Select Department --</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Select a department to see available employees.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team Lead (Optional)</label>
                        <select name="team_lead_id" class="form-select rounded-0">
                            <option value="">-- No Lead Assigned --</option>
                            @foreach ($potentialLeads as $lead)
                                <option value="{{ $lead->id }}">{{ $lead->name }} ({{ ucwords(str_replace('_', ' ', $lead->role)) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3 d-flex align-items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" checked value="1" class="form-check-input">
                        <label for="is_active" class="form-check-label small fw-medium">Active Team</label>
                    </div>
                </div>

                <!-- Right Column: Member Selection -->
                <div class="col-12 col-lg-7">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div>
                            <div class="fw-semibold">Team Members</div>
                            <p class="text-muted small">Select employees to map to this team.</p>
                        </div>
                        <template x-if="selectedDept">
                            <span class="badge bg-primary rounded-pill" x-text="`${selectedMembers.length} selected`"></span>
                        </template>
                    </div>

                    <div x-show="!selectedDept" class="p-5 text-center bg-light border rounded-3 mt-2">
                        <i class="fas fa-building-user fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Please select a department first</h6>
                    </div>

                    <div x-show="selectedDept" class="mt-2" x-transition>
                        <!-- Search Employee -->
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" x-model="searchQuery" class="form-control border-start-0 ps-0" placeholder="Search by name or ID...">
                        </div>

                        <!-- Employee List -->
                        <div class="border rounded-3 overflow-hidden" style="max-height: 400px; overflow-y: auto; background: #fafafa;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <input type="checkbox" class="form-check-input" @change="toggleAll($event.target.checked)">
                                        </th>
                                        <th>Employee Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="emp in filteredEmployees" :key="emp.id">
                                        <tr @click="toggleMember(emp.id)" class="cursor-pointer" :class="isMemberSelected(emp.id) ? 'table-success' : ''">
                                            <td class="text-center align-middle">
                                                <input type="checkbox" name="member_ids[]" :value="emp.id" :checked="isMemberSelected(emp.id)" @click.stop class="form-check-input">
                                            </td>
                                            <td class="align-middle">
                                                <div class="fw-medium" x-text="emp.name"></div>
                                                <div class="small text-muted" x-text="emp.employee_id || 'No ID'"></div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="filteredEmployees.length === 0">
                                        <tr>
                                            <td colspan="2" class="text-center p-4 text-muted">
                                                No employees found matching your search.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-3">
                <x-ui.button variant="secondary" href="{{ route('teams.index') }}">
                    Cancel
                </x-ui.button>
                <x-ui.button variant="primary" type="submit">
                    <i class="fas fa-check me-1"></i> Create Team
                </x-ui.button>
            </div>
        </form>
    </x-ui.datatable-card>

    <script>
        function teamManager() {
            return {
                selectedDept: '',
                searchQuery: '',
                selectedMembers: [],
                allEmployees: @json($allUsers),
                
                get filteredEmployees() {
                    if (!this.selectedDept) return [];
                    return this.allEmployees.filter(emp => {
                        const inDept = emp.department_id == this.selectedDept;
                        const matchesSearch = emp.name.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                           (emp.employee_id && emp.employee_id.toLowerCase().includes(this.searchQuery.toLowerCase()));
                        return inDept && matchesSearch;
                    });
                },

                updateEmployees() {
                    this.selectedMembers = [];
                    this.searchQuery = '';
                },

                toggleMember(id) {
                    const index = this.selectedMembers.indexOf(id);
                    if (index > -1) {
                        this.selectedMembers.splice(index, 1);
                    } else {
                        this.selectedMembers.push(id);
                    }
                },

                isMemberSelected(id) {
                    return this.selectedMembers.includes(id);
                },

                toggleAll(checked) {
                    if (checked) {
                        this.filteredEmployees.forEach(emp => {
                            if (!this.selectedMembers.includes(emp.id)) {
                                this.selectedMembers.push(emp.id);
                            }
                        });
                    } else {
                        const filteredIds = this.filteredEmployees.map(e => e.id);
                        this.selectedMembers = this.selectedMembers.filter(id => !filteredIds.includes(id));
                    }
                }
            }
        }
    </script>
    <style>
        .cursor-pointer { cursor: pointer; }
        .table-success { background-color: rgba(42, 135, 96, 0.08) !important; }
    </style>
@endsection
