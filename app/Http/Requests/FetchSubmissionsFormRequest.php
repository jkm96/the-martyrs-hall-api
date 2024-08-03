<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class FetchSubmissionsFormRequest extends BaseFormRequest
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
                'period_from' => 'nullable',
                'is_subscribed' => 'nullable',
                'period_to' => 'nullable',
            ];
    }
}
