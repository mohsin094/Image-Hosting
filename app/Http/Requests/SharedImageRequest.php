<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SharedImageRequest extends FormRequest
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
            "shared_email"=>"required|exists:users,email",
            "image_id"=>"required|exists:images,id"
        ];
    }

    public function failedValidation(Validator $error){
        throw new HttpResponseException(response()->error( $error->errors(),400));
    }
    
    public function messages(){
        return [
            'shared_email.exists' => 'Email does not exist!',
            'image_id.exists' => 'Image does not exixt!'
        ];
    }
}
