<?php namespace App\Http\Requests;

class RoleRequest extends Request
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
            'description' => 'required',
            'name'        => 'required',
            'slug'        => 'required'
        ];
    }


    /**
     * Set message for broken rules
     * @return array
     */
    public function messages()
    {
        return [
            'description.required'       => 'Descripcion requerida',
            'name.required'        => 'Nombre requerido',
            'slug.required'     => 'Identificador requerido'
        ];
    }

}
