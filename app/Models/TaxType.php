<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxType extends Model
{
    use SoftDeletes;

    protected $table = 'tax_types';

    protected $fillable = ['tax_code','description'];
    
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
}
