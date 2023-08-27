<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;
use Carbon\Carbon;

class DepositoAlquilerRequest extends Request
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
        $today = Carbon::now();

        $quince = Carbon::now()->addDays(-15);

        return [
            'tipo_pago_id'          => 'required',
            'banco_id'              => 'required',
            'cuenta_bancaria_id'    => 'required',
            'monto'                 => 'required|numeric',
            'boleta_numero'         => 'required|unique:boletas_depositos,boleta_numero,NULL,id,deleted_at,NULL|unique:mt_recibos_pagos_miniterminales,boleta_numero,NULL,id,deleted_at,NULL',
            'fecha'                 => 'required|date|before:'. $today . '|after:' . $quince,
            'atm_id'                => 'required'
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
            'tipo_pago_id.required'         => 'El campo de tipo de pago es obligatorio',
            'banco_id.required'             => 'El campo banco es obligatorio',
            'cuenta_bancaria_id.required'   => 'El campo cuenta bancaria es obligatorio',
            'monto.required'                => 'El campo monto es obligatorio',
            'monto.numeric'                 => 'El campo monto solo acepta datos numericos',
            'boleta_numero.required'        => 'El campo de numero de boleta es Obligatorio',
            'boleta_numero.unique'          => 'El siguiente numero de boleta ya existe en el sistema',
            'fecha.required'                => 'El campo fecha es Obligatorio',
            'fecha.date'                    => 'El campo Fecha debe ser de un formato valido',
            'fecha.before'                  => 'La fecha que coloco es superior a la fecha actual',
            'fecha.after'                   => 'Favor colocar una Fecha mas reciente',
            'atm_id.required'               => 'El campo ATM es obligatorio',
        ];
    }
}
