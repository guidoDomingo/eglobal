<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaracteristicaSucursal extends Model
{
    use SoftDeletes;
   
    protected $table        = 'caracteristicas';
    protected $fillable     = [ 'group_id',
                                'canal_id', 
                                'categoria_id', 
                                'cta_bancaria_id', 
                                'referencia',
                                'accesibilidad',
                                'visibilidad',
                                'trafico',
                                'dueño',
                                'atendido_por',
                                'estado_pop',
                                'permite_pop',
                                'tiene_pop',
                                'tiene_bancard',
                                'tiene_pronet',
                                'tiene_netel',
                                'tiene_pos_dinelco',
                                'tiene_pos_bancard',
                                'tiene_billetaje',
                                'tiene_tm_telefonito',
                                'visicooler',
                                'bebidas_alcohol',
                                'bebidas_gasificadas',
                                'productos_limpieza',
                                'correo'
                            ];
    protected $dates        = [ 'deleted_at', 
                                'created_at', 
                                'updated_at'];


}
