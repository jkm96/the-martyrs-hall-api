<?php

namespace App\Http\Requests\Submissions;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'name' => 'required|string|unique:submissions',
            'birth_date' => 'required|date',
            'death_date' => 'required|date',
            'location' => 'required|string',
            'contributions' => 'required|string',
            'death_reason' => 'required|string',
            'profile_picture' => 'file|mimes:jpeg,png,jpg|max:2048'
        ];
    }

    /**
     * @param Validator $validator
     * @return mixed
     */
    public function failedValidation(Validator $validator)
    {
        $errorMessages = implode('. ', $validator->errors()->all());
        throw new HttpResponseException(ResponseHelpers::ConvertToJsonResponseWrapper(
            $validator->errors(),
            "Validation errors: " . $errorMessages,
            422
        ));
    }
}
