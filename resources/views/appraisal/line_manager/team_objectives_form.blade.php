@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>{{ isset($objective) ? 'Edit' : 'Create' }} Team Objective</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
            action="{{ isset($objective) ? route('team.objectives.update', $objective) : route('team.objectives.store') }}">
            @csrf
            @if (isset($objective))
                @method('PUT')
            @endif
            <div id="objectives-wrapper">
                <div class="mb-3">
                    <label for="department_id" class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}"
                                {{ old('department_id', $objective->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="certifying_authority" class="form-label">Name of the Certifying Authority /
                        Department</label>
                    <input type="text" name="certifying_authority" id="certifying_authority" class="form-control"
                        value="{{ old('certifying_authority', $objective->certifying_authority ?? '') }}"
                        placeholder="Leave empty to use selected department name" />
                </div>

                <div class="mb-3">
                    <label class="form-label">Objectives</label>
                    <div id="objective-rows">
                        @php
                            $oldObjectives = old('objectives', []);
                            if (isset($objective) && empty($oldObjectives)) {
                                $oldObjectives = [
                                    [
                                        'description' => $objective->description,
                                        'weightage' => $objective->weightage,
                                        'target' => $objective->target,
                                    ],
                                ];
                            }
                        @endphp
                        @if (!empty($oldObjectives))
                            @foreach ($oldObjectives as $row)
                                <div class="objective-row mb-2">
                                    <select name="objectives[][description]" class="form-control mb-1" required>
                                        <option value="">Select departmental objective</option>
                                        @foreach ($departmentalObjectiveOptions ?? collect() as $opt)
                                            @php
                                                $rowDesc = $row['description'] ?? '';
                                                $isSelected =
                                                    strtolower(trim(preg_replace('/\s+/', ' ', $rowDesc))) ===
                                                    strtolower(trim(preg_replace('/\s+/', ' ', $opt)));
                                            @endphp
                                            <option value="{{ $opt }}" @selected($isSelected)>
                                                {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="d-flex gap-2">
                                        <select name="objectives[][weightage]" class="form-control w-25" required>
                                            @foreach ([10, 15] as $w)
                                                <option value="{{ $w }}"
                                                    {{ ($row['weightage'] ?? '') == $w ? 'selected' : '' }}>
                                                    {{ $w }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="objectives[][target]" class="form-control"
                                            placeholder="Target" value="{{ $row['target'] ?? '' }}" required />
                                        <x-ui.button variant="danger" type="button"
                                            class="btn-sm remove-row">Remove</x-ui.button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="objective-row mb-2">
                                <select name="objectives[][description]" class="form-control mb-1" required>
                                    <option value="">Select departmental objective</option>
                                    @foreach ($departmentalObjectiveOptions ?? collect() as $opt)
                                        <option value="{{ $opt }}">
                                            {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="d-flex gap-2">
                                    <select name="objectives[][weightage]" class="form-control w-25" required>
                                        @foreach ([10, 15] as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="objectives[][target]" class="form-control"
                                        placeholder="Target" required />
                                    <x-ui.button variant="danger" type="button"
                                        class="btn-sm remove-row">Remove</x-ui.button>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="mt-2">
                        <button type="button" id="add-objective" class="btn btn-outline-primary btn-sm">Add
                            Objective</button>
                        <small class="text-muted ms-2">Provide 2-3 departmental objectives; total weightage must equal
                            30%.</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="financial_year" class="form-label">Financial Year</label>
                    <select name="financial_year" id="financial_year" class="form-control" required>
                        @foreach ((array) $years as $y)
                            <option value="{{ $y }}"
                                {{ old('financial_year', $objective->financial_year ?? null) == $y || (empty(old('financial_year', $objective->financial_year ?? null)) && $loop->first) ? 'selected' : '' }}>
                                {{ $y }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            <x-ui.button variant="success" type="submit">{{ isset($objective) ? 'Update' : 'Create' }}</x-ui.button>
            <x-ui.button variant="secondary" href="{{ route('team.objectives.index') }}">Cancel</x-ui.button>
        </form>
        <script>
            (function() {
                const maxRows = 3;
                const container = document.getElementById('objective-rows');
                const departmentSelect = document.getElementById('department_id');
                const certifyingAuthorityInput = document.getElementById('certifying_authority');
                const optionsEndpoint = "{{ route('departmental-objective-masters.options') }}";
                let objectiveOptions = `
                    @foreach ($departmentalObjectiveOptions ?? collect() as $opt)
                        <option value="{{ $opt }}">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}</option>
                    @endforeach
                `;

                function sentenceCase(value) {
                    const lower = String(value || '').toLowerCase();
                    return lower.charAt(0).toUpperCase() + lower.slice(1);
                }

                function escapeHtml(value) {
                    return String(value)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function refreshAllDescriptionSelects() {
                    container.querySelectorAll('select[name="objectives[][description]"]').forEach(function(select) {
                        const current = select.value;
                        select.innerHTML = '<option value="">Select departmental objective</option>' +
                            objectiveOptions;
                        if (current && Array.from(select.options).some(o => o.value === current)) {
                            select.value = current;
                        }
                    });
                }

                function fetchDepartmentalOptions() {
                    const deptId = departmentSelect ? departmentSelect.value : '';
                    if (!deptId) {
                        return;
                    }

                    if (certifyingAuthorityInput && !String(certifyingAuthorityInput.value || '').trim()) {
                        const selected = departmentSelect.options[departmentSelect.selectedIndex];
                        certifyingAuthorityInput.value = selected ? selected.text : '';
                    }

                    fetch(optionsEndpoint + '?department_id=' + encodeURIComponent(deptId), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(r => r.json()).then(data => {
                        const list = Array.isArray(data.options) ? data.options : [];
                        objectiveOptions = list.map(item => '<option value="' + escapeHtml(item) + '">' +
                            escapeHtml(sentenceCase(item)) + '</option>').join('');
                        refreshAllDescriptionSelects();
                    }).catch(() => {
                        // keep existing options on failure
                    });
                }

                document.getElementById('add-objective').addEventListener('click', function() {
                    const rows = container.querySelectorAll('.objective-row');
                    if (rows.length >= maxRows) return alert('Maximum ' + maxRows + ' objectives allowed');
                    const el = rows[0].cloneNode(true);
                    el.querySelectorAll('input').forEach(i => i.value = '');
                    const select = el.querySelector('select[name="objectives[][description]"]');
                    if (select) {
                        select.innerHTML = '<option value="">Select departmental objective</option>' +
                            objectiveOptions;
                        select.value = '';
                    }
                    container.appendChild(el);
                });

                if (departmentSelect) {
                    departmentSelect.addEventListener('change', fetchDepartmentalOptions);
                    if (departmentSelect.value) {
                        fetchDepartmentalOptions();
                    }
                }

                container.addEventListener('click', function(e) {
                    if (!e.target.matches('.remove-row')) return;
                    const rows = container.querySelectorAll('.objective-row');
                    if (rows.length <= 1) return; // keep at least one in UI
                    e.target.closest('.objective-row').remove();
                });
            })();
        </script>
    </div>
@endsection
