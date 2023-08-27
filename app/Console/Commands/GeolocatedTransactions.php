<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Mail;
use Carbon\Carbon;


class GeolocatedTransactions extends Command
{
    /**
     * Este comando trae los selects a convertir en formato excel
     *
     * @var string
     */

    protected $signature = 'paymentGW:GeolocatedTransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista de transacciones geolocalizadas.';

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

        \Log::info("Iniciando proceso: Envío de archivo 'Geolocalización de transacciones a Tigo':");

        try {

            ini_set('max_execution_time', 0);
            ini_set('max_execution_time', 0);
            ini_set('client_max_body_size', '20M');
            ini_set('max_input_vars', 10000);
            ini_set('upload_max_filesize', '20M');
            ini_set('post_max_size', '20M');
            ini_set('memory_limit', '-1');
            set_time_limit(3600);

            $date_now = date('d-m-Y');
            $date_past = strtotime('-1 day', strtotime($date_now));
            $date_past = date('d-m-Y', $date_past);

            /**
             * Datos del archivo
             */
            $url_temp = public_path('exports');
            //$date = date('d_m_Y') . '_';
            //$time = date('H_i_s');
            $now = "$date_past";
            $filename = "transacciones_geolocalizadas_$now";
            $format = 'csv';

            $file_path = "$url_temp/$filename.$format";

            /**
             * Generación del archivo localmente
             */
            $file = null;
            $file = fopen($file_path, 'w');

            /**
             * Comandos hexadecimales para evitar los caracteres raros
             */
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            $row = array(
                'Identificador de transaccion',
                'Descripcion del Servicio',
                'Fecha de Transaccion',
                'ID de Atm',
                'Monto',
                'Estado',
                'Numero de Factura',
                'Identificador Externo',
                'Referencia 1',
                'Referencia 2',
                'Latitud',
                'Longitud'
            );

            fputcsv($file, $row, ";");

            $query = "
                select
                    t.id,
                    m.descripcion || ' - ' || sm.descripcion as descripcion_servicio,
                    t.created_at as fecha_transaccion,
                    a.id as atm_id,
                    t.amount as monto,
                    t.status as estado,
                    t.factura_numero,
                    t.identificador_transaction_id as identificador_externo_id,
                    t.referencia_numero_1,
                    t.referencia_numero_2,
                    b.latitud,
                    b.longitud
                from transactions t join atms a on a.id = t.atm_id
                join points_of_sale pos on pos.atm_id = a.id
                join branches b on b.id = pos.branch_id
                join servicios_x_marca sm on sm.service_source_id = t.service_source_id and t.service_id = sm.service_id
                join marcas m on m.id = sm.marca_id
                where to_char(t.created_at, 'DD/MM/YYYY') = to_char((now() - '1 day'::interval), 'DD/MM/YYYY')
                    and t.status= 'success'
                    and (t.service_id = 3 and t.service_source_id = 0 or
                        t.service_id = 89 and t.service_source_id = 0 or
                        t.service_id = 7 and t.service_source_id = 0 or
                        t.service_id = 8 and t.service_source_id = 0 or
                        t.service_id = 9 and t.service_source_id = 0)
            ";

            $query_list = \DB::select($query);
            $query_list = json_decode(json_encode($query_list), true);

            //\Log::debug('Ubicaciones de Transacciones envíadas a TIGO:', [$query_list]);

            foreach ($query_list as $row) {

                $row['descripcion_servicio'] = trim(preg_replace('/\s+/', ' ', $row['descripcion_servicio']));
                $row['latitud'] = str_replace(',',".",$row['latitud']);
                $row['longitud'] = str_replace(',',".",$row['longitud']);

                fputcsv($file, $row, ";");
            }

            fclose($file);


            $tigo_sftp_server = env('TIGO_SFTP_SERVER');
            $tigo_sftp_port = env('TIGO_SFTP_PORT');
            $tigo_sftp_user_name = env('TIGO_SFTP_USER_NAME');
            $tigo_sftp_user_password = env('TIGO_SFTP_USER_PASSWORD');
            $tigo_sftp_folder = env('TIGO_SFTP_FOLDER');

            $base_name_data_file = basename($file_path);

            $sftp_url = "sftp://$tigo_sftp_server:$tigo_sftp_port/$tigo_sftp_folder/$base_name_data_file";
            $sftp_user_and_password = "$tigo_sftp_user_name:$tigo_sftp_user_password";
            //\Log::info("sftp_url: $sftp_url, sftp_user_and_password: $sftp_user_and_password");

            $ch = curl_init($sftp_url);

            /**
             * Se abre al archivo para ser leido
             */

            $fh = fopen($file_path, 'r');

            if ($fh) {

                curl_setopt($ch, CURLOPT_USERPWD, $sftp_user_and_password);
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

                /*$verbose = fopen('php://temp', 'w+');
    
                $curl = curl_init();
    
                $curl_parameters = [
                    CURLOPT_URL => $sftp_url,
                    CURLOPT_USERPWD => $sftp_user_and_password,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_UPLOAD => true,
                    CURLOPT_PROTOCOLS => CURLPROTO_SFTP,
                    CURLOPT_INFILE => $file,
                    CURLOPT_INFILESIZE => filesize($file_path),
                    CURLOPT_VERBOSE => true,
                    CURLOPT_STDERR => $verbose
                ];
    
                curl_setopt_array($curl, $curl_parameters);
    
                $response = curl_exec($curl);
                $error = curl_errno($curl);
                curl_close($curl);*/

                if ($response) {
                    $date = date('d/m/Y');
                    $time = date('H:i:s');
                    $now = "el $date a las $time.";

                    \Log::info("Archivo guardado en el sftp de tigo $now");
                    fclose($fh);
                    \Log::info("Archivo cerrado $now");
                    unlink($file_path);
                    \Log::info("Archivo eliminado $now");
                } else {
                    rewind($verbose);
                    $verbose_log = stream_get_contents($verbose);

                    $error_detail = [
                        'message' => 'Archivo no guardado en el sftp de tigo.',
                        'verbose_log' => $verbose_log,
                        'response_ftp' => $response,
                        'error' => $error
                    ];

                    \Log::error("\nError en " . __FUNCTION__ . ": \nDetalles: " . json_encode($error_detail));
                }

                \Log::info("Archivo procesado el $now");
            }
        } catch (\Exception $e) {

            $error_detail = [
                'message' => 'Ocurrió un error al generar el archivo de geolocalización de transacciones para TIGO.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("\nError en " . __FUNCTION__ . ": \nDetalles: " . json_encode($error_detail));
        }

        \Log::info("Fin de proceso: Envío de archivo 'Geolocalización de transacciones a Tigo'.");
    }
}
