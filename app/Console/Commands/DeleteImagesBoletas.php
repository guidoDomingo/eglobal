<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;
use Carbon\Carbon;


class DeleteImagesBoletas extends Command
{
    /**
     * Este comando trae los selects a convertir en formato excel
     *
     * @var string
     */

    protected $signature = 'paymentGW:DeleteImagesBoletas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminas todas las boletas descargadas del dia.';

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

        \Log::info("Iniciando proceso: Eliminacion de todas las imagenes de Boletas':");

        try {

            ini_set('max_execution_time', '0'); // for infinite time of execution
           
            //Url de donde se guardan las imagenes
            $url = public_path().'/resources/images/boleta_deposito/';

            \Log::info("Buscando todas las imagenes de la url: " . $url);
            //Se obtienen todas las imagenes de de la carpeta
            $files = array_diff(scandir($url), array('.', '..'));

            foreach($files as $file){
                $image_file = $url . $file;
                \Log::info("Eliminando la imagen: ". $file);
                unlink($image_file);

                \Log::info("Imagen ". $file . ' eliminada');
            }

            $date = date('d/m/Y');
            $time = date('H:i:s');

            \Log::info("Imagenes borradas exitosamente el $date a las $time.");
        } catch (\Exception $e) {

            $error_detail = [
                'message' => 'Ocurrió un error al eliminar todas las imagenes de Boletas.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("\nError en " . __FUNCTION__ . ": \nDetalles: " . json_encode($error_detail));
        }

        \Log::info("Fin de proceso: Envío de archivo 'Eliminacion de imagenes de Boletas'.");
    }
}
