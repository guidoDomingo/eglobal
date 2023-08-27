<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class GroupRequest extends Request
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
            'description'   => 'required|max:255',
            'ruc'           => 'unique:business_groups|required|max:255'
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
            'description.required'          => 'El campo de Descripcion es Obligatorio',
            'ruc.required'                  => 'El campo RUC es obligatorio',
            'ruc.unique'                    => 'El RUC colocado ya existe'
        ];
    }
}
