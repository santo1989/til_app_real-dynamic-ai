@php
    /** @var \App\Models\User $employee */
    $employee = $employee ?? null;
    $fyLabel = $fyLabel ?? ($activeFYLabel ?? null);
    $deptObjectives = $deptObjectives ?? collect();
    $individualObjectives = $individualObjectives ?? collect();
    $idps = $idps ?? collect();
    $appraisal = $appraisal ?? null;
    $mode = $mode ?? 'read';
    $readOnly = (bool) ($readOnly ?? true);

    $ratings = is_string($appraisal?->ratings) ? json_decode($appraisal->ratings, true) : (is_array($appraisal?->ratings) ? $appraisal->ratings : []);
    $scores = $ratings['scores'] ?? [];
    $midtermNotes = $ratings['notes'] ?? [];

    $computedYearTotal = 0.0;
    foreach ($deptObjectives as $d) {
        $ta = is_null($d->final_score) ? 0 : (float) $d->final_score;
        $w = (float) ($d->weightage ?? 0);
        $computedYearTotal += ($w * $ta / 100);
    }
    foreach ($individualObjectives as $o) {
        $ta = (float) (old('scores.' . $o->id, $scores[$o->id] ?? 0) ?? 0);
        $w = (float) ($o->weightage ?? 0);
        $computedYearTotal += ($w * $ta / 100);
    }

    $displayRating = $computedYearTotal >= 95 ? 'Outstanding' : ($computedYearTotal >= 85 ? 'Very Good' : ($computedYearTotal >= 70 ? 'Good' : 'Below'));
@endphp

