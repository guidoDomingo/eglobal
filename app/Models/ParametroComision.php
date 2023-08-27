<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParametroComision extends Model
{
    use SoftDeletes;
    /**
     * The database table used by the model
     * @var string
     */
    protected $table = 'parametros_comisiones';

    /**
     * The attribute that are mass assignable
     * @var array
     */
    protected $fillable = ['atm_id', 'comision', 'service_id', 'service_source_id', 'tipo_comision', 'created_by', 'updated_by', 'tipo_servicio_id'];

    protected $dates = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * For Searches by name
     */
    public function scopeName($query, $name)
    {
        if (trim($name) != "") {
            $query->where(function($sql) use($name){
                // $sql->OrWhere('services_ondanet_pairing.service_description', 'ILIKE', "%$name%");
                $sql->orWhereHas('atm', function($q) use($name){
                    $q->where('name', 'ILIKE', "%$name%");
                });

                $sql->orWhereHas('service_source', function($q) use($name){
                    $q->where('description', 'ILIKE', "%$name%");
                });

                $sql->orWhereHas('services_ondanet_pairing', function($q) use($name){
                    $q->where('service_description', 'ILIKE', "%$name%");
                });

                $sql->orWhereHas('service_product', function($q) use($name){
                    $q->where('description', 'ILIKE', "%$name%");
                });

                $sql->orWhereHas('service_product.webserviceprovider', function($q) use($name){
                    $q->where('name', 'ILIKE', "%$name%");
                });

                $sql->orWhere(function($q) use($name){
                    $estados = ['Integración Directa','Bocas de Cobranzas'];

                    foreach($estados as $key => $estado){
                        if(stristr($estado, $name) == $estado){
                            switch ($key) {
                                case 0:
                                    $q->orWhere(function($subQuery){
                                        $subQuery->where('tipo_servicio_id', '=', 1);
                                    });
                                    break;
                                case 1:
                                    $q->orWhere(function($subQuery){
                                        $subQuery->where('tipo_servicio_id', '=', 0);
                                    });
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                });
            });
        }
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get Atm Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function atm()
    {
        return $this->hasOne('App\Models\Atm', 'id', 'atm_id')->withTrashed();
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_source()
    {
        return $this->hasOne('App\Models\ServiceProviderSource', 'id', 'service_source_id');
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function services_ondanet_pairing()
    {
        return $this->hasOne('App\Models\ServiceOndanetPairing', 'id', 'service_id');
    }

    /**
     * Get Categories Model for marca
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_product()
    {
        return $this->hasOne('App\Models\ServiceProviderProduct', 'id', 'service_id');
    }

    public static function filterAndPaginate($name)
    {
        return ParametroComision::name($name)
            ->select([
                'parametros_comisiones.*',
                'services_ondanet_pairing.service_description as servicio',
                'service_provider_products.description',
                'service_providers.name',
            ])
            ->with('atm')
            ->leftJoin('services_ondanet_pairing', function($query){
                $query->on('services_ondanet_pairing.service_request_id', '=', 'parametros_comisiones.service_id');
                $query->on('services_ondanet_pairing.service_source_id', '=', 'parametros_comisiones.service_source_id');
                $query->where('parametros_comisiones.tipo_servicio_id', '=', 0);
            })
            ->leftJoin('service_provider_products', function($query){
                $query->on('service_provider_products.id', '=', 'parametros_comisiones.service_id');
                $query->where('parametros_comisiones.tipo_servicio_id', '=', 1);
            })
            ->leftJoin('service_providers', function($query){
                $query->on('service_provider_products.service_provider_id', '=', 'service_providers.id');
                $query->where('parametros_comisiones.tipo_servicio_id', '=', 1);
            })
            ->with('service_source')
            ->orderBy('id', 'DESC')
            ->paginate();
    }
}