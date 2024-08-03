<?php

namespace App\Http\Requests\Submissions;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class FetchMartyrsFormRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return $this->commonRules() + [
                'country' => 'nullable',
                'reason' => 'nullable',
                'period_from' => 'nullable',
                'period_to' => 'nullable',
            ];
    }
}
