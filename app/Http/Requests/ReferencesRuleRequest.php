<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ReferencesRuleRequest extends Request
{
    
    public function authorize()
    {
        return true;
    }

    
    public function rules()
    {
        return [
            'reference'   => 'required|max:255',

        ];
    }

  
    public function messages()
    {
        return [
            'reference.required'          => 'El campo de Descripcion es Obligatorio',

        ];
    }
}
