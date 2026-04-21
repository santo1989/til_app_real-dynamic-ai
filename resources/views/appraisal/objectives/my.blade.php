@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>My Objectives </h5>
            @if (!empty($fyLockedMessage ?? null))
                <div class="alert alert-warning">
                    {{ $fyLockedMessage }}
                    Please contact Admin / HR Admin / Board to activate a financial year first.
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Validation Errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('objectives.submit') }}">
                @csrf
                @include('components.alert')
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Weightage</th>
                            <th>Target</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                        @php
                            $oldObjectives = old('objectives');
                            $rows = is_array($oldObjectives)
                                ? collect($oldObjectives)
                                : collect($objectives)->map(function ($o) {
                                    return [
                                        'description' => $o->description,
                                        'weightage' => $o->weightage,
                                        'target' => $o->target,
                                    ];
                                });
                        @endphp
                        @forelse($rows as $i => $row)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="objectives[{{ $i }}][type]" value="individual" />
                                    <select name="objectives[{{ $i }}][description]" class="form-control"
                                        required>
                                        <option value="">Select objective</option>
                                        @if (!empty($individualObjectiveOptions ?? null) && count($individualObjectiveOptions) > 0)
                                            <optgroup label="Individual Objectives">
                                                @foreach ($individualObjectiveOptions as $opt)
                                                    @php
                                                        $currentValue = $row['description'] ?? '';
                                                        // Robust comparison: normalize whitespace and case
                                                        $isSelected =
                                                            strtolower(
                                                                trim(preg_replace('/\s+/', ' ', $currentValue)),
                                                            ) === strtolower(trim(preg_replace('/\s+/', ' ', $opt)));
                                                    @endphp
                                                    <option value="{{ $opt }}" {{ $isSelected ? 'selected' : '' }}>
                                                        {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @php
                                            $normalizedCurrent = strtolower(
                                                trim(preg_replace('/\s+/', ' ', (string) ($row['description'] ?? ''))),
                                            );
                                            $optionExists = collect($individualObjectiveOptions ?? [])->contains(
                                                function ($opt) use ($normalizedCurrent) {
                                                    return strtolower(
                                                        trim(preg_replace('/\s+/', ' ', (string) $opt)),
                                                    ) === $normalizedCurrent;
                                                },
                                            );
                                        @endphp
                                        @if (!empty($row['description']) && !$optionExists)
                                            <option value="{{ $row['description'] }}" selected>{{ $row['description'] }}
                                            </option>
                                        @endif
                                    </select>
                                </td>
                                <td>
                                    <select name="objectives[{{ $i }}][weightage]" class="form-control"
                                        required>
                                        @foreach ([10, 15, 20, 25] as $w)
                                            @php
                                                $currentWeight = $row['weightage'] ?? '';
                                            @endphp
                                            <option value="{{ $w }}"
                                                @if ($currentWeight == $w) selected @endif>{{ $w }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        value="{{ $row['target'] ?? '' }}" class="form-control" required /></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No objectives found. Add below.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <button type="button" id="add-row" class="btn btn-sm btn-outline-secondary"
                    @if (!empty($fyLockedMessage ?? null)) disabled @endif>Add Objective</button>
                <button type="submit" id="save-btn" class="btn btn-outline-primary"
                    @if (!empty($fyLockedMessage ?? null)) disabled @endif>Save</button>
                <a href="{{ route('objectives.my.form') }}" class="btn btn-outline-success"
                    @if ($objectives->count() === 0) aria-disabled="true" style="pointer-events:none;opacity:.6;" @endif>
                    View Saved Form
                </a>
                <a href="{{ route('idp.index') }}" class="btn btn-outline-info">Manage My IDPs</a>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-dark">Back</a>
            </form>
            @php
                $indMin = (int) config('appraisal.individual_min', 3);
                $indMax = (int) config('appraisal.individual_max', 6);
                $indTotal = (int) config('appraisal.individual_total', 70);
            @endphp
            <div id="objective-validation-note" class="mt-2 text-muted">Total objectives allowed:
                {{ $indMin }}–{{ $indMax }}. Weightages must sum to
                {{ $indTotal }}%.</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof $ === 'undefined') {
                alert('jQuery is not loaded. Please contact admin.');
                return;
            }
            let idx = $('#objectives-body tr').filter(function() {
                return $(this).find('select[name*="[description]"]').length > 0;
            }).length;
            const requiredTotal = {{ (int) config('appraisal.individual_total', 70) }};
            const minCount = {{ (int) config('appraisal.individual_min', 3) }};
            const maxCount = {{ (int) config('appraisal.individual_max', 6) }};
            const objectiveOptionGroups = `
                @if (!empty($individualObjectiveOptions ?? null) && count($individualObjectiveOptions) > 0)
                    <optgroup label="Individual Objectives">
                        @foreach ($individualObjectiveOptions as $opt)
                            <option value="{{ $opt }}">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}</option>
                        @endforeach
                    </optgroup>
                @endif
            `;

            function markFieldState($field, isInvalid) {
                $field.toggleClass('is-invalid', isInvalid);
            }

            function setFieldError($field, message) {
                let $feedback = $field.siblings('.inline-field-error');
                if (!$feedback.length) {
                    $feedback = $('<div class="invalid-feedback inline-field-error d-block"></div>');
                    $field.after($feedback);
                }

                if (message) {
                    $feedback.text(message).show();
                } else {
                    $feedback.text('').hide();
                }
            }

            function updateValidation() {
                const rows = $('#objectives-body tr').filter(function() {
                    return $(this).find('select[name*="weightage"]').length > 0;
                });
                let total = 0;
                let valid = true;
                rows.each(function() {
                    const $row = $(this);
                    const $desc = $row.find('select[name*="[description]"]');
                    const $target = $row.find('input[name*="[target]"]');
                    const $weightSel = $row.find('select[name*="weightage"]');

                    const descMissing = !String($desc.val() || '').trim();
                    const targetMissing = !String($target.val() || '').trim();
                    const weightVal = parseInt($weightSel.val(), 10);
                    const weightMissing = Number.isNaN(weightVal);

                    markFieldState($desc, descMissing);
                    markFieldState($target, targetMissing);
                    markFieldState($weightSel, weightMissing);

                    setFieldError($desc, descMissing ? 'Description is required.' : '');
                    setFieldError($target, targetMissing ? 'Target is required.' : '');
                    setFieldError($weightSel, weightMissing ? 'Weightage is required.' : '');

                    const rowInvalid = descMissing || targetMissing || weightMissing;
                    $row.toggleClass('table-warning', rowInvalid);

                    if (rowInvalid) valid = false;

                    const weight = parseInt($weightSel.val(), 10);
                    if (!isNaN(weight)) total += weight;
                });
                const weightageValid = (total === requiredTotal);
                // keep full form validation message for guidance
                if (rows.length < minCount || rows.length > maxCount) valid = false;
                if (!weightageValid) valid = false;

                // Requested behavior:
                // - Save enabled only when total weightage is valid (== requiredTotal)
                // - Add disabled when total weightage is valid
                $('#save-btn').prop('disabled', !weightageValid);
                $('#add-row').prop('disabled', weightageValid || rows.length >= maxCount);

                const note = $('#objective-validation-note');
                if (valid) {
                    note.removeClass('text-danger').addClass('text-success')
                        .text(`Ready to save. Weightage total is valid (${total}% / ${requiredTotal}%).`);
                } else {
                    note.removeClass('text-success').addClass('text-danger')
                        .text(
                            `Validation: weightage is valid only when total = ${requiredTotal}% (current: ${total}%). Objectives must also be ${minCount}–${maxCount}.`
                        );
                }

            }

            $('#add-row').on('click', function() {
                // prevent adding if disabled (double-safety)
                if ($(this).prop('disabled')) return;

                const placeholderRow = $('#objectives-body tr').filter(function() {
                    return $(this).find('select[name*="weightage"]').length === 0;
                });
                if (placeholderRow.length) {
                    placeholderRow.remove();
                }

                $('#objectives-body').append(`
                <tr>
                <td>${idx+1}</td>
                <td>
                    <input type="hidden" name="objectives[${idx}][type]" value="individual" />
                    <select name="objectives[${idx}][description]" class="form-control" required>
                        <option value="">Select objective</option>
                        ${objectiveOptionGroups}
                    </select>
                </td>
                <td>
                    <select name="objectives[${idx}][weightage]" class="form-control" required>
                        <option value="">Select</option>
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="20">20</option>
                        <option value="25">25</option>
                    </select>
                </td>
                <td><input type="text" name="objectives[${idx}][target]" class="form-control" required /></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button></td>
            </tr>
        `);
                idx++;
                updateValidation();
            });

            $('#objectives-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateValidation();
            });

            $('#objectives-body').on('change', 'select[name*="weightage"]', updateValidation);
            $('#objectives-body').on('input', 'input', updateValidation);

            updateValidation();

            @if (!empty($fyLockedMessage ?? null))
                $('#save-btn').prop('disabled', true);
                $('#add-row').prop('disabled', true);
            @endif
        });
    </script>
@endsection
