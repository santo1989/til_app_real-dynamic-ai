@extends('layouts.app')

@section('content')
    <style>
        .excel-style {
            min-width: 1600px;
            table-layout: fixed;
        }
        .excel-style th, .excel-style td {
            white-space: normal;
            word-wrap: break-word;
            vertical-align: top;
        }
        .excel-textarea {
            resize: none;
            overflow: hidden;
            min-height: 45px;
            transition: all 0.2s;
        }
        .excel-textarea:focus {
            min-height: 80px;
            background-color: #fff !important;
            z-index: 10;
            position: relative;
        }
        .excel-input:focus {
            background-color: #fff !important;
        }
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
            border-radius: 8px;
        }
        .table-responsive::-webkit-scrollbar { height: 8px; }
        .table-responsive::-webkit-scrollbar-track { background: #f8fafc; }
        .table-responsive::-webkit-scrollbar-thumb {
            background-color: #cbd5e1;
            border-radius: 20px;
            border: 2px solid #f8fafc;
        }
    </style>

    <div class="container-fluid py-4 min-vh-100" style="background-color: #f8fafc;" x-data="idpManagerReview()">
        <form @submit.prevent="submitReview">
            <div class="row mb-4 align-items-center">
                <div class="col-12 col-md-6">
                    <h1 class="h3 fw-bold text-dark mb-1">Review IDP Plan</h1>
                    <p class="text-muted small mb-0">
                        Reviewing for <span class="fw-bold text-success">{{ $employee->name }}</span>
                        @if ($activeFY)
                            | <span class="badge bg-success text-white px-3">{{ $activeFY }}</span>
                        @endif
                    </p>
                </div>
                <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2 align-items-center">
                    <a href="{{ route('idp.team.list') }}" class="btn btn-outline-secondary px-4 shadow-sm">
                        <i class="fas fa-arrow-left me-2"></i> Back
                    </a>
                    <button type="submit" class="btn text-white px-4 shadow-sm" style="background-color: #1a6b3b;" :disabled="loading">
                        <template x-if="!loading">
                            <span><i class="fas fa-save me-2"></i> Save Review</span>
                        </template>
                        <template x-if="loading">
                            <span><i class="fas fa-spinner fa-spin me-2"></i> Saving...</span>
                        </template>
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm overflow-hidden mb-4">
                <div class="card-body p-4 bg-white border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="bg-success p-3 rounded-circle me-3 text-white">
                                    <i class="fas fa-user-tie fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold text-dark">{{ $employee->name }}</h5>
                                    <p class="text-muted small mb-0">{{ $employee->employee_id }} | {{ $employee->designation }} | {{ $employee->department->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <span class="text-muted small">Total Milestones:</span>
                            <span class="h4 fw-bold text-success ms-1" x-text="rows.length"></span>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0 excel-style">
                        <thead style="background-color: #f8fbff; color: #1a6b3b;">
                            <tr>
                                <th style="width: 50px;" class="text-center small">SL</th>
                                <th style="width: 150px;" class="small"><i class="fas fa-star text-warning me-1"></i> Skill area</th>
                                <th style="min-width: 300px;" class="small"><i class="fas fa-seedling text-success me-1"></i> Development Objective</th>
                                <th style="min-width: 150px;" class="small">Expected Benefits</th>
                                <th style="min-width: 200px;" class="small">Development Action Plan</th>
                                <th style="min-width: 150px;" class="small">Resources Required</th>
                                <th style="width: 130px;" class="small">Deadline/ Timeline</th>
                                <th style="width: 150px;" class="text-center small">Attainment of Individual Development Plan:</th>
                                <th style="min-width: 300px;" class="small">If yes, whether there is visible demonstration of use of the learning</th>
                                <th style="min-width: 150px;" class="small">HR Input</th>
                                <th style="width: 100px;" class="text-center small">Approval</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <td class="text-center fw-bold text-muted small" x-text="index + 1"></td>
                                    <td class="p-0">
                                        <select x-model="row.skill_area" class="form-select border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;">
                                            <template x-for="option in skillOptions" :key="option">
                                                <option :value="option" x-text="option" :selected="row.skill_area == option"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="p-0">
                                        <textarea x-model="row.description" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2"></textarea>
                                    </td>
                                    <td class="p-0">
                                        <textarea x-model="row.expected_benefits" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2"></textarea>
                                    </td>
                                    <td class="p-0">
                                        <textarea x-model="row.action_plan" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2"></textarea>
                                    </td>
                                    <td class="p-0">
                                        <textarea x-model="row.resources_required" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2"></textarea>
                                    </td>
                                    <td class="p-0">
                                        <input type="date" x-model="row.timeline" class="form-control border-0 shadow-none h-100 excel-input py-1" style="font-size: 0.75rem;">
                                    </td>
                                    <td class="p-1">
                                        <select x-model="row.attainment" class="form-select form-select-sm border-0 shadow-none" style="font-size: 0.7rem;">
                                            <option value="">Unset</option>
                                            <option value="1">YES</option>
                                            <option value="0">NO</option>
                                        </select>
                                    </td>
                                    <td class="p-0">
                                        <textarea x-model="row.visible_demonstration" class="form-control border-0 shadow-none excel-textarea py-1" style="font-size: 0.75rem;" rows="2" placeholder="Visible demonstration..."></textarea>
                                    </td>
                                    <td class="p-0 bg-light text-muted">
                                        <textarea x-model="row.hr_input" class="form-control border-0 shadow-none excel-textarea py-1 bg-light" style="font-size: 0.75rem;" rows="2" readonly></textarea>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input" type="checkbox" x-model="row.is_approved">
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <script>
        function idpManagerReview() {
            return {
                rows: @json($idpData),
                skillOptions: @json($skillAreaOptions),
                isApproved: @json($idps->every('is_approved', true)),
                loading: false,
                
                async submitReview() {
                    this.loading = true;
                    try {
                        const response = await fetch("{{ route('idp.team.update', $idps->first()) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                idps: this.rows.map(row => ({
                                    ...row,
                                    review_date: row.timeline
                                }))
                            })
                        });

                        const result = await response.json();
                        if (response.ok) {
                            window.location.href = "{{ route('idp.team.list') }}";
                        } else {
                            let errorMsg = result.message || 'Something went wrong';
                            if (result.errors) {
                                errorMsg += ':\n' + Object.values(result.errors).flat().join('\n');
                            }
                            alert(errorMsg);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('A connection error occurred.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
@endsection
