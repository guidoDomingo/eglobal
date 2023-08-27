<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;

class ProductsRequest extends Request
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

            'description' => 'required|max:100',
            'product_provider_id' => 'required|numeric',
            'tax_type_id' => 'required|numeric',
            'currency' => 'required|min:2',
            'cost' => 'required|numeric',
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
            'description.required' => 'El campo descripcion es obligatorio',
            'description.max' => 'La cantidad maxima de caracteres para el campo descripcion es de 100',
            'product_provider_id.required' => 'Debe elegir un proveedor',
            'tax_type.required' => 'Debe elegir un tipo de IVA',
            'currency.required' => 'Debe elegir la moneda',
            'cost.required' => 'Debe asignar un precio',
            'cost.numeric' => 'El costo solo acepta datos numericos'
        ];
    }
}
