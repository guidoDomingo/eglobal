<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Carbon\Carbon;

class VentaRequest extends Request
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
        //dd($today . ' and ' . $this->fecha);

        $treinta = Carbon::now()->addDays(-30);

        /*$end = Carbon::parse($this->end);
        $start =  Carbon::parse($this->start);

        $date1=date_create(Carbon::today());
        $date2=date_create($input['fecha']);
        $diff=date_diff($date1,$date2);

        $diff_in_days = $end->diffInDays($start);*/

        return [
            'group_id'      => 'required',
            'id_vendedor'   => 'required',
            'id_acreedor'   => 'required',
            'amount'        => 'required|numeric',//|min:1000000',
            'serialnumber'  => 'required',//|unique:housing',
            'tipo_venta_id' => 'required_if:id_acreedor,7',
            'fecha'         => 'required|date|before:'. $today . '|after:' . $treinta,
            'num_cuota'     => 'required_if:tipo_venta_id,1|integer|between:1,60'
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
            'id_vendedor.required'      => 'El campo Vendedor es obligatorio',
            'id_acreedor.required'      => 'El campo Acreedor es obligatorio',
            'amount.required'           => 'El campo Monto es obligatorio',
            'amount.numeric'            => 'El campo Monto solo debe incluir numeros',
            'amount.min'                => 'El campo Monto debe tener un monto minimo de 20.000.000',
            'serialnumber.required'     => 'El campo Codigo de Equipo es obligatorio',
            //'serialnumber.unique'       => 'El Siguiente Serial ya existe en el sistema',
            'tipo_venta_id.required_if' => 'El campo de Tipo de Venta es obligatorio',
            'fecha.required'            => 'El campo Fecha es obligatorio',
            'fecha.date'                => 'El campo Fecha debe ser de un formato valido',
            'fecha.before'              => 'La fecha que coloco es superior a la fecha actual',
            'fecha.after'               => 'Favor colocar una Fecha mas reciente',
            'num_cuota.required_if'     => 'Favor colocar el numero de Cuotas',
            'num_cuota.integer'         => 'El numero de cuotas debe ser numerico',
            'num_cuota.between'         => 'El numero de Cuotas debe estar entre 1 y 48'
        ];
    }
}
