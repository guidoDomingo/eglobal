<?php

namespace App\Http\Requests\TerminalInteraction;

Use App\Http\Requests\Request;

class UsersRequest extends Request
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
            'description' => 'required|min:3|max:100',
            'username' => 'required|min:3|max:100',
            'password' => 'required',
            'email' => 'email',
            'role_id' => 'required',
            'branch_id' => 'required',
        ];
    }

    /**
     * Set custom messages for validator errors
     * @return array
     */
    public function messages()
    {
        return [
            'username.required' => 'El campo nombre de usuario es obligatorio.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'description.required' => 'El campo descripción es obligatorio.',
            'description.min' => 'La cantidad minima de caracteres es: 3.',
            'description.max' => 'La cantidad maxima de caracteres es: 100.',
            //'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'La direccción correo electrónico no es válida.',
            'role_id.required' => 'Seleccionar rol.',
            'branch_id.required' => 'Seleccionar sucursal.'
        ];
    }
}