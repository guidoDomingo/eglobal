<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Atm;
use Carbon\Carbon;

class checkAtmStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atms:checkStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado de los atms y emite Notificaciones en caso de encontrar problemas';

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
            $atms = Atm::where('atm_status',0)->get();
            $count = 0;
            foreach ($atms as $atm){

                $previuos_alert = \DB::table('notifications')
                    ->where('atm_id',$atm->id)
                    ->where('updated_at',null)
                    ->where('processed', false)
                    ->whereIn('notification_type',[1,3])
                    ->first();

                $init = $atm->last_request_at;
                $end = Carbon::now();
                $elasep_time = Carbon::parse($end)->diffInMinutes(Carbon::parse($init));

                //Si no se reportaron incidencias
                if(!$previuos_alert){
                    if($elasep_time > 19){
                        $alerts = \DB::table('notifications')->insert([
                            'asigned_to' => $atm->related_user,
                            'atm_id' => $atm->id,
                            'created_at' => Carbon::now(),
                            'message'  => 'ATM Offline '.$elasep_time.' hace minutos',
                            'notification_type' =>  1,
                            'status'       =>  3
                        ]);
                        if($alerts){$count++;}
                    }
                }

                if(isset($previuos_alert->id) && $elasep_time < 19){
                    \DB::table('notifications')
                        ->where('id', $previuos_alert->id)
                        ->update([
                            'updated_at' => Carbon::now(),
                            'processed'  => true,
                            'comments'   => 'se reestablecio la conexion a internet'
                        ]);
                }

            }
            if($count <> 0){
                $this->info('ATM CHECKS | Se registraron '. $count . 'incidencias');
            }else{
                $this->info('ATM CHECKS | no se reportaron incidencias');
            }

            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada atms:checkStatus  | se registraron '.$count.' incidencias: '. $fecha );

        }catch (\Exception $e){
            //$this->info($e->getMessage());
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada atms:checkStatus  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('Check ATMS | Error al ejecutar la tarea');
        }
    }
}
