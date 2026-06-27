@extends('layouts.app')

@section('content')
    @php
        $departmental = $deptObjectives;
        $individual = $objectives->where('type', 'individual')->values();
        $totalWeight = (int) ($departmental->sum('weightage') + $individual->sum('weightage'));

        $joiningDate = !empty($user->date_of_joining) ? \Carbon\Carbon::parse($user->date_of_joining) : null;
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
        .obj-preview-wrap {
            background: #f2f4f5;
            border: 1px solid #dfe3e6;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        }

        .obj-preview-title {
            color: #116b45;
            font-weight: 600;
            border-bottom: 2px solid #116b45;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .obj-label {
            font-size: 12px;
            color: #2d3b41;
            margin-bottom: 4px;
        }

        .obj-input {
            background: #f8fafb;
            border-color: #d5dde2;
            font-size: 13px;
        }

        .obj-head th {
            background: #e6f3ec;
            color: #146543;
            font-size: 12px;
            vertical-align: middle;
        }

        .obj-band {
            background: #dde1e5;
            font-weight: 600;
            color: #1d2529;
        }

        .obj-total {
            background: #cfe8f1;
            font-weight: 600;
        }

        .signature-box {
            height: 92px;
            border: 1px dashed #c3c9cf;
            background: #f7f8fa;
        }

        .obj-footer {
            text-align: center;
            color: #7b848b;
            font-size: 11px;
            margin-top: 14px;
        }
    </style>

    <div class="obj-preview-wrap p-3 p-md-4">
        <div class="d-flex justify-content-end mb-2">
            <a href="{{ route('objectives.my') }}" class="btn btn-outline-secondary btn-sm me-2">Edit Objectives</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-sm">Back</a>
        </div>

        <div class="card border-0 bg-transparent">
            <div class="card-body p-0">
                @include('components.alert')

                <h4 class="obj-preview-title">Objective Setting: {{ $activeFY ?? 'N/A' }}</h4>

                @if (!empty($fyLockedMessage ?? null))
                    <div class="alert alert-warning">
                        {{ $fyLockedMessage }}
                        Please contact Admin / HR Admin / Board to activate a financial year first.
                    </div>
                @endif

                <form method="POST" action="{{ route('employee.profile.context.update') }}" class="mb-3">
                    @csrf
                    <div class="row g-3 mb-2">
                        <div class="col-md-6">
                            <label class="obj-label">Designation</label>
                            <select class="form-control obj-input" name="designation">
                                <option value="">Select Designation</option>
                                @foreach ($designationOptions ?? collect() as $dOpt)
                                    <option value="{{ $dOpt }}"
                                        {{ (string) old('designation', $user->designation) === (string) $dOpt ? 'selected' : '' }}>
                                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($dOpt)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="obj-label">Department</label>
                            <select class="form-control obj-input" name="department_id">
                                <option value="">Select Department</option>
                                @foreach ($departments ?? collect() as $dept)
                                    <option value="{{ $dept->id }}"
                                        {{ (string) old('department_id', $user->department_id) === (string) $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="obj-label">Date of joining</label>
                            <input type="date" class="form-control obj-input" name="date_of_joining"
                                value="{{ old('date_of_joining', optional($user->date_of_joining)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="obj-label">Tenure in current role</label>
                            <input type="text" class="form-control obj-input" name="tenure_in_current_role"
                                value="{{ old('tenure_in_current_role', $user->tenure_in_current_role ?? $tenureText) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm">Save Employee Details</button>
                </form>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="obj-label">Name of the employee</label>
                        <input type="text" class="form-control obj-input" value="{{ $user->name }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="obj-label">Employee ID</label>
                        <input type="text" class="form-control obj-input" value="{{ $user->employee_id ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="obj-label">Designation</label>
                        <input type="text" class="form-control obj-input" value="{{ $user->designation ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="obj-label">Department</label>
                        <input type="text" class="form-control obj-input" value="{{ $user->department->name ?? 'N/A' }}"
                            readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="obj-label">Date of joining</label>
                        <input type="text" class="form-control obj-input"
                            value="{{ $joiningDate ? $joiningDate->format('d F Y') : 'N/A' }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="obj-label">Tenure in current role</label>
                        <input type="text" class="form-control obj-input" value="{{ $tenureText }}" readonly>
                    </div>
                    <div class="col-md-12">
                        <label class="obj-label">Date of Objective Setting</label>
                        <input type="text" class="form-control obj-input" value="{{ now()->format('d F Y') }}" readonly>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="obj-head">
                            <tr>
                                <th style="width:70px;">SL #</th>
                                <th>Objectives/Action Plans</th>
                                <th style="width:30%;">Timeline</th>
                                <th style="width:220px;">Weightage % (10-25%, total 100%)</th>
                                <th style="width:260px;">Name of the Certifying Authority / Department</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="obj-band">
                                <td colspan="5"><strong>Departmental/Team Objectives (Total 30%)</strong></td>
                            </tr>
                            @forelse ($departmental as $index => $obj)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $obj->master->title ?? $obj->description }}</td>
                                    <td>{{ $obj->timeline ?? $obj->target }}</td>
                                    <td>{{ $obj->weightage }}%</td>
                                    <td>{{ $obj->certifyingAuthorityUser->name ?? ($obj->department->name ?? 'N/A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">No departmental/team objectives assigned.</td>
                                </tr>
                            @endforelse

                            <tr class="obj-band">
                                <td colspan="5"><strong>Individual Objectives (Total 70%)</strong></td>
                            </tr>
                            @forelse ($individual as $index => $obj)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $obj->description }}</td>
                                    <td>{{ $obj->timeline ?? $obj->target }}</td>
                                    <td>{{ $obj->weightage }}%</td>
                                    <td>{{ $obj->certifying_authority ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-muted">No individual objectives saved.</td>
                                </tr>
                            @endforelse

                            <tr class="obj-total">
                                <td colspan="4" class="text-end"><strong>Total Weightage</strong></td>
                                <td><strong>{{ $totalWeight }}%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('idp.index') }}" class="btn btn-outline-info btn-sm">Go To My IDP Form</a>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="obj-label">Signature of the employee</label>
                        <div class="signature-box"></div>
                        <label class="obj-label mt-2">Name</label>
                        <input type="text" class="form-control obj-input" value="{{ $user->name }}" readonly>
                        <label class="obj-label mt-2">Date</label>
                        <input type="text" class="form-control obj-input" value="{{ now()->format('d F Y') }}"
                            readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="obj-label">Signature of the immediate line manager</label>
                        <div class="signature-box"></div>
                        <label class="obj-label mt-2">Name</label>
                        <input type="text" class="form-control obj-input"
                            value="{{ $user->lineManager->name ?? 'N/A' }}" readonly>
                        <label class="obj-label mt-2">Date</label>
                        <input type="text" class="form-control obj-input" value="" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="obj-label">Signature of HR Manager</label>
                        <div class="signature-box"></div>
                        <label class="obj-label mt-2">Name</label>
                        <input type="text" class="form-control obj-input" value="" readonly>
                        <label class="obj-label mt-2">Date</label>
                        <input type="text" class="form-control obj-input" value="" readonly>
                    </div>
                </div>

                <div class="obj-footer">Tosrifa Industries Limited - Performance Appraisal System &copy;
                    {{ now()->year }}</div>
            </div>
        </div>
    @endsection
