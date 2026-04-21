@extends('layouts.app')
@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Set Departmental Objectives</h5>
            <form method="POST" action="{{ route('objectives.board.set') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Department</label>
                    <select id="department_id" name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="certifying_authority">Name of the Certifying Authority /
                        Department</label>
                    <input type="text" id="certifying_authority" name="certifying_authority" class="form-control"
                        value="{{ old('certifying_authority') }}" placeholder="Leave empty to use selected department name">
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Objective</th>
                            <th>Weightage</th>
                            <th>Target</th>
                            <th>Remove</th>
                        </tr>
                    </thead>
                    <tbody id="objective-rows">
                        @for ($i = 0; $i < 2; $i++)
                            <tr>
                                <td>
                                    <select name="objectives[{{ $i }}][description]" class="form-control"
                                        required>
                                        <option value="">Select departmental objective</option>
                                        @foreach ($departmentalObjectiveOptions ?? collect() as $opt)
                                            <option value="{{ $opt }}">
                                                {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="objectives[{{ $i }}][weightage]" class="form-control" required>
                                        @foreach ([10, 15] as $w)
                                            <option value="{{ $w }}">{{ $w }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        class="form-control" required></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button>
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <button type="button" id="add-row" class="btn btn-outline-secondary btn-sm">Add Objective</button>
                <x-ui.button variant="primary" type="submit">Set Objectives</x-ui.button>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const maxRows = 3;
            const rows = document.getElementById('objective-rows');
            const departmentSelect = document.getElementById('department_id');
            const certifyingAuthorityInput = document.getElementById('certifying_authority');
            const optionsEndpoint = "{{ route('departmental-objective-masters.options') }}";
            let idx = rows.querySelectorAll('tr').length;
            let optionHtml = `
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
                rows.querySelectorAll('select[name$="[description]"]').forEach(function(select) {
                    const current = select.value;
                    select.innerHTML = '<option value="">Select departmental objective</option>' + optionHtml;
                    if (current && Array.from(select.options).some(o => o.value === current)) {
                        select.value = current;
                    }
                });
            }

            function fetchDepartmentalOptions() {
                if (!departmentSelect || !departmentSelect.value) {
                    return;
                }

                if (certifyingAuthorityInput && !String(certifyingAuthorityInput.value || '').trim()) {
                    const selected = departmentSelect.options[departmentSelect.selectedIndex];
                    certifyingAuthorityInput.value = selected ? selected.text : '';
                }

                fetch(optionsEndpoint + '?department_id=' + encodeURIComponent(departmentSelect.value), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(r => r.json()).then(data => {
                    const list = Array.isArray(data.options) ? data.options : [];
                    optionHtml = list.map(item => '<option value="' + escapeHtml(item) + '">' + escapeHtml(
                        sentenceCase(item)) + '</option>').join('');
                    refreshAllDescriptionSelects();
                }).catch(() => {
                    // keep existing options on failure
                });
            }

            document.getElementById('add-row').addEventListener('click', function() {
                const count = rows.querySelectorAll('tr').length;
                if (count >= maxRows) {
                    alert('Maximum 3 objectives are allowed.');
                    return;
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <select name="objectives[${idx}][description]" class="form-control" required>
                            <option value="">Select departmental objective</option>
                            ${optionHtml}
                        </select>
                    </td>
                    <td>
                        <select name="objectives[${idx}][weightage]" class="form-control" required>
                            <option value="10">10</option>
                            <option value="15">15</option>
                        </select>
                    </td>
                    <td><input type="text" name="objectives[${idx}][target]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger remove-row">Remove</button></td>
                `;
                rows.appendChild(tr);
                idx++;
            });

            if (departmentSelect) {
                departmentSelect.addEventListener('change', fetchDepartmentalOptions);
                if (departmentSelect.value) {
                    fetchDepartmentalOptions();
                }
            }

            rows.addEventListener('click', function(e) {
                if (!e.target.classList.contains('remove-row')) return;
                const count = rows.querySelectorAll('tr').length;
                if (count <= 2) {
                    alert('Minimum 2 objectives are required.');
                    return;
                }
                e.target.closest('tr').remove();
            });
        })();
    </script>
@endsection
