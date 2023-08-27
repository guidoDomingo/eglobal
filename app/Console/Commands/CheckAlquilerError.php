<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;

class CheckAlquilerError extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'miniterminales:checkAlquilerError';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chequea los pagos de alquiler sin generar';

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
        try {

            $detalleAlquiler = [];
            $atms_ids = [];

            //return $gruop_id['group_ids'];
            $alquileres = \DB::select(
            "
                select  ca.id, 
                        bg.description as grupo, 
                        bg.ruc as ruc, 
                        num_cuota as numero_cuota, 
                        ca.importe, 
                        alquiler_id, 
                        fecha_vencimiento 
                    from 
                        cuotas_alquiler ca 
                    join 
                        alquiler a on a.id = ca.alquiler_id 
                    join 
                        business_groups bg on bg.id = a.group_id
                    where 
                        a.activo is true 
                        and a.deleted_at is null 
                        and ca.cod_venta is null 
                        and ca.fecha_vencimiento < now()
                order by fecha_vencimiento desc"
            );

            //return $resumen_transacciones_groups;


            if (!empty($alquileres)) {
                foreach ($alquileres as $alquiler) {

                    $obj = [
                        "id_alquiler" => $alquiler->id,
                        "cliente" => $alquiler->grupo,
                        "ruc" => $alquiler->ruc,
                        "numero_cuota" => $alquiler->numero_cuota,
                        "importe" => $alquiler->importe,
                        "alquiler_id" => $alquiler->alquiler_id,
                        "fecha_vencimiento" => $alquiler->fecha_vencimiento,
                    ];

                    array_push($detalleAlquiler, $obj);
                }

                //seccion de mail y excel

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


                $url_temp = public_path('exports');

                $files = [];

                $now = date('Y_m_d_H_i_s_') . gettimeofday()["usec"];
                $filename = "alquileres_pendientes_$now";
                $file = null;
                $file = fopen("$url_temp/$filename.$format", 'w');

                fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

                array_push(
                    $files,
                    [
                        'name' => 'Cuotas de Alquileres Pendientes Hoy',
                        'url_local' => "$url_temp/$filename.$format",
                        'url_link' => "$sftp_link/$filename.$format"
                    ]
                );

                $row = array(
                    'ID', 'Cliente', 'RUC', 'Numero de Cuota', 'Importe', 'ID Alquiler', 'Fecha de Vencimiento'
                );

                fputcsv($file, $row, ";");

                foreach ($detalleAlquiler as $detalle) {
                    $row = array(
                        $detalle['id_alquiler'],
                        $detalle['cliente'],
                        $detalle['ruc'],
                        $detalle['numero_cuota'],
                        $detalle['importe'],
                        $detalle['alquiler_id'],
                        $detalle['fecha_vencimiento']
                    );
                    fputcsv($file, $row, ";");
                }
                fclose($file);


                \Log::info("Archivo generados");
                \Log::info($files);

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


                $template = 'mails.alert_alquiler_pendientes';

                $data = [
                    'files' => $files,
                    'title' => "Lista de Cuotas Pendientes de Alquiler",
                    'sub_title' => 'Este es el link de descarga del archivo que fué generado de las Cuotas pendientes de Alquiler.'
                ];

                $function = function ($message) {
                    $user_email = 'sistemas@eglobalt.com.py';
                    $user_name  = 'Admin';
                    $message->to($user_email, $user_name)->cc('rzacarias@eglobalt.com.py')->subject('[EGLOBAL] Cuotas Pendientes de Alquiler.');
                };

                \Log::info('Enviando mail...');

                Mail::send($template, $data, $function);

                \Log::info("MAIL ENVIADO PARA");

                $error_detail = [
                    'message' => 'Sin errores'
                ];
            } else {
                \Log::info('No Existen Cuotas Pendientes de Alquiler');
            }
        } catch (\Exception $e) {
            \Log::debug('Error en detalleAlquilerPendientes: ' . $e);
            //\Log::info('[controlMini]', ['e' => $e]);
            $response['error'] = true;
            $response['message'] = 'Ha ocurrido un error';
            return $response;
        }
    }
}
