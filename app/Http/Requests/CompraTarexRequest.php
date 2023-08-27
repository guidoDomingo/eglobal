<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CompraTarexRequest extends Request
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
            'factura3'      => 'required|numeric',
            'fecha'         => 'required',
            'timbrado'      => 'required|numeric',
            'forma_pago'    => 'required',
            'modalidad'     => 'required',
            'cantidad'      => 'required|numeric',
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
            'factura3.required'     => 'El campo Nro. de factura es obligatorio',
            'factura3.numeric'      => 'El campo Nro. de factura solo debe incluir numeros',
            'fecha.required'        => 'El campo Fecha es obligatorio',
            'timbrado.required'     => 'El campo Timbrado es obligatorio',
            'timbrado.numeric'      => 'El campo Timbrado solo debe incluir numeros',
            'forma_pago.required'   => 'El campo Forma de Pago es obligatorio',
            'modalidad.required'    => 'El campo Modalidad es obligatorio',
            'cantidad.required'     => 'El campo Cantidad es obligatorio',
            'cantidad.numeric'      => 'El campo cantidad solo debe incluir numeros',
        ];
    }
}
