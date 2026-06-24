@extends('layouts.app')

@section('content')
    @php
        $employee = $idp->user;
        $joiningDate = !empty($employee?->date_of_joining) ? \Carbon\Carbon::parse($employee->date_of_joining) : null;
        $today = now();
        $tenureText = 'N/A';
        if ($joiningDate) {
            $diff = $joiningDate->diff($today);
            $years = (int) $diff->y;
            $months = (int) $diff->m;
            $tenureText = trim(
                ($years > 0 ? $years . ' year' . ($years > 1 ? 's' : '') : '') .
                    ' ' .
                    ($months > 0 ? $months . ' month' . ($months > 1 ? 's' : '') : ''),
            );
            if ($tenureText === '') {
                $tenureText = 'Less than a month';
            }
        }
    @endphp

    <style>
        .idp-wrap {
            background: #f2f4f5;
            border: 1px solid #dfe3e6;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .idp-title {
            color: #116b45;
            font-weight: 600;
            border-bottom: 2px solid #116b45;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .idp-label {
            font-size: 12px;
            color: #2d3b41;
            margin-bottom: 4px;
        }

        .idp-input {
            background: #f8fafb;
            border-color: #d5dde2;
            font-size: 13px;
        }

        .signature-box {
            height: 92px;
            border: 1px dashed #c3c9cf;
            background: #f7f8fa;
        }

        .idp-footer {
            text-align: center;
            color: #7b848b;
            font-size: 11px;
            margin-top: 14px;
        }
    </style>

    <div class="idp-wrap p-3 p-md-4">
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('idp.index') }}" class="btn btn-outline-secondary btn-sm me-2">Back To My IDPs</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-sm">Back</a>
        </div>

        <div class="card border-0 bg-transparent">
            <div class="card-body p-0">
                @include('components.alert')

                <h4 class="idp-title">Edit Individual Development Plan (IDP)</h4>

                <form method="POST" action="{{ route('employee.profile.context.update') }}" class="mb-3">
                    @csrf
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="idp-label">Designation</label>
                            <select class="form-control idp-input" name="designation">
                                <option value="">Select Designation</option>
                                @foreach ($designationOptions ?? collect() as $dOpt)
                                    <option value="{{ $dOpt }}"
                                        {{ (string) old('designation', $employee->designation) === (string) $dOpt ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($dOpt)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label">Department</label>
                            <select class="form-control idp-input" name="department_id">
                                <option value="">Select Department</option>
                                @foreach ($departments ?? collect() as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ (string) old('department_id', $employee->department_id) === (string) $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label">Date of joining</label>
                            <input type="date" class="form-control idp-input" name="date_of_joining"
                                value="{{ old('date_of_joining', optional($employee->date_of_joining)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label">Tenure in current role</label>
                            <input type="text" class="form-control idp-input" name="tenure_in_current_role"
                                value="{{ old('tenure_in_current_role', $employee->tenure_in_current_role ?? $tenureText) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm">Save Employee Details</button>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="idp-label">Name of the employee</label>
                        <input type="text" class="form-control idp-input" value="{{ $employee->name ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="idp-label">Employee ID</label>
                        <input type="text" class="form-control idp-input" value="{{ $employee->employee_id ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="idp-label">Designation</label>
                        <input type="text" class="form-control idp-input" value="{{ $employee->designation ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="idp-label">Department</label>
                        <input type="text" class="form-control idp-input"
                            value="{{ $employee->department->name ?? 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="idp-label">Date of joining</label>
                        <input type="text" class="form-control idp-input"
                            value="{{ $joiningDate ? $joiningDate->format('d F Y') : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="idp-label">Tenure in current role</label>
                        <input type="text" class="form-control idp-input" value="{{ $tenureText }}" readonly>
                    </div>
                </div>

                <form method="POST" action="{{ route('idp.update', $idp) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="user_id" value="{{ $idp->user_id }}">

                    <div class="row g-3">
                        <div class="col-12 mb-2">
                            <h5 class="fw-bold text-success"><i class="fas fa-user-edit me-2"></i>Employee Input</h5>
                            <hr class="mt-1 mb-3">
                        </div>

                        <div class="col-md-6">
                            <label class="idp-label fw-bold text-dark"><i class="fas fa-star text-warning me-1"></i> Skill area</label>
                            <select name="skill_area" class="form-control idp-input" id="skill_area_select">
                                <option value="">Select skill area</option>
                                @foreach ($idpSkillAreaOptions ?? collect() as $opt)
                                    <option value="{{ $opt }}"
                                        {{ old('skill_area', $idp->skill_area) == $opt ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label fw-bold text-dark"><i class="fas fa-calendar-alt text-primary me-1"></i> Deadline/ Timeline</label>
                            <input type="date" name="review_date" class="form-control idp-input"
                                value="{{ old('review_date', optional($idp->review_date)->format('Y-m-d') ?? $idp->review_date) }}"
                                required />
                        </div>
                        <div class="col-md-12">
                            <label class="idp-label fw-bold text-dark"><i class="fas fa-seedling text-success me-1"></i> Development Objective</label>
                            <textarea name="description" class="form-control idp-input" rows="3" required placeholder="What skill/behavior do you want to develop and what is the end goal?">{{ old('description', $idp->description_sentence_case ?? $idp->description) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label fw-bold text-dark">Expected Benefits</label>
                            <textarea name="expected_benefits" class="form-control idp-input" rows="3">{{ old('expected_benefits', $idp->expected_benefits_sentence_case ?? $idp->expected_benefits) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="idp-label fw-bold text-dark">Development Action Plan</label>
                            <textarea name="action_plan" class="form-control idp-input" rows="3">{{ old('action_plan', $idp->action_plan_sentence_case ?? $idp->action_plan) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="idp-label fw-bold text-dark">Resources Required</label>
                            <textarea name="resources_required" class="form-control idp-input" rows="2">{{ old('resources_required', $idp->resources_required_sentence_case ?? $idp->resources_required) }}</textarea>
                        </div>

                        <div class="col-12 mt-4 mb-2">
                            <h5 class="fw-bold text-muted"><i class="fas fa-user-check me-2"></i>Review & HR Input (Read-only)</h5>
                            <hr class="mt-1 mb-3">
                        </div>

                        <div class="col-md-6">
                            <label class="idp-label text-muted">Attainment of Individual Development Plan:</label>
                            <div class="p-2 bg-light border rounded small text-dark fw-bold">
                                {{ is_null($idp->attainment) ? 'Not Yet Reviewed' : ($idp->attainment ? 'YES' : 'NO') }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="idp-label text-muted">If yes, whether there is visible demonstration of use of the learning</label>
                            <textarea class="form-control idp-input bg-light" rows="2" readonly>{{ $idp->visible_demonstration ?? 'No demonstration recorded yet.' }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="idp-label text-muted">HR Input</label>
                            <textarea class="form-control idp-input bg-light" rows="2" readonly>{{ $idp->hr_input ?? 'No HR input yet.' }}</textarea>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="fas fa-check me-1"></i> Update My IDP
                        </button>
                        <a href="{{ route('idp.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </form>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="idp-label">Signature of the employee</label>
                        <div class="signature-box"></div>
                        <label class="idp-label mt-2">Name</label>
                        <input type="text" class="form-control idp-input" value="{{ $employee->name ?? '' }}"
                            readonly>
                        <label class="idp-label mt-2">Date</label>
                        <input type="text" class="form-control idp-input" value="{{ now()->format('d F Y') }}"
                            readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="idp-label">Signature of the immediate line manager</label>
                        <div class="signature-box"></div>
                        <label class="idp-label mt-2">Name</label>
                        <input type="text" class="form-control idp-input"
                            value="{{ $employee->lineManager->name ?? 'N/A' }}" readonly>
                        <label class="idp-label mt-2">Date</label>
                        <input type="text" class="form-control idp-input" value="" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="idp-label">Signature of HR Manager</label>
                        <div class="signature-box"></div>
                        <label class="idp-label mt-2">Name</label>
                        <input type="text" class="form-control idp-input" value="" readonly>
                        <label class="idp-label mt-2">Date</label>
                        <input type="text" class="form-control idp-input" value="" readonly>
                    </div>
                </div>

                @include('appraisal.idp.partials.milestones', ['idp' => $idp])

                <div class="idp-footer">Tosrifa Industries Limited - Performance Appraisal System &copy;
                    {{ now()->year }}</div>
            </div>
        </div>
    </div>
@endsection
