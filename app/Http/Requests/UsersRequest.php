<?php

namespace App\Http\Requests;

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
            'description'   => 'required|min:3|max:100',
            'username'      => 'required|min:3|max:100',
            'email'         => 'required|email',
            'roles'       => 'required',
        ];
    }

    /**
     * Set custom messages for validator errors
     * @return array
     */
    public function messages()
    {
        return [
            'description.required'      => 'El campo descripcion es obligatorio',
            'description.min'           => 'La cantidad minima de caracteres es: 3',
            'description.max'           => 'La cantidad maxima de caracteres es: 100',
            'email.required'            => 'El correo electronico es obligatorio',
            'email.email'               => 'La direcccion correo electronico no es valida',
            'roles.required'          => 'Debe asignar al menos un rol'
        ];
    }
}