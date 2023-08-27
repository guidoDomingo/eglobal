<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class StoreAtmRequest extends Request
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
            'code' => 'required|numeric|unique:atms,code',
            'public_key' => 'required|alpha_num|unique:atms,public_key',
            'private_key' => 'required|alpha_num|unique:atms,private_key',
        ];
    }
}
