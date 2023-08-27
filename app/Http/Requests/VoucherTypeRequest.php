<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;

class VoucherTypeRequest extends Request
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
            'voucher_type_code' => 'required|alpha_num|size:3',
            'description' => 'required|max:255',
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
            'voucher_type_code.required' => 'El campo codigo de comprobante es obligatorio',
            'voucher_type_code.alpha_num' => 'El campo codigo de comprobante debe ser alfanumerico - Ej: AC1',
            'voucher_type_code.size' => 'El campo codigo de comprobante debe tener 3 caracteres',
            'description.required' => 'El campo descripcion es obligatorio',
            'description.max' => 'El campo descripcion solo puede tener un maximo de 255 caracteres'
        ];
    }
}
