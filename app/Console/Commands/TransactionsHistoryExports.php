<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Excel;
use Mail;
use Carbon\Carbon;

class TransactionsHistoryExports extends Command
{
    /**
     * Este comando trae los selects a convertir en formato excel
     *
     * @var string
     */
    protected $signature = 'paymentGW:TransactionsHistoryExports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista de selects guardados para su posterior exportación a formato excel.';

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
        \Log::info("Iniciando comando $this->signature.");
        $this->info("Iniciando comando $this->signature.");
        \Log::info('--------------------------------------------------');

        ini_set('max_execution_time', 0);
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $sftp_server = env('SFTP_SERVER');
        $sftp_user_name = env('SFTP_USER_NAME');
        $sftp_user_password = env('SFTP_USER_PASSWORD');
        $sftp_port = env('SFTP_PORT');
        $sftp_remote_dir = env('SFTP_REMOTE_DIR');
        $sftp_link = env('SFTP_LINK');

        $format = 'csv';

        $list_ids = [23]; //Lista de ids excepcionales. Id de usuario Wilson Bazan

        $url_temp = public_path('exports');

        $list = \DB::table('select_to_manage as stm')
            ->select(
                'stm.id',
                'stm.description',
                'u.id as user_id',
                'u.email as user_mail',
                'u.username as user_name',
                'u.description as user_description'
            )
            ->join('users as u', 'u.id', '=', 'stm.user_id')
            ->where('stm.status', false)
            ->whereNotNull('u.email')
            ->orderBy('stm.created_at', 'asc')
            ->take(10)
            ->get();

        $i = 1;

        $query_1 = '';
        $query_2 = '';

        //Fin de valores iniciales

        \Log::info('Obteniendo selects pendientes:');

