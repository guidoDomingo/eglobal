<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AlquilerRequest extends Request
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
            'group_id'      => 'required',
            'amount'        => 'required|numeric',//|min:300000',
            'num_cuota'     => 'required|integer'//|between:12,24'
        ];
    }

    /**
     * Get the messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'group_id.required'         => 'El campo de Grupo es Obligatorio',
            'amount.required'           => 'El campo Monto es obligatorio',
            'amount.numeric'            => 'El campo Monto solo debe incluir numeros',
            'amount.min'                => 'El campo Monto debe tener un monto minimo de 100.000',
            'num_cuota.required'        => 'Favor colocar el numero de Cuotas',
            'num_cuota.integer'         => 'El numero de cuotas debe ser numerico',
            'num_cuota.between'         => 'El numero de Cuotas debe estar entre 12 y 24'
        ];
    }
}
