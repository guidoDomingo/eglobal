<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Routing\Route;

class UpdateAtmRequest extends Request
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
            'code' => 'required|numeric|unique:atms,code,'.$this->route('atm'),
            'public_key' => 'required|alpha_num|unique:atms,public_key,'.$this->route('atm'),
            'private_key' => 'required|alpha_num|unique:atms,private_key,'.$this->route('atm'),
        ];
    }
}
