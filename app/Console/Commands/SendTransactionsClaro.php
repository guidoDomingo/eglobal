<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Http\Controllers\ReportingController;

class SendTransactionsClaro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:sendTransactionsClaro';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia todas las transacciones del mes a Claro';

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
            
            $transactions = new ReportingController();    
            
            $sendClaro = $transactions->getTransactions();
            
            $this->info('SendTransactionsClaro | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada transactions:sendTransactionsClaro  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            $this->info('transactions:sendTransactionsClaro | Error al ejecutar la tarea. Se registra evento en el log');
        }
    }
}
