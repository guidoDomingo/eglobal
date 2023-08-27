<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationsParams extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'notification_params';
    public $timestamps = false;
    protected $guarded = ['id'];
    // protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where('mensaje', 'ILIKE', "%$name%");
        }
    }

    public static function filterAndPaginate($name)
    {
        return NotificationsParams::name($name)
        	->select([
        		'notification_params.id',
        		'notification_types.description as tipo_notificacion',
        		'notification_params.prefix',
        		'notification_params.mensaje',
        		'services_providers_sources.description as services_sources',
        		'notification_params.valor',
        		'notification_params.service_id',
        		'notification_params.created_at',
        		'notification_params.updated_at',
        	])
            ->join('notification_types', 'notification_types.id', '=', 'notification_params.notification_type')
            ->leftJoin('services_providers_sources', 'services_providers_sources.id', '=', 'notification_params.service_source_id')
            ->orderBy('id', 'ASC')
            ->paginate();
    }
}
