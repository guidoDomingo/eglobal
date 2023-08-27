<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BancoAltas extends Model
{
    use SoftDeletes;
   
    protected $table        = 'clientes_bancos';
    protected $fillable     = ['descripcion'];
    protected $dates        = ['deleted_at', 'created_at', 'updated_at'];

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }

    
}
