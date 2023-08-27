<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class StorePointOfSaleVoucherRequest extends Request
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
            //'voucher_code' => 'required|digits_between:1,5',
            'valid_from' => 'required|date_format:d/m/Y',
            'valid_until' => 'required|date_format:d/m/Y',
            'from_number' => 'required|digits_between:1,10',
            'to_number' => 'required|digits_between:1,10',
        ];
    }
}
