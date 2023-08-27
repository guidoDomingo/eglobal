<?php

namespace App\Console\Commands;

use App\Http\Controllers\ComisionesController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckComisiones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comisiones:GenerarDescuento';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesar los descuentos de comisiones';

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
            $reporting = new ComisionesController();

            //Verifica si existe una primera transaccion para un alquiler nuevo y inserta en ondanet con la fecha de la primera transaccion
            $results_comisiones = $reporting->procesar_pagos_comision();
            if($results_comisiones['error'] == false){
                $this->info($results_comisiones['message']);
            }else{
                $this->error($results_comisiones['message']);
            }

            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada comisiones:GenerarDescuento a las: '. $fecha);
        }catch(\Exception $e){
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada comisiones:GenerarDescuento  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('Generar Descuento | Error al ejecutar la tarea');
        }
    }
}
