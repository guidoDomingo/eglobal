<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class ScreenRequest extends Request
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
            'name' => 'required|max:255',
            'description' => 'max:255',
            'refresh_time' => 'required|integer|min:1|max:9999',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El campo nombre es obligatorio',
            'name.max' => 'La cantidad maxima de caracteres para el campo nombre es de 255',
            'description.max' => 'La cantidad maxima de caracteres para la descripcion es de 255',
            'refresh_time.required' => 'El campo Tiempo de Refresco es obligatorio',
            'refresh_time.integer' => 'El campo Tiempo de Refresco debe ser Entero',
            'refresh_time.min' => 'El campo Tiempo de refresco debe tener al menos un valor',
            'refresh_time.max'  => 'El valor maximo del campo Tiempo de Refresco es de 9999'
        ];
    }

}
