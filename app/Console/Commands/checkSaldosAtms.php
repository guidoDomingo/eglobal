<?php

namespace App\Console\Commands;

use App\Models\Atm;
use Illuminate\Console\Command;
use PhpSpec\Exception\Exception;
use Carbon\Carbon;

class checkSaldosAtms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atms:checkSaldos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica saldos en linea y notifica cuando estos estan al limite';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $atm_parts = \DB::table('atms_parts')
                ->where('cantidad_minima','<>',0)
                ->where('activo',true)
                ->whereRaw('cantidad_actual < cantidad_alarma OR cantidad_actual < cantidad_minima')
                ->get();
            $count = 0;
            foreach($atm_parts as $parts){
                $previuos_alert = \DB::table('notifications')
                    ->where('atm_id',$parts->atm_id)
                    ->where('atm_part_id',$parts->id)
                    ->where('updated_at',null)
                    ->where('processed', false)
                    ->whereIn('notification_type',[4])
                    ->first();

                if(!$previuos_alert){
                    $atm = Atm::find($parts->atm_id);
                    $message = '';

                    if($atm){
                        if($parts->cantidad_actual < $parts->cantidad_minima){
                            $message = $parts->nombre_parte. ' - ' .$parts->denominacion  . ' : ha llegado a la cantidad minima';
                        }else{
                            $message = $parts->nombre_parte. ' - ' .$parts->denominacion  . ' : se  acerca a la cantidad minima';
                        }
                        $alerts = \DB::table('notifications')->insert([
                        'asigned_to' => $atm->related_user,
                        'atm_id' => $atm->id,
                        'atm_part_id' => $parts->id,
                        'created_at' => Carbon::now(),
                        'message'  => $message,
                        'notification_type' =>  4,
                        'status'       =>  5
                        ]);
                        if($alerts){$count++;}
                    }
                }


            }

            if($count <> 0){
                $this->info('ATM CHECKS | Se registraron '. $count . ' partes con saldos al limite');
            }else{
                $this->info('ATM CHECKS | no se reportaron incidencias');
            }

            /*VERICA ESTADOS DE NOTIFICACIONES de tipo saldos*/
            $notificaciones = \DB::table('notifications')->
                where('notification_type', 4)->
                where('updated_at',null)->
                where('processed',false)->get();

            $count = 0;
            foreach ($notificaciones as $notificacion){
                $atm_id = $notificacion->atm_id;
                $part_id = $notificacion->atm_part_id;
                $partes = \DB::table('atms_parts')->
                    where('atm_id', $atm_id)->
                    where('id',$part_id)->
                    first();


                if($partes->cantidad_actual > $partes->cantidad_alarma){
                    \DB::table('notifications')
                        ->where('id',$notificacion->id)
                        ->update([
                        'processed' => true,
                        'updated_at' => Carbon::now()
                            ]);
                    $count++;
                }

            }
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada atms:checkSaldos  | se actualizaron '.$count.' partes de atm a las: '. $fecha );
            $this->info('ATM checkSaldos | se actualizaron '.$count.' partes de atm');

        }catch (\Exception $e){
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada atms:checkSaldos  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('ATM checkSaldos | Error al ejecutar la tarea. Se registra evento en el log');
        }


    }
}
