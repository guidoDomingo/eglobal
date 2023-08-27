<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table    = 'campaigns';
    public $timestamps  = false;
    protected $fillable = ['name', 'duration', 'flow', 'perpetuity','tipoCampaña'];
    protected $dates    = ['start_date', 'end_date'];

    public function setEndDateAttribute($value)
     {
         $this->attributes['end_date'] = Carbon::parse($value);
     }
 
     // Accesor (accessor) para obtener el campo `last_login` como instancia de Carbon
     public function getEndDateAttribute($value)
     {
         return Carbon::parse($value);
     }

    public function setStartDateAttribute($value)
     {
         $this->attributes['start_date'] = Carbon::parse($value);
     }
 
     // Accesor (accessor) para obtener el campo `last_login` como instancia de Carbon
     public function getStartDateAttribute($value)
     {
         return Carbon::parse($value);
     }

    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function campaignsDetails()
    {
        return $this->hasMany('App\Models\CampaignDetails', 'campaigns_id');
        //return $this->hasMany('App\Models\CampaignDetails');

    }

    public static function filterAndPaginate($name)
    {

        return Campaign::name($name)
            //->with('categorias')
            //->with('service_sources')
            ->orderBy('id', 'DESC')
            ->paginate();
    }
    
}
