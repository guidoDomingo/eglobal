<?php

namespace App\Http\Controllers\Conciliators;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Session;
use App\Services\Conciliators\BallotConciliatorServices;
use Carbon\Carbon;
use Excel;

class BallotConciliatorController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    /**
     * @var class $service: Conciliador de boleta Services
     * @global object 
     */
    protected $service;

    /**
     * Lista global de bancos
     */
    protected $list_banks;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new BallotConciliatorServices();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ballot_conciliator(Request $request)
    {
        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $data = [
            'open_modal' => 'no',
            'list' => null,
            'date' => null,
            'method' => 'index',
            'inputs' => $inputs
        ];

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($data);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return view('conciliators.ballot_conciliator', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function ballot_conciliator_create(Request $request)
    {
        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => 'Archivos obtenidos correctamente.',
            'message_type' => 'message',
            'date' => null,
            'files' => null,
            'open_modal' => 'si',
            'method' => 'create',
            'inputs' => $inputs,
            'list' => []
        ];

        try {
            $files = $request['files'];
            $timestamp = $request['timestamp'];

            $response['date'] = $timestamp;
            $response['files'] = $files;

            $response['list'] = json_encode($this->service->get_record_validations($files, $timestamp, true));

            \Log::info('ballot_conciliator_create:', [$response['list']]);
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Error al crear datos de documento.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        if ($response['error'] == true) {
            $response['message_type'] = 'error_message';
            $response['open_modal'] = 'no';
        }

        $data = $response;

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($data);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        \Session::flash($response['message_type'], $response['message']);

        return view('conciliators.ballot_conciliator', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function ballot_conciliator_store(Request $request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        try {
            $json = $request['json'];
            $list = json_decode($json, true);

            $message_type = 'message';
            $message = 'No se actualizó ningún registro.';

            $number_of_updated_records = 0;
            $data_to_excel = [];
            $user_info = $this->user->username . ' - ' . $this->user->description;

            foreach ($list as $key => $name) {

                $sub_list = $list[$key]['data'];

                for ($j = 0; $j < count($sub_list); $j++) {
                    $item            = $sub_list[$j];
                    $bank_id         = $item['bank_id'];
                    $bank            = $item['bank'];
                    $payment_type_id = $item['payment_type_id'];
                    $payment_type    = $item['payment_type'];
                    $id              = $item['id'];
                    $ballot_number   = $item['ballot_number'];
                    $amount          = $item['amount'];
                    $status          = $item['status'];
                    $date            = $item['date'];
                    $correct_data    = $item['correct_data'];
                    $conciliate      = ($status == null) ? 'No' : 'Si';
                    $correct         = ($correct_data) ? 'Si' : 'No';

                    if ($conciliate == 'No' and $correct == 'Si') {

                        $update_date = Carbon::now();

                        try {

                            \DB::beginTransaction();

                            $boletas_depositos_update = [
                                'estado'     => true,
                                'conciliado' => false,
                                'updated_at' => $update_date,
                                'updated_by' => $this->user->id
                            ];

                            $update = \DB::table('boletas_depositos')
                                ->where('id', $id)
                                ->update($boletas_depositos_update);

                            \DB::commit();

                            \Log::info("\n\nSe actualizó boleta de deposito con id: $id, con los siguientes parámetros:", [$boletas_depositos_update]);

                            $data = [$bank, $payment_type, $ballot_number, $amount, $user_info, $date, $update_date];
                            array_push($data_to_excel, $data);
                            $number_of_updated_records++;

                            \Log::info('Boleta de deposito agregado a la lista:', [$data]);
                        } catch (\Exception $e) {

                            \DB::rollback();

                            $message = 'Error al actualizar boletas de depositos.';
                            $message_type = 'error_message';

                            $error_detail = [
                                'from' => 'CMS',
                                'message' => "Error al actualizar la boleta con id: $id",
                                'exception' => $e->getMessage(),
                                'file' => $e->getFile(),
                                'class' => $class,
                                'function' => $function,
                                'line' => $e->getLine()
                            ];

                            $error_detail = json_encode($error_detail);

                            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
                        }
                    }
                }
            }

            if ($number_of_updated_records > 0) {
                $message = "Se actualizaron $number_of_updated_records registro/s.";
            } else {
                $message = 'No se actualizó ningún registro.';
            }

            if (count($data_to_excel) > 0) {
                $filename = 'conciliacion_' . time();
                $columnas = array(
                    'Banco', 'Tipo', 'Número de boleta', 'Monto', 'Usuario', 'Creación del Registro', 'Actualización del Registro'
                );

                $excel = new ExcelExport($data_to_excel,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($data_to_excel) {
                //     $excel->sheet('sheet1', function ($sheet) use ($data_to_excel) {
                //         $sheet->rows($data_to_excel, false);
                //         $sheet->prependRow(array(
                //             'Banco', 'Tipo', 'Número de boleta', 'Monto', 'Usuario', 'Creación del Registro', 'Actualización del Registro'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            }
        } catch (\Exception $e) {

            $message = 'Error al crear datos de documento.';
            $message_type = 'error_message';

            $error_detail = [
                'from' => 'CMS',
                'message' => 'Error al actualizar boletas de depósitos conciliadas.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        $data = [
            'open_modal' => 'no',
            'list'       => null,
            'date'       => null,
            'method'     => 'store'
        ];

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($data);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        \Session::flash($message_type, $message);
        return view('conciliators.ballot_conciliator', compact('data'));
    }

    /**
     * Cancel
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function ballot_conciliator_cancel(Request $request)
    {
        $message = 'Validación de documentos cancelada.';
        $message_type = 'message';

        $data = [
            'open_modal' => 'no',
            'list' => null,
            'method' => 'cancel'
        ];

        \Session::flash($message_type, $message);
        return view('conciliators.ballot_conciliator', compact('data'));
    }
}
