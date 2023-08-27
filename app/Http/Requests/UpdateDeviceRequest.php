<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;

class UpdateDeviceRequest extends Request
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
            'serialnumber'       => 'required|max:255|unique:device,serialnumber,'.$this->route('device'),
            //'ruc' =>'required|string|min:6|unique:clientes,ruc,'.$this->route('cliente')->id.'|max:11',
            //'code' => 'required|numeric|unique:atms,code,'.$this->route('atmnew'),

            'installation_date'  => 'required',
            'model_id'           => 'required',

        ];
    }

    public function messages()
    {
        return[
            'serialnumber.required'   =>'El campo Serial es requerido.',
            'serialnumber.max'        =>'Solo se permite hasta 255 caracteres.',
            'serialnumber.min'        =>'Se requiere como minimo 1 caracter.',
            'serialnumber.unique'     =>'El Serial ya existe.',

            'installation_date.required'   =>'El campo Fecha de instalacion es requerido.',
            'model_id.required'       =>'El campo Modelo es requerido.',

        ];
    }
}