        foreach($list as $item) {

            try {

                $roles = \DB::table('roles as r')
                    ->select(
                        \DB::raw("trim(array_to_string(array_agg(r.slug), ',')) as description")
                    )
                    ->join('role_users as ru', 'r.id', '=', 'ru.role_id')
                    ->join('users as u', 'u.id', '=', 'ru.user_id')
                    ->where('u.id', $item->user_id)
                    ->get();

                if (count($roles) > 0) {
                    $roles = $roles[0]->description;
                } else {
                    $roles = '';
                }

                $supervisor = str_contains($roles, 'superuser');
                $mini_terminal = str_contains($roles, 'mini_terminal');
                
                $description = json_decode($item->description);

                $user_list = [
                    'id' => $item->user_id, 
                    'user' => $item->user_name, 
                    'description' => $item->user_description,
                    'roles' => $roles
                ];

                \Log::info("Información de usuario:", [json_encode($user_list)]);

                $query_1 = '';
                $query_2 = '';

                if (count($description) > 1) {
                    $query_1 = $description[0];
                    $query_2 = $description[1];
                } else {
                    $query_1 = $description[0];
                }

                \Log::info("ejecutando conjunto de selects n°. $i:");



                \Log::info("Ejecutando query 1...");

                $query_1 = (strlen($query_1) > 0) ? \DB::select($query_1) : [];

                $query_1_count = count($query_1);

                \Log::info("Cantidad de filas del query_1: $query_1_count");



                \Log::info("Ejecutando query 2...");

                $query_2 = (strlen($query_2) > 0) ? \DB::select($query_2) : [];

                $query_2_count = count($query_2);

                \Log::info("Cantidad de filas del query_2: $query_2_count");



                \Log::info("Recorriendo filas de selects:");

                $files = [];

                $now = date('Y_m_d_H_i_s_') . gettimeofday()["usec"];
                $filename = "transaction_export_$now";
                $file = null;
                $file = fopen("$url_temp/$filename.$format", 'w');
                
                fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

                array_push($files, 
                    [
                        'name' => 'Transacciones', 
                        'url_local' => "$url_temp/$filename.$format",
                        'url_link' => "$sftp_link/$filename.$format"
                    ]
                );

                $row = array(
                    'Identificador de transacción', 
                    'Proveedor', 
                    'Tipo', 
                    'Estado', 
                    'Fecha', 
                    'Hora', 
                    'Valor de transacción', 
                    'Valor de comisión', 
                    'Código de pago', 
                    'Forma de pago', 
                    'Identificador de transacción', 
                    'Número de factura', 
                    'Sede', 
                    'Red', 
                    'Referencia 1', 
                    'Referencia 2', 
                    'Código de cajero'
                );

                fputcsv($file, $row, ";");

                foreach($query_1 as $query_1_item) {

                    if($query_1_item->service_source_id == 0) {
                        $query_1_item->proveedor = $query_1_item->provider;
                        $query_1_item->tipo = $query_1_item->servicio;
                    }

                    $row = array(
                        $query_1_item->id,
                        $query_1_item->proveedor,
                        $query_1_item->tipo,
                        $query_1_item->estado,
                        $query_1_item->fecha,
                        $query_1_item->hora,
                        $query_1_item->valor_transaccion,
                        //$query_1_item->commission_amount,
                        0, // commission_amount
                        $query_1_item->cod_pago,
                        $query_1_item->forma_pago,
                        $query_1_item->identificador_transaccion,
                        $query_1_item->factura_nro,
                        $query_1_item->sede,
                        $query_1_item->owner_id,
                        $query_1_item->ref1,
                        $query_1_item->ref2,
                        $query_1_item->codigo_cajero,
                    );

                    fputcsv($file, $row, ";");
                }

                fclose($file);

                if (!$mini_terminal or in_array($item->user_id, $list_ids, true)) {

                    $now = date('Y_m_d_H_i_s_') . gettimeofday()["usec"];
                    $filename = "movements_export_$now";
                    $file = null;
                    $file = fopen("$url_temp/$filename.$format", 'w');
                    fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

                    array_push($files, 
                        [
                            'name' => 'Movimientos', 
                            'url_local' => "$url_temp/$filename.$format",
                            'url_link' => "$sftp_link/$filename.$format"
                        ]
                    );

                    $row = array(
                        'Identificador de movimiento', 'Identificador de transacción', 'Identificador de atms_parts', 'Acción', 'Cantidad', 'Valor',
                        'Dinero virtual', 'Identificador de payments', 'Fecha', 'Hora'
                    );

                    fputcsv($file, $row, ";");

                    foreach($query_2 as $query_2_item) {

                        $row = array(
                            $query_2_item->id,
                            $query_2_item->transaction_id,
                            $query_2_item->atm,
                            $query_2_item->accion,
                            $query_2_item->cantidad,
                            $query_2_item->valor,
                            $query_2_item->dinero_virtual,
                            $query_2_item->payments_id,
                            $query_2_item->fecha,
                            $query_2_item->hora
                        );
        
                        fputcsv($file, $row, ";");
                    }

                    fclose($file);
                }

                \Log::info("Archivos generados:", [$files]);

                for ($i = 0; $i < count($files); $i++) {

                    $file_path = $files[$i]['url_local'];  
                        
                    $base_name_data_file = basename($file_path);

                    \Log::info("URL: sftp://$sftp_server:$sftp_port$sftp_remote_dir/$base_name_data_file");

                    $ch = curl_init("sftp://$sftp_server:$sftp_port$sftp_remote_dir/$base_name_data_file");

                    $fh = fopen($file_path, 'r');
                    
                
                    if ($fh) {
                        curl_setopt($ch, CURLOPT_USERPWD, "$sftp_user_name:$sftp_user_password");
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        curl_setopt($ch, CURLOPT_UPLOAD, true);
                        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
                        curl_setopt($ch, CURLOPT_INFILE, $fh);
                        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
                        curl_setopt($ch, CURLOPT_VERBOSE, true); 
                                                
                        $verbose = fopen('php://temp', 'w+');
                        curl_setopt($ch, CURLOPT_STDERR, $verbose);

                        $response = curl_exec($ch);
                        $error = curl_errno($ch);

                        curl_close($ch);

                        if ($response) {
                            \Log::info("Archivo guardado en el sftp.");
                            fclose($fh);
                            \Log::info("Archivo cerrado.");
                            unlink($file_path);
                            \Log::info("Archivo eliminado.");
                        } else {
                            rewind($verbose);
                            $verbose_log = stream_get_contents($verbose);

                            $error_detail = [
                                'message' => 'Archivo no guardado en el sftp.',
                                'verbose_log' => $verbose_log,
                                'response_ftp' => $response,
                                'error' => $error
                            ];

                            \Log::info($error_detail);
                            break;
                        }

                        \Log::info("Archivo procesado.");
                    }
                }

                $template = 'mails.link_export_file';

                $data = [
                    'files' => $files,
                    'title' => "Estimado usuario $item->user_description",
                    'sub_title' => 'Este es el link de descarga del archivo que fué generado desde el reporte Histórico de transacciones.'
                ];

                $function = function ($message) use ($item) {
                    $message->to($item->user_mail, "$item->user_description")->subject('[EGLOBAL] Enlace de archivo.');
                };

                \Log::info('Enviando mail...');

                Mail::send($template, $data, $function);

                \Log::info("MAIL ENVIADO PARA:", [json_encode($user_list)]);

                \DB::table('select_to_manage')
                    ->where('id', $item->id)
                    ->update([
                        'files' => json_encode($files),
                        'status' => true,
                        'updated_at' => Carbon::now()
                    ]);

                \Log::info('registro de select_to_manage actualizado.');

                $i++;

                $error_detail = [
                    'message' => 'Sin errores'
                ];
            } catch(\Exception $e) {
                $error_detail = [
                    'from' => 'CMS',
                    'message' => '[Exportador de archivos] Ocurrió un error la querer enviar los archivos al correo.',
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'line' => $e->getLine()
                ];
        
                \Log::error($error_detail['message'], [$error_detail]);
            }
        }

        \Log::info('--------------------------------------------------');
        \Log::info("Fin de la ejecución del comando $this->signature.");
        $this->info("Fin de la ejecución del comando $this->signature.");
    }
}