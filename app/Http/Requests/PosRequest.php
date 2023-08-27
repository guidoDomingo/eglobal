<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class PosRequest extends Request
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
            'description' => 'required|max:255',
            //'seller_type' => 'required|numeric|min:1',
            'pos_code' => 'required|numeric|min:1|max:99999',
            'branch_id' => 'required|numeric',
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
            'description.required' => 'El campo Descripcion es obligatorio',
            'description.max'   => 'La cantidad maxima de caracteres es de 255',
            //'seller_type.required' => 'El campo tipo de vendedor es obligatorio',
            //'seller_type.numeric' => 'El cambio tipo de vendedor debe ser numerico',
            'pos_code.required' => 'El codigo de punto de venta es obligatorio',
            'pos_code.numeric' => 'El codigo del punto debe ser obligatorio',
            'pos.code.min'  => 'El numero minimo para el codigo del punto es 1000',
            'pos_code.max' => 'El numero maximo para le codigo del punto es 99999',
            'branch_id.required' => 'El campo sucursal es obligatorio'
        ];
    }
}
