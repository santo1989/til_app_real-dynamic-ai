@extends('layouts.app')

@section('content')
@php
    $employee = $profileUser ?? auth()->user();
    $activeFYLabel = $activeFY?->label ?? 'N/A';
@endphp

<div class="container-fluid py-4 min-vh-100" style="background-color: #f8fafc;" x-data="idpSelfService()">
    <!-- Top Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-12 col-md-6">
            <h1 class="h3 fw-bold text-dark mb-1">My Individual Development Plan (IDP)</h1>
            <p class="text-muted small mb-0">Align your growth with your performance objectives for <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3">{{ $activeFYLabel }}</span></p>
        </div>
        <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
            <button @click="submitForm" class="btn text-white px-4 shadow-sm" style="background-color: #1a6b3b;" :disabled="rows.length === 0">
                <i class="fas fa-paper-plane me-2"></i> Submit Plan
            </button>
        </div>
    </div>

    <!-- Employee Profile Summary -->
    <div class="row g-4 mb-4">
        <!-- Profile Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm overflow-hidden h-100">
                <div class="card-body p-0 d-flex flex-column h-100">
                    <div class="p-4 bg-white border-bottom flex-grow-1">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle me-3">
                                <i class="fas fa-user-tie fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">{{ $employee->name }}</h5>
                                <p class="text-muted small mb-0">{{ $employee->employee_id }} | {{ $employee->designation ?? 'Designation Not Set' }}</p>
                            </div>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                                    <label class="text-xs fw-bold text-uppercase text-muted mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 0.05em;">Department</label>
                                    <div class="fw-semibold text-dark">{{ $employee->department->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                                    <label class="text-xs fw-bold text-uppercase text-muted mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 0.05em;">Reporting To</label>
                                    <div class="fw-semibold text-dark">{{ $employee->lineManager->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cycle Context Card -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Development Phase</h6>
                            <p class="text-muted small mb-0">Current status and upcoming milestones</p>
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-bold">Setting Period</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 border-start border-4 border-primary bg-light">
                                <div class="text-xs text-muted mb-1" style="font-size: 0.7rem;">IDP START DATE</div>
                                <div class="fw-bold fs-5">{{ $activeFY ? \Carbon\Carbon::parse($activeFY->start_date)->format('d M Y') : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 border-start border-4 border-success bg-light">
                                <div class="text-xs text-muted mb-1" style="font-size: 0.7rem;">PLAN END DATE</div>
                                <div class="fw-bold fs-5">{{ $activeFY ? \Carbon\Carbon::parse($activeFY->end_date)->format('d M Y') : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 rounded-3 bg-success bg-opacity-5 border border-success border-opacity-10">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-success me-2"></i>
                            <span class="small text-dark fw-medium">Align your development goals with your 70% Individual Objectives.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- IDP Setting Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">Development Milestones</h5>
                <p class="text-muted small mb-0 font-italic">Define your skill areas and development activities</p>
            </div>
            <button @click="addRow" class="btn btn-outline-success btn-sm px-3 rounded-pill">
                <i class="fas fa-plus me-1"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 excel-style">
                    <thead style="background-color: #f8fbff; color: #1a6b3b;">
                        <tr>
                            <th style="width: 50px;" class="text-center small">SL</th>
                            <th style="width: 180px;" class="small">Linked Plan</th>
                            <th style="width: 160px;" class="small">Skill Area</th>
                            <th class="small">Dev. Objective</th>
                            <th class="small">Expected Benefits</th>
                            <th class="small">Action Plan</th>
                            <th class="small">Resources</th>
                            <th style="width: 130px;" class="small">Timeline</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="text-center fw-bold text-muted small" x-text="index + 1"></td>
                                <td class="p-0">
                                    <select x-model="row.objective_title" class="form-select border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;" @change="onObjectiveChange(index)">
                                        <option value="">Select Plan</option>
                                        <template x-for="obj in objectives" :key="obj.id">
                                            <option :value="obj.description" x-text="obj.description"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="p-0">
                                    <template x-if="row.objective_title && getSkillAreas(row.objective_title).length > 0">
                                        <select x-model="row.skill_area" class="form-select border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;">
                                            <option value="">Select Skill</option>
                                            <template x-for="skill in getSkillAreas(row.objective_title)" :key="skill">
                                                <option :value="skill" x-text="skill"></option>
                                            </template>
                                        </select>
                                    </template>
                                    <template x-if="!row.objective_title || getSkillAreas(row.objective_title).length === 0">
                                        <input type="text" x-model="row.skill_area" class="form-control border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;" placeholder="Type skill area...">
                                    </template>
                                </td>
                                <td class="p-0">
                                    <textarea x-model="row.description" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Growth goal..."></textarea>
                                </td>
                                <td class="p-0">
                                    <textarea x-model="row.expected_benefits" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Benefits..."></textarea>
                                </td>
                                <td class="p-0">
                                    <textarea x-model="row.action_plan" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Activities..."></textarea>
                                </td>
                                <td class="p-0">
                                    <textarea x-model="row.resources_required" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Support..."></textarea>
                                </td>
                                <td class="p-0">
                                    <input type="text" x-model="row.timeline" class="form-control border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;" placeholder="e.g. Q4">
                                </td>
                                <td class="text-center p-0">
                                    <button @click="removeRow(index)" class="btn btn-link text-danger p-2 shadow-none border-0" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                        <template x-if="rows.length === 0">
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted small italic">
                                    <i class="fas fa-layer-group fa-2x mb-3 d-block opacity-25"></i>
                                    No milestones added yet. Click "Add Row" to begin your IDP setting.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Success History Section -->
    @if($idps->count() > 0)
    <div class="mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2 text-success"></i> My Active IDP Milestones</h5>
            <span class="badge bg-success text-white px-3 py-2 shadow-sm">{{ $idps->count() }} Saved Entries</span>
        </div>
        <div class="row g-3">
            @foreach($idps as $idp)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 mb-2 hover-lift">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success text-white rounded-pill px-3">{{ $idp->skill_area }}</span>
                            <span class="badge {{ $idp->status === 'approved' ? 'bg-success' : 'bg-warning' }} bg-opacity-10 {{ $idp->status === 'approved' ? 'text-success' : 'text-warning' }} small rounded-3 px-2 py-1">{{ strtoupper($idp->status ?? 'OPEN') }}</span>
                        </div>
                        <h6 class="fw-bold text-dark line-clamp-2 mb-2" style="min-height: 2.5rem;">{{ $idp->description }}</h6>
                        <div class="bg-light p-3 rounded-3 small text-muted mb-2">
                            <i class="fas fa-clipboard-list me-1"></i> {{ Str::limit($idp->action_plan, 80) }}
                        </div>
                        @if($idp->resources_required)
                        <div class="small mb-3 text-success bg-success bg-opacity-5 p-2 rounded border border-success border-opacity-10">
                            <i class="fas fa-tools me-1"></i> <span class="fw-semibold">Resources:</span> {{ Str::limit($idp->resources_required, 40) }}
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-end mt-auto pt-3 border-top border-light">
                            <div class="small">
                                <div class="text-xs text-muted fw-bold text-uppercase" style="font-size: 0.6rem;">Review Timeline</div>
                                <div class="text-dark fw-bold">{{ $idp->review_date ? \Carbon\Carbon::parse($idp->review_date)->format('d M Y') : ($idp->timeline ?? 'N/A') }}</div>
                            </div>
                            @can('delete', $idp)
                            <form method="POST" action="{{ route('idp.destroy', $idp) }}" onsubmit="return confirm('Remove this milestone?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm text-danger shadow-none p-0 border-0"><i class="fas fa-trash-alt"></i></button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
    .excel-style {
        border-collapse: collapse;
        width: 100%;
        table-layout: fixed;
    }
    .excel-style th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 12px 15px;
        letter-spacing: 0.02em;
    }
    .excel-style td {
        border: 1px solid #e9ecef;
        padding: 0;
        height: 60px;
        position: relative;
    }
    .excel-input {
        padding: 15px 20px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #2b3a4a;
        transition: all 0.2s;
        height: 100% !important;
        border-radius: 0;
    }
    .excel-input:focus {
        background-color: #f0f7f3 !important;
        z-index: 10;
        box-shadow: inset 0 0 0 2px #1a6b3b !important;
    }
    .excel-textarea {
        padding: 10px 15px;
        font-size: 0.85rem;
        resize: none;
        border-radius: 0;
    }
    .excel-textarea:focus {
        background-color: #f0f7f3 !important;
        box-shadow: inset 0 0 0 2px #1a6b3b !important;
    }
    .hover-lift {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .text-xs { font-size: 0.75rem; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

<script>
function idpSelfService() {
    return {
        rows: [],
        objectives: @json($myObjectives),
        skillMap: @json($objectiveSkillMap),

        init() {
            // Start with one empty row if none exist
            if (this.rows.length === 0) {
                this.addRow();
            }
        },

        addRow() {
            this.rows.push({
                objective_title: '',
                skill_area: '',
                description: '',
                action_plan: '',
                expected_benefits: '',
                resources_required: '',
                timeline: ''
            });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
        },

        getSkillAreas(objectiveTitle) {
            if (!objectiveTitle) return [];
            return this.skillMap[objectiveTitle] || [];
        },

        onObjectiveChange(index) {
            const title = this.rows[index].objective_title;
            // Reset skill area when objective changes
            this.rows[index].skill_area = '';
            // Auto-fill Dev Objective with the Plan title to assist user
            if (title) {
                this.rows[index].description = title;
            }
        },

        async submitForm() {
            // Flexible Validation: Just ensure we have at least one valid row
            if (this.rows.length === 0) return alert('Please add at least one row.');
            
            const hasData = this.rows.some(r => r.objective_title || r.skill_area);
            if (!hasData) return alert('Please select a Linked Plan or Skill Area for at least one row.');

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('user_id', '{{ auth()->id() }}');
            
            this.rows.forEach((row, index) => {
                formData.append(`idps[${index}][skill_area]`, row.skill_area);
                formData.append(`idps[${index}][description]`, row.description); 
                formData.append(`idps[${index}][expected_benefits]`, row.expected_benefits);
                formData.append(`idps[${index}][action_plan]`, row.action_plan);
                formData.append(`idps[${index}][resources_required]`, row.resources_required);
                formData.append(`idps[${index}][review_date]`, row.timeline); 
            });

            try {
                const r = await fetch("{{ route('idp.store') }}", {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (r.ok) {
                    window.location.reload();
                } else {
                    const data = await r.json();
                    alert('Submission failed: ' + (data.message || 'Check your inputs'));
                }
            } catch (e) {
                alert('An error occurred during submission.');
            }
        }
    }
}
</script>
@endsection
