<?php

namespace App\Http\Requests\Auth;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserLoginRequest extends BaseFormRequest
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
            'username' => 'required|min:4',
            'password' => 'required|min:6',
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
