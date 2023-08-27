<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;

class ProvidersRequest extends Request
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
            'ruc' => 'required|max:20',
            'business_name' => 'required|max:80',
            'mobile_phone' => 'required|max:80',
            'address' => 'required|max:80',
            'ci' => 'max:20',
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
            'ruc.required' => 'El campo RUC es requerido',
            'ruc.max'       => 'La cantidad maxima para el campo RUC es de 20',
            'business_name.required' => 'El campo Razon Social es obligatorio',
            'business_name.max' => 'La cantidad maxima de caracteres para la Razon Social es de 80',
            'mobile_phone.required' => 'El campo Telefono Movil es obligatorio',
            'mobile_phone.max' => 'La cantidad maxima de caracteres para le Telefono Movil es de 80',
            'address.required' => 'El campo Direccion es obligatorio',
            'address.max' => 'La cantidad maxima de caracteres para el campo Direccion es de 80',
            'ci.max' => 'La cantidad maxima de caracteres para el campo Cedula de Identidad es de 20'
        ];
    }
}
