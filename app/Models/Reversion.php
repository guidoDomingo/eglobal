<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reversion extends Model
{
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'mt_recibos_reversiones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $fillable = ['recibo_id', 'transaction_id', 'ventas_cobradas', 'reversion_id'];

    protected $dates = ['fecha_reversion'];

    public function scopeName($query, $name)
    {   
        if (trim($name) != "") {                        
            $query->whereRaw('CAST(transaction_id AS TEXT) LIKE '."'%$name%'");
        }
    }

    public static function filterAndPaginate($name)
    {
       
        $rever = Reversion::name($name)
            ->select([
                'mt_recibos_reversiones.*',
                'movements.destination_operation_id',
                'mt_recibos.id as recibo_id',
                'mt_recibos.created_at'
            ])
            ->join('mt_recibos', function($query){
                $query->on('mt_recibos.id', '=', 'mt_recibos_reversiones.recibo_id');
            })
            // ->join('movements', 'movements.id', '=', 'mt_recibos.movements_id')
            ->join('movements', 'movements.id', '=', 'mt_recibos.mt_movements_id')
            ->join('transactions', 'transactions.id', '=', 'mt_recibos_reversiones.transaction_id')
            ->where('transactions.service_source_id', 7)
            ->orderBy('mt_recibos.id', 'DESC')
        ->Paginate(20);

        return $rever;

    }
}
