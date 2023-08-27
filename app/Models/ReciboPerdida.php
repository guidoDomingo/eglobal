<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReciboPerdida extends Model
{
    use SoftDeletes;

    protected $table     = 'grupos_recibo_perdida';

    protected $fillable  = [
                            'group_id',
                            'numero', 
                            'fecha_finiquito',
                            'valor',
                            'forma_cobro', 
                            'gestionado',
                            'comentario',
                            'created_at',
                            'created_by',
                            'updated_at',
                            'updated_by',
                            'deleted_at'];
  
}
