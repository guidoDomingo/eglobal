<?php

namespace App\Console\Commands;

use App\Services\ReportServices;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarReporteContable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:saldosContables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza registros historicos de saldos en línea de una fecha determinada';

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
            $reporting = new ReportServices(null);
            $results = $reporting->historico_saldos_en_linea();
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada reports:saldosContables a las: '. $fecha);
            if($results)
            {
                $this->info('Generar reporte contable | Se insertaron registros en Historico Saldos Contables');
            }else{
                $this->info('Generar reporte contable | no se insertó ningún registro');
            }
        }catch(\Exception $e){
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada reports:saldosContables  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('Generar reporte contable | Error al ejecutar la tarea');
        }
    }
}
