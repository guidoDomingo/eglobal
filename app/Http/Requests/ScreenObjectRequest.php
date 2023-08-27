<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;

class ScreenObjectRequest extends Request
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //TODO: Use Sentinel auth method
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
            'location_x' => 'required|integer|max:9999',
            'location_y' => 'required|integer|max:9999',
            'screen_id' => 'required|numeric',
            'object_type_id' => 'required|numeric',
        ];
    }
}
