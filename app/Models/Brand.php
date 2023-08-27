<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    public $timestamps = false;    

    protected $table = 'brand';

    protected $fillable = ['id','description'];

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {
            $query->where('description', 'ILIKE', "%$name%");
        }

    }

    public static function filterAndPaginate($name)
    {
        return Brand::name($name)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }
}
