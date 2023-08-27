<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class AppUpdateRequest extends Request
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
            'owner_id'   => 'required',
            'version'    => 'required',
            'file'    => 'required|mimes:zip,rar',
        ];
    }

    /**
     * Set custom messages for validator errors
     * @return array
     */
    public function messages()
    {
        return [
            'owner_id.required'     => 'El campo Red es obligatorio.',
            'version.required'      => 'El campo Versión es obligatorio.',
            'file.required'         => 'No subió ningun archivo.',
            'file.mimes'            => 'Formatos permitidos ZIP, RAR.'
        ];
    }
}
