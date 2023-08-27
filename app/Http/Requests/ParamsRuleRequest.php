<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ParamsRuleRequest extends Request
{
    
    public function authorize()
    {
        return true;
    }

    
    public function rules()
    {
        return [
            'description'   => 'required|max:255',
        ];
    }

  
    public function messages()
    {
        return [
            'description.required'          => 'El campo de Descripcion es Obligatorio',
        ];
    }
}
