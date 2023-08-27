<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
//use Illuminate\Http\Request;

class LoginRequest extends Request
{
    /**
     * Determine if the user is authorize to make this request
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to this request
     * @return array
     */
    public function rules()
    {
        return [
            'username'   => 'required',
            'password'      => 'required',
        ];
    }

    /**
     * Set custom messages for validator errors
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'El campo Usuario es obligatorio.',
            'password.required' => 'El campo Contraseña es obligatorio.'
        ];
    }
}