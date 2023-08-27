<?php

namespace App\Console\Commands;

use App\Http\Controllers\AlquilerController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarAlquiler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alquiler:GenerarAlquiler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingresa a ondanet un nuevo alquiler o un alquiler con vencimiento';

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
        try
        {
            $reporting = new AlquilerController();

            //Verifica si existe una primera transaccion para un alquiler nuevo y inserta en ondanet con la fecha de la primera transaccion
            $results_transactions = $reporting->checkTransaction();
            if($results_transactions['error'] == false){
                $this->info($results_transactions['message']);
            }else{
                $this->error($results_transactions['message']);
            }

            $results_vencimiento = $reporting->checkVencimiento();
            if($results_vencimiento['error'] == false){
                $this->info($results_vencimiento['message']);
            }else{
                $this->error($results_vencimiento['message']);
            }

            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada alquiler:GenerarAlquiler a las: '. $fecha);
        }catch(\Exception $e){
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada alquiler:GenerarAlquiler  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('Generar Alquiler | Error al ejecutar la tarea');
        }
    }
}