<div class="yearend-scope">
<div class="form-container">
    <h2 class="section-title">Year End Appraisal: {{ $fyLabel }}</h2>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Name of the employee</label>
                <input type="text" class="form-control" value="{{ $employee?->name }}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Designation</label>
                <input type="text" class="form-control" value="{{ $employee?->designation }}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Date of joining</label>
                <input type="text" class="form-control"
                    value="{{ $employee?->date_of_joining ? $employee->date_of_joining->format('d F Y') : '' }}" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label">Employee ID</label>
                <input type="text" class="form-control" value="{{ $employee?->employee_id }}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control" value="{{ $employee?->department?->name }}" readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Tenure in current role</label>
                <input type="text" class="form-control" value="{{ $employee?->tenure_in_current_role }}" readonly>
            </div>
        </div>
        <div class="col-12">
            <div class="mb-3">
                <label class="form-label">Date of Year End Review</label>
                <input type="text" class="form-control" value="{{ now()->format('d F Y') }}" readonly>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="yearend-table">
            <thead>
                <tr>
                    <th>Sl. #</th>
                    <th>Objectives/Action Plans</th>
                    <th>Timeline</th>
                    <th>Weightage %</th>
                    <th>Mid Term Comment</th>
                    <th>Emp. Self Score</th>
                    <th>Line Manager Score (TA)</th>
                    <th>Final Score (W * TA / 100)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="table-secondary">
                    <td colspan="8"><strong>Departmental/Team Objectives</strong></td>
                </tr>

                @php $sl = 1; @endphp
                @foreach ($deptObjectives as $deptObj)
                    @php
                        $w = (float) ($deptObj->weightage ?? 0);
                        $yearTa = is_null($deptObj->final_score) ? null : (float) $deptObj->final_score;
                        $yearScore = $yearTa === null ? null : ($w * $yearTa / 100);
                        $deptMidComment = '';
                        if (isset($deptObj->midterm_notes) && is_string($deptObj->midterm_notes)) {
                            $deptMidComment = trim($deptObj->midterm_notes);
                        }
                    @endphp
                    <tr>
                        <td>{{ $sl++ }}</td>
                        <td>{{ $deptObj->master?->title }}</td>
                        <td>{{ $deptObj->timeline }}</td>
                        <td>{{ $deptObj->weightage }}</td>
                        <td>{{ $deptMidComment !== '' ? $deptMidComment : '—' }}</td>
                        <td class="text-center text-primary fw-bold">—</td>
                        <td>{{ $yearTa === null ? '—' : rtrim(rtrim(number_format($yearTa, 2), '0'), '.') }}</td>
                        <td class="dept-score" data-score="{{ $yearScore ?? 0 }}">{{ $yearScore === null ? '—' : number_format($yearScore, 2) }}</td>
                    </tr>
                @endforeach

                <tr class="table-secondary">
                    <td colspan="8"><strong>Individual Objectives</strong></td>
                </tr>

                @php $sl = 1; @endphp
                @foreach ($individualObjectives as $obj)
                    @php
                        $w = (float) ($obj->weightage ?? 0);
                        $yearTaValue = old('scores.' . $obj->id, $scores[$obj->id] ?? null);
                        $yearTa = is_null($yearTaValue) ? null : (float) $yearTaValue;
                        $yearScore = $yearTa === null ? null : ($w * $yearTa / 100);
                        $midNote = $midtermNotes[$obj->id] ?? null;
                        $midDisplay = is_string($midNote) ? trim($midNote) : '';
                    @endphp
                    <tr>
                        <td>{{ $sl++ }}</td>
                        <td>{{ $obj->description }}</td>
                        <td>{{ $obj->timeline }}</td>
                        <td>{{ $obj->weightage }}</td>
                        <td>{{ $midDisplay !== '' ? $midDisplay : '—' }}</td>
                        <td class="text-center text-primary fw-bold">{{ $obj->employee_score !== null ? rtrim(rtrim(number_format($obj->employee_score, 2), '0'), '.') : '—' }}</td>
                        <td>
                            @if ($mode === 'lm')
                                <input type="number" name="scores[{{ $obj->id }}]" class="form-control ta-input d-print-none"
                                    data-weightage="{{ $obj->weightage }}"
                                    value="{{ old('scores.' . $obj->id, $scores[$obj->id] ?? '') }}" min="0" max="100"
                                    @if($readOnly) disabled @endif>
                                <span class="print-value d-none d-print-inline">{{ old('scores.' . $obj->id, $scores[$obj->id] ?? '') }}</span>
                            @else
                                {{ $yearTa === null ? '—' : rtrim(rtrim(number_format($yearTa, 2), '0'), '.') }}
                            @endif
                        </td>
                        <td class="ind-score">{{ $yearScore === null ? '—' : number_format($yearScore, 2) }}</td>
                    </tr>
                @endforeach

                <tr class="table-info">
                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                    <td><strong>100</strong></td>
                    <td colspan="2"></td>
                    <td></td>
                    <td id="year-total"><strong>{{ number_format($computedYearTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h3 class="section-title">Performance Rating</h3>
        <p>Based on total score:</p>
        <ul>
            <li>Outstanding: &gt;=95 (min 80% per individual target, innovation in at least one)</li>
            <li>Very Good: 85-94</li>
            <li>Good: 60-84 (min 60% per target)</li>
            <li>Below: &lt;60 (Performance Improvement Plan)</li>
        </ul>
        <div class="mb-3">
            <label class="form-label">Calculated Rating</label>
            <input type="text" class="form-control" id="rating" value="{{ $displayRating }}" readonly>
        </div>
    </div>

    <h3 class="section-title mt-4">Year End Review on IDP Progress</h3>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Skill Area</th>
                    <th>Development Objective</th>
                    <th>Expected Benefits</th>
                    <th>Development Action Plan</th>
                    <th>Resources Required</th>
                    <th>Deadline/ Timeline</th>
                    <th>Attainment of Individual Development Plan: Yes / No</th>
                    <th>If yes, whether there is visible demonstration of use of the learning</th>
                    <th class="table-warning">HR Input</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($idps as $idx => $idp)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>{{ $idp->skill_area }}</td>
                        <td>{{ $idp->description }}</td>
                        <td>{{ $idp->expected_benefits }}</td>
                        <td>{{ $idp->action_plan }}</td>
                        <td>{{ $idp->resources_required }}</td>
                        <td>{{ $idp->review_date ?? '' }}</td>
                        <td>
                            @if (is_null($idp->attainment))
                                —
                            @else
                                {{ $idp->attainment ? 'Yes' : 'No' }}
                            @endif
                        </td>
                        <td>{{ $idp->visible_demonstration }}</td>
                        <td>{{ $idp->hr_input }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted italic">No IDP data found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h3 class="section-title">Overall Comments</h3>

        <div class="mb-3">
            <label class="form-label">Immediate Line Manager’s Comment</label>
            @if ($mode === 'lm')
                <textarea name="manager_comment" class="form-control" rows="3"
                    @if($readOnly) disabled @endif>{{ old('manager_comment', $appraisal?->action_points) }}</textarea>
            @else
                <textarea class="form-control" rows="3" readonly>{{ $appraisal?->action_points }}</textarea>
            @endif
        </div>

        <div class="mb-3">
            <label class="form-label">Line Manager’s Supervisor’s Comment</label>
            @if ($mode === 'hr')
                <textarea name="hr_comment" class="form-control" rows="3"
                    @if($readOnly || !empty(trim((string) ($appraisal?->supervisor_comments ?? '')))) disabled @endif>{{ old('hr_comment', $appraisal?->supervisor_comments) }}</textarea>
            @else
                <textarea class="form-control" rows="3" readonly>{{ $appraisal?->supervisor_comments }}</textarea>
            @endif
        </div>
    </div>
</div>

<style>
    .yearend-scope .form-container {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
    }
    .yearend-scope .section-title {
        color: #1a6b3b;
        border-bottom: 2px solid #1a6b3b;
        padding-bottom: 8px;
        margin-bottom: 20px;
        font-weight: 700;
    }
    .yearend-scope .table th {
        background-color: #e9f5ee;
        color: #1a6b3b;
    }
</style>

<script>
(function() {
    function initAppraisalScoring() {
        const inputs = document.querySelectorAll('.ta-input');
        const totalEl = document.getElementById('year-total');
        const ratingEl = document.getElementById('rating');

        function calculateTotal() {
            let total = 0;
            
            document.querySelectorAll('.dept-score').forEach(td => {
                total += parseFloat(td.getAttribute('data-score') || 0);
            });

            inputs.forEach(input => {
                let ta = parseFloat(input.value || 0);
                let w = parseFloat(input.getAttribute('data-weightage') || 0);
                let score = (w * ta) / 100;
                
                let scoreTd = input.closest('tr').querySelector('.ind-score');
                if (scoreTd) {
                    scoreTd.innerHTML = input.value === '' ? '—' : score.toFixed(2);
                }
                
                // also update a print value span
                let printSpan = input.closest('td').querySelector('.print-value');
                if (printSpan) {
                    printSpan.innerHTML = input.value;
                }
                
                total += score;
            });

            if (totalEl) {
                totalEl.innerHTML = '<strong>' + total.toFixed(2) + '</strong>';
            }

            if (ratingEl) {
                let rating = 'Below';
                if (total >= 95) rating = 'Outstanding';
                else if (total >= 85) rating = 'Very Good';
                else if (total >= 70) rating = 'Good';
                ratingEl.value = rating;
            }
        }

        inputs.forEach(input => {
            // remove old listener if init is called multiple times
            input.removeEventListener('input', calculateTotal);
            input.addEventListener('input', calculateTotal);
        });
        
        // Run once on load just in case there are pre-filled values
        if (inputs.length > 0) {
            calculateTotal();
        }
    }

    // Run immediately
    initAppraisalScoring();

    // Support for Turbo/Livewire navigations
    document.addEventListener('turbo:load', initAppraisalScoring);
    document.addEventListener('livewire:navigated', initAppraisalScoring);
})();
</script>
</div>
