<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applications extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'version_code', 'version_name', 'owner_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * The attributes included from the model's JSON form.
     */
    protected $appends = array('images_url', 'fonts_url', 'font_files');

    /**
     * Get image url path for current atm-application/version
     */
    public function getImagesUrlAttribute()
    {
        $images_url = config('app.url') . '/resources/' . $this->id . '/';
        return $images_url;
    }

    /**
     * Get image url path for current atm-application/version
     */
    public function getFontsUrlAttribute()
    {
        $images_url = config('app.url') . '/resources/' . $this->id . '/fonts/';
        return $images_url;
    }

    public function getFontFilesAttribute()
    {

        $fonts = [
            [
                'id' => 1,
                'font_name' => 'remachine',
                'file' => 'RemachineScript.ttf',
            ],
            [
                'id' => 2,
                'font_name' => 'pacifico',
                'file' => 'Pacifico.ttf',
            ],

        ];
        return $fonts;
    }

    /**
     * Get active application for a given atm_id
     */
    public static function active($atm_id)
    {
        // return Application::where('atm_id', $atm_id)
        //     ->where('active', 't');
    }

    /**
     * Relationship between applications and atms
     */
    public function atms()
    {
        // return $this->belongsTo('App\Atm');
        return $this->belongsToMany('App\Models\Atm')->withPivot('active');
    }

    /**
     * Relationship between applications and screens
     */
    /*public function screens()
    {
        return $this->hasMany('App\Modules\Art\Screen');
    }*/

    /**
     * Relationship between applications and versions
     */
    public function versions()
    {
        return $this->hasMany('App\Models\Version');
    }

    /**
     * Relationship between applications and flows
     */
    public function flows()
    {
        return $this->hasMany('App\Modules\Art\Flow');
    }
    /**
     * Relationship between applications and params
     */
    public function params()
    {
        return $this->belongsToMany('App\Models\Param')->withPivot('value', 'created_at', 'updated_at');
    }
    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('name', 'ILIKE', "%$name%");
        }
    }
    /**
     * Query for App with Atm_id
     */
    /* public function scopeAtmId($query, $atm_id)
    {
    if (is_numeric($atm_id) && $atm_id > 0) {
    $query->where('atm_id', $atm_id);
    }
    }
     */
    public static function filterAndPaginate($name)
    {

        return Applications::name($name)
            ->orderBy('id', 'desc')
            ->paginate();
    }

}
