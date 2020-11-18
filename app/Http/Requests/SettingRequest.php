<?php

namespace App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
            'payment_multiple' => 'integer|required',
            'bank_user' => 'required|integer|exists:users,id',
            'commission_user' => 'required|integer|exists:users,id',
            'gaming_user' => 'required|integer|exists:users,id',
        ];
    }



    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();

        $transformed = [];

        foreach ($errors as $field => $message) {
            $transformed[] = [
                'field' => $field,
                'message' => $message[0]
            ];
        }

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message'=> 'VALIDATION_ERROR',
            'data' => $transformed,
        ],Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
