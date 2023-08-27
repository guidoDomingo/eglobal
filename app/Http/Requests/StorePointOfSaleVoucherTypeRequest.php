<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;


class StorePointOfSaleVoucherTypeRequest extends Request
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
            'voucher_type_id' => 'required|numeric',
            'expedition_point' => 'required|size:3'
        ];
    }
}
