<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{

 
    protected $table = 'contract';

    protected $fillable = ['id','busines_group_id','contract_type', 'credit_limit', 'number', 'status', 'observation','created_by','image'];

    protected $dates = ['date_init', 'date_end', 'created_at', 'updated_at','reception_date','signature_date'];

    public function setDateEndAttribute($value)
     {
         $this->attributes['date_end'] = Carbon::parse($value);
     }
 
     // Accesor (accessor) para obtener el campo `last_login` como instancia de Carbon
     public function getDateEndAttribute($value)
     {
         return Carbon::parse($value);
     }

    public function setDateInitAttribute($value)
     {
         $this->attributes['date_init'] = Carbon::parse($value);
     }
 
     // Accesor (accessor) para obtener el campo `last_login` como instancia de Carbon
     public function getDateInitAttribute($value)
     {
         return Carbon::parse($value);
     }


    public function group()
    {
        return $this->belongsTo('App\Models\Group', 'group_id');
    }


    public function scopeDescription($query, $description)
    {
        if (trim($description) != "") {
            $query->where('business_groups.description', 'ILIKE', "%$description%");
        }
    }

    // public function scopeNumber($query, $number)
    // {
    //     if (trim($number) != "") {
    //         $query->where('contract.number', 'ILIKE', "%$number%");
    //     }
    // }

    
}
