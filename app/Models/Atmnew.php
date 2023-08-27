<?php

namespace App\Models;

//use App\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Atmnew extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    use SoftDeletes;
    protected $table = 'atms';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['auth_token', 'created_at', 'updated_at'];
    /**
     * The attributes included from the model's JSON form.
     */
    protected $appends = array('application_version');

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'created_by', 'updated_by', 'code', 'last_token',
        'public_key', 'private_key', 'deposit_code', 'owner_id', 'housing_id'];

    public function getNameCodeAttribute()
    {
        return $this->attributes['name'] . ' - ' . $this->attributes['code'];
    }
    /**
     * For Searches by name
     */
    public function scopeName($sql, $name)
    {
        if (trim($name) != "") {
            $sql->where(function($query) use($name){
                $query->where('compile_version', 'ILIKE', "%$name%");
                $query->orWhere('name', 'ILIKE', "%$name%");
                $query->orWhere('code', 'ILIKE', "%$name%");
                $query->orWhereRaw("id::text ILIKE '%$name%'");
                $query->orWhereRaw("to_char(last_request_at, 'DD/MM/YYYY HH:MI:SS') ILIKE '%$name%'");
                $query->orWhere(function($q) use($name){
                    $estados = ['Online','Offline','Suspendido'];

                    foreach($estados as $key => $estado){
                        if(stristr($estado, $name) == $estado){
                            switch ($key) {
                                case 0:
                                    $q->orWhere(function($subQuery){
                                        $subQuery->where('atm_status', '=', 0);
                                        $subQuery->whereRaw("last_request_at >= NOW() - interval '20 minutes'");
                                    });
                                    break;
                                case 1:
                                    $q->orWhere(function($subQuery){
                                        $subQuery->where('atm_status', '=', 0);
                                        $subQuery->whereRaw("last_request_at < NOW() - interval '20 minutes'");
                                    });
                                    break;
                                case 2:
                                    $q->orWhere(function($subQuery){
                                        $subQuery->where('atm_status', '<>', 0);
                                    });
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                });
                $query->orWhereHas('ownedBy', function($q) use($name){
                    $q->where('name', 'ILIKE', "%$name%");
                });
                
            });
        }
    }

    /**
     * For Searches by id
     */
    public function scopeId($query, $id)
    {
        if (trim($id) != "") {
            $query->where('id', '=', "$id");
        }
    }

    public static function filterAndPaginate($name, $id = null, $owner_id = 0, $group_id = 0)
    {
        $atms = Atmnew::where('owner_id','!=',18)
                ->where('type','=','at')
                ->orderBy('atm_status', 'desc')
                ->orderBy('last_request_at', 'asc');
        
        if(!empty($id)){
            $atms->where('id',$id)
            ->where('owner_id','!=',18)
            ->where('type','=','at');
        }

        if(!empty($owner_id)){
            $atms->where('owner_id',$owner_id)
                ->where('owner_id','!=',18)
                ->where('type','=','at');
        }

        if(!empty($group_id)){
            $group=\DB::table('branches')
            ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
            ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
            ->where('branches.group_id', $group_id)
            ->pluck('atms.id', 'atms.id');

            $atms_id = '('.implode(',', $group).')';

            $atms->whereIn('id', $group);
        }

        return $atms->name($name)->paginate(20);
    }

    public function ownedBy()
    {
        return $this->hasOne('App\Models\Owner', 'id', 'owner_id');
    }

    /**
     * Relationship between atms and Applications
     */
    public function applications()
    {
        return $this->belongsToMany('App\Models\Applications', 'atm_application', 'atm_id', 'application_id')->withPivot('active');
    }
    /**
     * Relationship between atms and Applications
     */
    public function activeApplication()
    {
        return $this->applications()->wherePivot('active', '=', true)->first();

    }

}
