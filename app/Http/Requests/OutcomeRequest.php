<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class OutcomeRequest extends Request
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
            'description' => 'required|min:5',
            'ondanet_outcome_code' => 'required|numeric',
            'provider_type_code' => 'numeric',
            'provider_id' => 'required'
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
            'description.min' => 'La descripcion debe contener al menos 5 caracteres',
            'ondanet_type_code.required' => 'El campo codigo ondanet es obligatorio',
            'ondanet_type_code.numeric' => 'El campo codigo ondanet debe ser numerico',
            'provider_type_code.numeric' => 'El campo codigo de proveedor debe ser numerico',
            'provider_id.required' => 'Debe elegir un proveedor'
        ];
    }

}
