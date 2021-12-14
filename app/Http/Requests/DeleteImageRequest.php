<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class DeleteImageRequest extends FormRequest
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
            'picture' => 'required | exists:images'
        ];
    }

    public function failedValidation(Validator $error){
        throw new HttpResponseException(response()->error( $error->errors(),400));
    }

    public function messages(){
        return [
            'picture.exists' => 'Image does not exist in database!',
        ];
    }
}
