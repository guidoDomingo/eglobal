<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions_batch extends Model
{
    protected $table    = 'transactions_batch';
    protected $fillable = array(
        'processed',
        'status',
        'atm_transaction_id',
        'parent_transaction_id',
        'updated_by_user',
        'updated_at',

    );
    protected $guarded  = ['id'];
}
