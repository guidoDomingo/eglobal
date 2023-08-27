<?php

namespace App\Console\Commands;

use App\Models\Deposit;
use App\Services\DepositoBoletaServices;
use Illuminate\Console\Command;
use Mail;
use Carbon\Carbon;

class DepositsMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miniterminales:insertCobranzas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa cobranzas de boletas de depósito para MINITERMINALES';

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
        // Establecer límite de tiempo de ejecución a 60 segundos
        set_time_limit(60);
        try 
        {
            $boletas = \DB::table('boletas_depositos')            
                ->where('conciliado','=', false)
                ->where('estado','=',true)
                ->take(20)
                ->get();

            
            $deposito_boleta_services = new DepositoBoletaServices();    
            foreach($boletas as $boleta)
            {
                // Verificar si se ha excedido el tiempo límite
                if (time() - $_SERVER['REQUEST_TIME'] >= 30)
                {
                    break; // Salir del bucle si se ha alcanzado el tiempo límite
                }

                $cobranzas = $deposito_boleta_services->insertCobranzas_V2($boleta->id);
                \Log::info('miniterminales:insertCobranzasBatch',['boleta_id' => $boleta->id, 'result'=> $cobranzas]);
                
                if($cobranzas['error'] == false)
                {
                    //update de campo conciliado
                    $conciliado = \DB::table('boletas_depositos')
                    ->where('id', $boleta->id)
                    ->update(['conciliado' => true]);

                }
                else
                {
                    $data = [
                        'user_name'    => 'Tesorería',
                        'fecha'        => $boleta->fecha, 
                        'nroboleta'    => $boleta->boleta_numero,
                        'monto'        => number_format($boleta->monto, 0),
                        'boleta'       => $boleta->id
                    ];     

                    Mail::send('mails.alert_cobranzas',$data,
                        function($message){
                            $user_email = 'sistemas@eglobalt.com.py';
                            $user_name  = 'Admin';
                            $message->to($user_email, $user_name)->subject('[EGLOBAL] Alerta de Miniterminales Cobranzas');
                    });
                }                                             
            }   
            
            $this->info('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    }
}
