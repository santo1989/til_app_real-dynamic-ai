@extends('layouts.app')

@section('content')
    @php
        $employee = $profileUser ?? auth()->user();
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
        $oldRows = old('idps');
        $initialRows = is_array($oldRows) && count($oldRows) > 0 ? $oldRows : [[]];
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

        .idp-head th {
            background: #e6f3ec;
            color: #146543;
            font-size: 12px;
            vertical-align: middle;
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
            <a href="{{ route('objectives.my.form') }}" class="btn btn-outline-secondary btn-sm me-2">View Objectives Form</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-dark btn-sm">Back</a>
        </div>

        <div class="card border-0 bg-transparent">
            <div class="card-body p-0">
                @include('components.alert')

                <h4 class="idp-title">Individual Development Plan (IDP): {{ $activeFY ?? 'N/A' }}</h4>

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
                    <div class="col-md-12">
                        <label class="idp-label">Date of IDP Setting</label>
                        <input type="text" class="form-control idp-input" value="{{ now()->format('d F Y') }}" readonly>
                    </div>
                </div>

                <form method="POST" action="{{ route('idp.store') }}" id="idp-form">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="idp-head">
                                <tr>
                                    <th style="width:60px;">SL #</th>
                                    <th style="width:170px;">Skill Area</th>
                                    <th>Development Objective</th>
                                    <th>Expected Benefits</th>
                                    <th>Development Action Plan</th>
                                    <th>Resources Required</th>
                                    <th style="width:170px;">Deadline / Timeline</th>
                                    <th style="width:90px;">Remove</th>
                                </tr>
                            </thead>
                            <tbody id="idp-rows">
                                @foreach ($initialRows as $idx => $row)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            <select class="form-control" name="idps[{{ $idx }}][skill_area]">
                                                <option value="">Select skill area</option>
                                                @foreach ($idpSkillAreaOptions ?? collect() as $opt)
                                                    @php
                                                        $selected =
                                                            strtolower(trim((string) ($row['skill_area'] ?? ''))) ===
                                                            strtolower(trim((string) $opt));
                                                    @endphp
                                                    <option value="{{ $opt }}" {{ $selected ? 'selected' : '' }}>
                                                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control"
                                                name="idps[{{ $idx }}][description]"
                                                value="{{ $row['description'] ?? '' }}"
                                                placeholder="Enter development objective" /></td>
                                        <td><input type="text" class="form-control"
                                                name="idps[{{ $idx }}][expected_benefits]"
                                                value="{{ $row['expected_benefits'] ?? '' }}" /></td>
                                        <td><input type="text" class="form-control"
                                                name="idps[{{ $idx }}][action_plan]"
                                                value="{{ $row['action_plan'] ?? '' }}" /></td>
                                        <td><input type="text" class="form-control"
                                                name="idps[{{ $idx }}][resources_required]"
                                                value="{{ $row['resources_required'] ?? '' }}" /></td>
                                        <td><input type="date" class="form-control"
                                                name="idps[{{ $idx }}][review_date]"
                                                value="{{ $row['review_date'] ?? '' }}" /></td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger remove-idp-row">Remove</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <button type="button" id="add-idp-row" class="btn btn-outline-secondary btn-sm">Add New IDP
                        Row</button>
                    <button type="submit" class="btn btn-outline-primary">Save IDPs</button>
                </form>

                <hr>
                <h6>Saved IDPs</h6>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Skill Area</th>
                            <th>Development Objective</th>
                            <th>Deadline</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($idps as $i => $idp)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $idp->skill_area_sentence_case ?? 'N/A' }}</td>
                                <td>{{ $idp->description_sentence_case }}</td>
                                <td>{{ $idp->review_date ? \Carbon\Carbon::parse($idp->review_date)->format('d M Y') : 'N/A' }}
                                </td>
                                <td>
                                    <x-ui.button variant="secondary" href="{{ route('idp.edit', $idp) }}"
                                        class="btn-sm">Edit</x-ui.button>
                                    @can('delete', $idp)
                                        <form method="POST" action="{{ route('idp.destroy', $idp) }}" class="d-inline"
                                            onsubmit="return confirm('Delete this IDP?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">No IDPs saved yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

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

                <div class="idp-footer">Tosrifa Industries Limited - Performance Appraisal System &copy;
                    {{ now()->year }}</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ === 'undefined') {
                return;
            }

            let idpIdx = $('#idp-rows tr').length;
            const skillOptionsHtml = `
                <option value="">Select skill area</option>
                @foreach ($idpSkillAreaOptions ?? collect() as $opt)
                    <option value="{{ $opt }}">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}</option>
                @endforeach
            `;

            function renumber() {
                $('#idp-rows tr').each(function(index) {
                    $(this).find('td:first').text(index + 1);
                });
            }

            $('#add-idp-row').on('click', function() {
                $('#idp-rows').append(`
                    <tr>
                        <td>${idpIdx + 1}</td>
                        <td><select class="form-control" name="idps[${idpIdx}][skill_area]">${skillOptionsHtml}</select></td>
                        <td><input type="text" class="form-control" name="idps[${idpIdx}][description]" placeholder="Enter development objective" /></td>
                        <td><input type="text" class="form-control" name="idps[${idpIdx}][expected_benefits]" /></td>
                        <td><input type="text" class="form-control" name="idps[${idpIdx}][action_plan]" /></td>
                        <td><input type="text" class="form-control" name="idps[${idpIdx}][resources_required]" /></td>
                        <td><input type="date" class="form-control" name="idps[${idpIdx}][review_date]" /></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger remove-idp-row">Remove</button></td>
                    </tr>
                `);
                idpIdx++;
                renumber();
            });

            $('#idp-rows').on('click', '.remove-idp-row', function() {
                if ($('#idp-rows tr').length <= 1) {
                    $(this).closest('tr').find('input').val('');
                    $(this).closest('tr').find('select').val('');
                    return;
                }
                $(this).closest('tr').remove();
                renumber();
            });
        });
    </script>
@endsection
