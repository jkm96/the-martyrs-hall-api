<?php

namespace App\Http\Requests\Martyrs;

use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Contracts\Validation\Validator;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LightMartyrsCandleRequest extends FormRequest
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
            'martyr_id' => 'required',
            'tmh_ip_address' => 'nullable'
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
