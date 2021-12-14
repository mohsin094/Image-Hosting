<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistrationRequest extends FormRequest
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
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password'=> 'required|confirmed',
            'age' => 'required|integer',
            //'picture' => 'mimes:jpg,png,jpeg|max:5000'
            'picture' => 'required'
        ];
    }

    public function failedValidation(Validator $error){
        throw new HttpResponseException(response()->error( $error->errors(),400));
    }

    public function messages(){
        return [
            'email.unique' => 'Invalid email!',
        ];
    }
}
