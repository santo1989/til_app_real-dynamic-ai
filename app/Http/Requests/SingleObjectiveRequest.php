<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\AllowedWeightage;

class SingleObjectiveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'user_id' => 'sometimes|required|exists:users,id',
            'department_id' => 'sometimes|nullable|exists:departments,id',
            'type' => 'required|string|in:individual,departmental',
            'description' => 'required|string',
            'weightage' => ['required', 'integer', new AllowedWeightage()],
            'target' => 'required|string',
            'status' => 'nullable|string',
            'financial_year' => 'required|string',
        ];

        return $rules;
    }
}
