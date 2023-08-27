<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class ServicesRuleRequest extends Request
{
    
    public function authorize()
    {
        return true;
    }

    
    public function rules()
    {
        return [
            'description'   => 'required|max:255',
            'message_user'   => 'required|max:255',

        ];
    }

  
    public function messages()
    {
        return [
            'description.required'          => 'El campo de Descripcion es Obligatorio',
            'message_user.required'              => 'El campo de Mensaje es Obligatorio',

        ];
    }
}
