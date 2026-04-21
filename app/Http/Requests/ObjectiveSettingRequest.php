<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FinancialYear;
use Carbon\Carbon;

class ObjectiveSettingRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Support both bulk submission (objectives array) and single-object forms.
        if ($this->has('objectives')) {
            return [
                'objectives' => 'required|array|min:3|max:9',
                'objectives.*.type' => 'required|string|in:departmental,individual',
                'objectives.*.description' => 'required|string',
                'objectives.*.weightage' => 'required|integer',
                'objectives.*.target' => 'required|string',
                'date_of_setting' => 'nullable|date',
                // optional IDP payload that may accompany objective submissions
                'idp' => 'nullable|array',
                'idp.skill_area' => 'nullable|string|max:255',
                'idp.description' => 'nullable|string',
                'idp.expected_benefits' => 'nullable|string',
                'idp.action_plan' => 'nullable|string',
                'idp.resources_required' => 'nullable|string',
                'idp.review_date' => 'nullable|date',
                'idp.status' => 'nullable|string',
            ];
        }

        // Single objective submission rules
        return [
            'type' => 'required|string|in:departmental,individual',
            'description' => 'required|string',
            'weightage' => 'required|integer',
            'target' => 'required|string',
            'department_id' => 'nullable|exists:departments,id',
            'financial_year' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $individualAllowed = array_map('intval', (array) config('appraisal.individual_allowed_weightages', [10, 15, 20, 25]));
            $departmentalAllowed = array_map('intval', (array) config('appraisal.departmental_allowed_weightages', [10, 15]));

            // Only apply aggregate checks for bulk submissions
            if ($this->has('objectives')) {
                $objectives = $this->input('objectives', []);

                foreach ($objectives as $idx => $o) {
                    $type = (string) ($o['type'] ?? '');
                    $weight = (int) ($o['weightage'] ?? 0);
                    if ($type === 'individual' && !in_array($weight, $individualAllowed, true)) {
                        $v->errors()->add("objectives.{$idx}.weightage", 'Individual objective weightage must be one of: ' . implode(', ', $individualAllowed) . ' %.');
                    }
                    if ($type === 'departmental' && !in_array($weight, $departmentalAllowed, true)) {
                        $v->errors()->add("objectives.{$idx}.weightage", 'Departmental objective weightage must be one of: ' . implode(', ', $departmentalAllowed) . ' %.');
                    }
                }

                $total = array_sum(array_column($objectives, 'weightage'));

                $deptTotal = 0;
                $indCount = 0;
                $deptCount = 0;
                foreach ($objectives as $o) {
                    if (($o['type'] ?? '') === 'departmental') {
                        $deptTotal += (int)($o['weightage'] ?? 0);
                        $deptCount++;
                    } else {
                        $indCount++;
                    }
                }
                // If there are departmental objectives present, keep previous behaviour:
                // total weight of all objectives must equal 100% and departmental portion must match configured departmental total
                if ($deptCount > 0) {
                    $configuredDeptTotal = config('appraisal.departmental_total', 30);
                    if ($deptTotal !== $configuredDeptTotal) {
                        $v->errors()->add('objectives', "Total departmental (team) objectives must total {$configuredDeptTotal}% of overall weightage.");
                    }

                    if ($total !== 100) {
                        $v->errors()->add('objectives', 'Total weightage of all objectives must equal 100%.');
                    }

                    // Enforce departmental objectives count: 2-3 when departmental objectives are present
                    $deptMin = config('appraisal.departmental_min_count', 2);
                    $deptMax = config('appraisal.departmental_max_count', 3);
                    if ($deptCount < $deptMin || $deptCount > $deptMax) {
                        $v->errors()->add('objectives', "Departmental/Team objectives must be between {$deptMin} and {$deptMax} items.");
                    }

                    $indMin = config('appraisal.individual_min', 3);
                    $indMax = config('appraisal.individual_max', 6);
                    if ($indCount < $indMin || $indCount > $indMax) {
                        $v->errors()->add('objectives', "Individual objectives must be between {$indMin} and {$indMax}.");
                    }
                } else {
                    // Individual-only submissions: enforce configured total (default 70%) and individual count between 3 and 6
                    $requiredTotal = config('appraisal.individual_total', 70);
                    if ($total !== $requiredTotal) {
                        $v->errors()->add('objectives', "Total weightage of individual objectives must equal {$requiredTotal}%.");
                    }

                    $indMin = config('appraisal.individual_min', 3);
                    $indMax = config('appraisal.individual_max', 6);
                    if ($indCount < $indMin || $indCount > $indMax) {
                        $v->errors()->add('objectives', "Individual objectives must be between {$indMin} and {$indMax}.");
                    }
                }

                // Validate date_of_setting is within the first month of the financial year when provided
                $dos = $this->input('date_of_setting');
                if ($dos) {
                    $fyLabel = $this->input('financial_year');
                    $fy = null;
                    if ($fyLabel) {
                        $fy = FinancialYear::where('label', $fyLabel)->first();
                    }

                    if (!$fy) {
                        // fallback to active FY
                        $fy = FinancialYear::active();
                    }

                    if ($fy && $fy->start_date) {
                        $start = Carbon::parse($fy->start_date)->startOfDay();
                        $firstMonthEnd = $start->copy()->addMonth()->endOfDay();
                        $given = Carbon::parse($dos);
                        if (!$given->between($start, $firstMonthEnd)) {
                            $v->errors()->add('date_of_setting', 'Date of setting must fall within the first month of the selected financial year.');
                        }

                        // optional extra rule: if setting against the active FY, ensure date is not in the future
                        if (($fyLabel && $fyLabel === FinancialYear::getActiveName()) && $given->greaterThan(now())) {
                            $v->errors()->add('date_of_setting', 'Date of setting for the active financial year cannot be in the future.');
                        }
                    }
                }

                // Ensure each objective has a non-null financial_year. If missing, attempt to populate
                // from the active FinancialYear. If there's no active FY and any objective is missing the
                // financial_year, fail validation.
                $activeFyLabel = FinancialYear::getActiveName();
                $missingFy = false;
                // Work on a copy of the request data so we can set nested values properly
                $all = $this->all();
                foreach ($objectives as $idx => $o) {
                    if (empty($o['financial_year'])) {
                        if ($activeFyLabel) {
                            if (!isset($all['objectives'][$idx])) {
                                $all['objectives'][$idx] = [];
                            }
                            $all['objectives'][$idx]['financial_year'] = $activeFyLabel;
                        } else {
                            $missingFy = true;
                        }
                    }
                }

                // If we injected values, replace the request input so controllers will receive them
                if (!$missingFy && isset($all['objectives'])) {
                    $this->replace($all);
                }

                if ($missingFy) {
                    $v->errors()->add('objectives', 'No active financial year found. Objective setting is locked until Admin, HR Admin, or Board activates a financial year.');
                }
            } else {
                $type = (string) $this->input('type');
                $weight = (int) $this->input('weightage');
                if ($type === 'individual' && !in_array($weight, $individualAllowed, true)) {
                    $v->errors()->add('weightage', 'Individual objective weightage must be one of: ' . implode(', ', $individualAllowed) . ' %.');
                }
                if ($type === 'departmental' && !in_array($weight, $departmentalAllowed, true)) {
                    $v->errors()->add('weightage', 'Departmental objective weightage must be one of: ' . implode(', ', $departmentalAllowed) . ' %.');
                }
            }
        });
    }

    /**
     * Prepare the data for validation. If an active financial year exists, inject its label
     * into any objective entries that are missing the `financial_year` key. This ensures
     * subsequent validation rules operate against a complete payload.
     */
    protected function prepareForValidation()
    {
        if (!$this->has('objectives')) {
            return;
        }

        $activeFyLabel = FinancialYear::getActiveName();
        if (!$activeFyLabel) {
            // Nothing to inject
            return;
        }

        $all = $this->all();
        foreach ($all['objectives'] as $idx => $obj) {
            if (empty($obj['financial_year'])) {
                $all['objectives'][$idx]['financial_year'] = $activeFyLabel;
            }
        }

        $this->replace($all);
    }
}
