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
             <p class="text-muted small mb-0">Align your growth with your performance objectives for <span class="badge bg-success text-white fw-bold px-3">{{ $activeFYLabel }}</span></p>
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
                             <div class="bg-success p-3 rounded-circle me-3">
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
                         <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">Setting Period</span>
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
                    
                     
                </div>
            </div>
        </div>
    </div>

    <!-- IDP Setting Table -->
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0 fw-bold text-dark">Development Milestones</h5>
                <p class="text-muted small mb-0 font-italic">Define your skill areas and development activities for the year</p>
            </div>
            <button @click="addRow" class="btn btn-outline-success btn-sm px-3 rounded-pill">
                <i class="fas fa-plus me-1"></i> Add Row
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 excel-style excel-grid">
                    <thead style="background-color: #f8fbff; color: #1a6b3b;">
                        <tr>
                            <th style="width: 50px;" class="text-center small">SL</th>
                            <th style="width: 150px;" class="small"><i class="fas fa-star text-warning me-1"></i> Skill area</th>
                            <th style="min-width: 300px;" class="small"><i class="fas fa-seedling text-success me-1"></i> Development Objective</th>
                            <th style="min-width: 150px;" class="small">Expected Benefits</th>
                            <th style="min-width: 200px;" class="small">Development Action Plan</th>
                            <th style="min-width: 150px;" class="small">Resources Required</th>
                            <th style="width: 130px;" class="small">Deadline/ Timeline</th>
                            <th style="width: 150px;" class="text-center">Attainment of Individual Development Plan:</th>
                            <th style="min-width: 300px;">If yes, whether there is visible demonstration of use of the learning</th>
                            <th style="min-width: 150px;" class="small">HR Input</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="text-center fw-bold text-muted small" x-text="index + 1"></td>
                                <td class="p-0">
                                    <select x-model="row.skill_area" class="form-select border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;">
                                        <option value="">Select Skill</option>
                                        <template x-for="option in skillOptions" :key="option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </td>
                                <td class="p-0">
                                    <textarea x-model="row.description" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Development objective..."></textarea>
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
                                    <input type="date" x-model="row.timeline" class="form-control border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;">
                                </td>
                                <td class="p-0 bg-light text-center small text-muted">
                                    <span x-text="row.attainment == 1 ? 'YES' : (row.attainment == 0 ? 'NO' : '-')"></span>
                                </td>
                                <td class="p-0 bg-light">
                                    <textarea x-model="row.visible_demonstration" class="form-control border-0 shadow-none excel-textarea py-1 bg-light text-muted" style="font-size: 0.7rem;" rows="2" readonly placeholder="Manager input..."></textarea>
                                    <textarea x-model="row.visible_demonstration" class="form-control border-0 shadow-none excel-textarea py-1 bg-light text-muted" style="font-size: 0.7rem;" rows="2" readonly placeholder="If yes, whether there is visible demonstration of use of the learning..."></textarea>
                                </td>
                                <td class="p-0 bg-light">
                                    <textarea x-model="row.hr_input" class="form-control border-0 shadow-none excel-textarea py-1 bg-light text-muted" style="font-size: 0.7rem;" rows="2" readonly placeholder="HR Input..."></textarea>
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
                                <td colspan="11" class="text-center py-5 text-muted small italic">
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
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-history me-2 text-success"></i> My IDP History</h5>
            <span class="badge bg-success text-white px-3 py-2 shadow-sm">{{ $idps->count() }} Entries Found</span>
        </div>
        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 excel-style excel-grid" style="font-size: 0.75rem;">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th class="text-center">SL</th>
                            <th style="width: 150px;">Skill area</th>
                            <th style="min-width: 300px;">Development Objective</th>
                            <th style="min-width: 150px;">Expected Benefits</th>
                            <th style="min-width: 200px;">Development Action Plan</th>
                            <th style="min-width: 150px;">Resources Required</th>
                            <th style="width: 130px;">Deadline/ Timeline</th>
                            <th style="width: 150px;" class="text-center">Attainment of Individual Development Plan:</th>
                            <th style="min-width: 300px;">If yes, whether there is visible demonstration of use of the learning</th>
                            <th style="width: 150px;">HR Input</th>
                            <th style="width: 100px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($idps as $idp)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $loop->iteration }}</td>
                            <td><span class="badge bg-success text-white px-2 py-1">{{ $idp->skill_area }}</span></td>
                            <td>{{ Str::limit($idp->description, 150) }}</td>
                            <td>{{ Str::limit($idp->expected_benefits, 50) }}</td>
                            <td>{{ Str::limit($idp->action_plan, 50) }}</td>
                            <td>{{ Str::limit($idp->resources_required, 50) }}</td>
                            <td>{{ $idp->review_date ? \Carbon\Carbon::parse($idp->review_date)->format('d M Y') : ($idp->timeline ?? 'N/A') }}</td>
                            <td class="text-center fw-bold">
                                @if(is_null($idp->attainment))
                                    <span class="text-muted">-</span>
                                @else
                                    <span class="badge {{ $idp->attainment ? 'bg-success' : 'bg-danger' }} text-white">
                                        {{ $idp->attainment ? 'YES' : 'NO' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ Str::limit($idp->visible_demonstration, 50) }}</td>
                            <td class="text-muted small">{{ Str::limit($idp->hr_input, 50) }}</td>
                            <td class="text-center">
                                @if(!$idp->is_approved)
                                    <a href="{{ route('idp.edit', $idp) }}" class="btn btn-sm btn-link text-primary p-0" title="Edit IDP">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <span class="badge bg-success text-white small" style="font-size: 0.6rem;">
                                        <i class="fas fa-check-circle me-1"></i> Approved
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
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

    /* Horizontal Scroll & Table Polish */
    .table-responsive {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
        border-radius: 8px;
    }
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f8fafc;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background-color: #cbd5e1;
        border-radius: 20px;
        border: 2px solid #f8fafc;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background-color: #94a3b8;
    }

    .excel-style {
        min-width: 1600px; /* Forces horizontal scroll on smaller viewports */
        table-layout: fixed;
    }
    .excel-style th, .excel-style td {
        white-space: normal;
        word-wrap: break-word;
        vertical-align: top;
    }
</style>

<div id="idp-data" data-skill-options='@json($skillAreaOptions)'></div>

<script>
function idpSelfService() {
    return {
        rows: [],
        skillOptions: [],

        init() {
            const el = document.getElementById('idp-data');
            if (el) {
                try {
                    this.skillOptions = JSON.parse(el.dataset.skillOptions || '[]');
                } catch (e) {
                    this.skillOptions = [];
                }
            }
            // Start with one empty row if none exist
            if (this.rows.length === 0) {
                this.addRow();
            }
        },

        addRow() {
            this.rows.push({
                skill_area: '',
                description: '',
                action_plan: '',
                expected_benefits: '',
                resources_required: '',
                timeline: '',
                attainment: null,
                visible_demonstration: '',
                hr_input: ''
            });
        },

        removeRow(index) {
            this.rows.splice(index, 1);
        },

        async submitForm() {
            // Flexible Validation: Just ensure we have at least one valid row
            if (this.rows.length === 0) return alert('Please add at least one row.');
            
            const hasData = this.rows.some(r => r.skill_area || r.description);
            if (!hasData) return alert('Please enter Skill Area and Development for at least one row.');

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
