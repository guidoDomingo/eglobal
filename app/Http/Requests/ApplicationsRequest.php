<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class ApplicationsRequest extends Request
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
            'resolution_width' => 'required|numeric|max:1920',
            'resolution_height' => 'required|numeric|max:1080'
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
            'name.required' => 'Debe asignar un nombre',
            'name.max' => 'La cantidad maxima de caracteres para el nombre es de 255',
            'resolution_width.required' => 'Debe asignar una resolucion (Ancho)',
            'resolution_width.numeric' => 'El valor de la resolucion debe ser numerico',
            'resolution_width.max' => 'La resolucion maxima (ancho) es de 1920px',
            'resolution_height.required' => 'Debe asignar una resolucion (Alto)',
            'resolution_height.numeric' => 'El valor de la resolucion debe ser numerico',
            'resolution_height.max' => 'La resolucion maxima (Alto) es de 1920px',
        ];
    }
}
