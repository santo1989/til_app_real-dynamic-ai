@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-body">
            <h5>Set Objectives for: {{ $employee->name }}</h5>
            <form method="POST" action="{{ route('objectives.set_for_user', $employee->id) }}">
                @csrf
                @include('components.alert')

                <div class="mb-3">
                    <strong>Employee:</strong> {{ $employee->name }}<br>
                    <strong>Department:</strong> {{ $employee->department->name ?? 'N/A' }}<br>
                    <strong>Role:</strong> {{ $employee->role }}<br>
                    <strong>Financial Year:</strong> {{ $activeFY }}
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Weightage (%)</th>
                            <th>Target</th>
                        </tr>
                    </thead>
                    <tbody id="objectives-body">
                        @forelse($existingObjectives as $i => $obj)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <input type="hidden" name="objectives[{{ $i }}][type]" value="individual" />
                                    <select name="objectives[{{ $i }}][description]" class="form-control"
                                        required>
                                        <option value="">Select objective</option>
                                        @foreach ($individualObjectiveOptions ?? collect() as $opt)
                                            @php
                                                $oldValue = old("objectives.{$i}.description");
                                                $currentValue = $oldValue ?? $obj->description;
                                                // Normalize both for safe comparison: trim, lowercase, remove extra spaces
                                                $isSelected =
                                                    strtolower(trim(preg_replace('/\s+/', ' ', $currentValue))) ===
                                                    strtolower(trim(preg_replace('/\s+/', ' ', $opt)));
                                            @endphp
                                            <option value="{{ $opt }}" {{ $isSelected ? 'selected' : '' }}>
                                                {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="objectives[{{ $i }}][weightage]" class="form-control"
                                        required>
                                        @foreach ([10, 15, 20, 25] as $w)
                                            @php
                                                $oldWeight = old("objectives.{$i}.weightage");
                                                $currentWeight = $oldWeight ?? $obj->weightage;
                                            @endphp
                                            <option value="{{ $w }}"
                                                {{ (string) $currentWeight === (string) $w ? 'selected' : '' }}>
                                                {{ $w }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="objectives[{{ $i }}][target]"
                                        value="{{ old("objectives.{$i}.target") ?? $obj->target }}" class="form-control"
                                        required /></td>
                            </tr>
                        @empty
                            @php
                                // When no existing objectives, check if form had errors and render rows from old() data
                                $oldObjectives = old('objectives', []);
                                $objectivesToRender = !empty($oldObjectives) ? $oldObjectives : [[], [], []]; // Default 3 empty rows
                            @endphp
                            @foreach ($objectivesToRender as $i => $objData)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>
                                        <input type="hidden" name="objectives[{{ $i }}][type]"
                                            value="individual" />
                                        <select name="objectives[{{ $i }}][description]" class="form-control"
                                            required>
                                            <option value="">Select objective</option>
                                            @foreach ($individualObjectiveOptions ?? collect() as $opt)
                                                @php
                                                    $selectedValue = $objData['description'] ?? '';
                                                    $isSelected =
                                                        !empty($selectedValue) &&
                                                        strtolower(trim(preg_replace('/\s+/', ' ', $selectedValue))) ===
                                                            strtolower(trim(preg_replace('/\s+/', ' ', $opt)));
                                                @endphp
                                                <option value="{{ $opt }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="objectives[{{ $i }}][weightage]" class="form-control"
                                            required>
                                            @foreach ([10, 15, 20, 25] as $w)
                                                @php
                                                    $w_val = isset($objData['weightage'])
                                                        ? (int) $objData['weightage']
                                                        : 0;
                                                @endphp
                                                <option value="{{ $w }}"
                                                    {{ (int) $w_val === (int) $w ? 'selected' : '' }}>
                                                    {{ $w }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="objectives[{{ $i }}][target]"
                                            value="{{ $objData['target'] ?? '' }}" class="form-control" required /></td>
                                </tr>
                            @endforeach
                        @endforelse
                    </tbody>
                </table>

                <x-ui.button variant="secondary" type="button" id="add-row" class="btn-sm">Add Objective</x-ui.button>
                <x-ui.button variant="primary" type="submit">Save Objectives</x-ui.button>
                <x-ui.button variant="secondary" href="{{ route('objectives.team') }}">Cancel</x-ui.button>

                <div class="mt-2 text-muted">
                    <small>Total objectives: 2–6 | Weightages must sum to 100% | Allowed: 10%, 15%, 20%, 25%</small>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function() {
            let idx = $('#objectives-body tr').filter(function() {
                return $(this).find('select[name*="[description]"]').length > 0;
            }).length;
            const objectiveOptions = `
                @foreach ($individualObjectiveOptions ?? collect() as $opt)
                    <option value="{{ $opt }}">{{ \Illuminate\Support\Str::ucfirst(\Illuminate\Support\Str::lower($opt)) }}</option>
                @endforeach
            `;
            $('#add-row').on('click', function() {
                $('#objectives-body').append(`
                    <tr>
                        <td>${idx + 1}</td>
                        <td>
                            <input type="hidden" name="objectives[${idx}][type]" value="individual" />
                            <select name="objectives[${idx}][description]" class="form-control" required>
                                <option value="">Select objective</option>
                                ${objectiveOptions}
                            </select>
                        </td>
                        <td>
                            <select name="objectives[${idx}][weightage]" class="form-control" required>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="25">25</option>
                            </select>
                        </td>
                        <td><input type="text" name="objectives[${idx}][target]" class="form-control" required /></td>
                    </tr>
                `);
                idx++;
            });
        });
    </script>
@endsection
