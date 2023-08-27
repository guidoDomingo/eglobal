<?php namespace App\Http\Requests;

use Illuminate\Validation\ValidationServiceProvider;
use App\Http\Requests\Request;

class ResetPasswordRequest extends Request
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
            'id'        => 'required|integer',
            'code'      => 'required',
            'password'  => 'required|alpha_num|between:5,15|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    /**
     * Set message for broken rules
     * @return array
     */
    public function messages()
    {
        return [
            'id.required'       => 'Usuario no encontrado',
            'id.integer'        => 'Usuario invalido',
            'code.required'     => 'Codigo de activacion no encontrado',
            'password.required' => 'Debe escribir su nueva contraseña',
            'password.alpha_num'=> 'La contraseña debe contener valores alfanumericos',
            'password.min'      => 'La contraseña debe contener al menos 5 caracteres',
            'password.max'      => 'La contraseña no puede contener mas de 15 caracteres',
            'password_confirmation.required' => 'Debe confirmar la contraseña',
            'password_confirmation.confirmed' => 'Ambas contraseñas deben coincidir'
        ];
    }

}
