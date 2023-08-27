<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services;

use App\Exports\ExcelExport;
use App\Models\Atm;
use Carbon\Carbon;
use App\Models\AtmStatusHistory;
use App\Models\Owner;
use App\Models\Branch;
use Excel;

class AtmStatusServices
{

    public function insertStatusAtm($atm_id, $comments, $status)
    {
        try {

            $atm_status = \DB::table('atm_status_history')->insert([
                'atm_id'            => $atm_id,
                'comments'          => $comments,
                'status'            => $status,
                'created_at'        =>  Carbon::now()
            ]);
            if ($atm_status) {
                $response = [
                    'error' => false,
                    'message' => 'Se registro correctamente',
                    'message_user' => ''
                ];
                \Log::info('[ATM Services]--- Se registro correctamente el estado del atm');
                return $response;
            }
        } catch (\Exception $e) {
            \Log::debug('[ATM Services]', ['result' => $e]);
            $response = [
                'error' => true,
                'message' => 'Error al actualizar la notificacion ',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function updateStatusAtm($id, $comments, $created_at)
    {

        try {
            $atm_status = \DB::table('atm_status_history')
                ->where('id', $id)
                ->update([
                    'diferencia'        => Carbon::now()->diffInMinutes(Carbon::parse($created_at)),
                    'updated_at'        => Carbon::now()
                ]);
            if ($atm_status) {
                $response = [
                    'error' => false,
                    'message' => 'Se actualizo correctamente',
                    'message_user' => '',
                ];
                \Log::info('[ATM Services]--- Se actualizo correctamente el estado del atm');
                return $response;
            }
        } catch (\Exception $e) {
            \Log::debug('[ATM Services]', ['result' => $e]);
            $response = [
                'error' => true,
                'message' => 'Error al actualizar la notificacion ',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function cierreYapertura($atm_id, $front = false, $comments = Null, $status = Null)
    {
        $response = [
            'error' => true,
            'message' => 'No se realizo ningun cambio',
            'message_user' => ''
        ];
        try {

            $atm_status = \DB::table('atm_status_history')
                ->where('atm_id', $atm_id)
                ->orderBy('id', 'desc')
                ->first();

            if (isset($atm_status)) {
                //si front el false se le da valor del atm_status obtenidos
                $comments = !$front ? $atm_status->comments : $comments;
                $status   = !$front ? $atm_status->status   : $status;

                $update =   $this->updateStatusAtm($atm_status->id, $comments, $atm_status->created_at);
                if ($update['error'] == false) {
                    $insert =  $this->insertStatusAtm($atm_id, $comments, $status);

                    if ($insert['error'] == false) {
                        $response['error']   = false;
                        $response['message'] = 'Se realizo correctamente';
                    }
                }
            } else {
                $insert =  $this->insertStatusAtm($atm_id, $comments = 'En linea', $status = 'Online');

                if ($insert['error'] == false) {

                    $response['error']   = false;
                    $response['message'] = 'Se realizo correctamente';
                }
            }
        } catch (\Exception $e) {
            \Log::debug('[ATM Services]', ['result' => $e]);

            $response['message'] = $e;
        }

        return $response;
    }

    public function getStatusHistory()
    {
        $data = null;

        $response = $this->getStatusHistorySearch($data);
        return $response;
    }

    public function getStatusHistorySearch($input)
    {
        try {
            if (empty($input)) {
                $fechaActual     = Carbon::now();
                $desde           = date("Y-m-d 00:00:00", strtotime($fechaActual));
                $hasta           = date("Y-m-d 23:59:59", strtotime($fechaActual));
                $reservationtime = $desde . ' - ' . $hasta;

                $owner_id    = 0;
                $status_id   = 'Todos';
                $atm_id      = 0;
                $branche_id  = 0;
                $tipo_id     = 0;
            } else {
                $atm_id           = $input['atm_id'];
                $owner_id         = $input['owner_id'];
                $status_id        = $input['status_id'];
                $reservationtime  = $input['reservationtime'];
                $branche_id       = $input['branches_id'];
                $tipo_id          = $input['tipo_id'];

                $daterange    = explode(' - ',  str_replace('/', '-', $reservationtime));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $desde        = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $hasta        = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            $owners = Owner::whereIn('id', [2, 11, 16])->whereNull('deleted_at')->pluck('name', 'id')->prepend('Todos', '0')->toArray();

            $branches = Branch::whereIn('owner_id', [2, 11, 16])->whereNull('deleted_at')->pluck('description', 'id')->prepend('Todos', '0')->toArray();

            $atms = Atm::whereIn('owner_id', [2, 11, 16])->whereNull('deleted_at')->pluck('name', 'id')->prepend('Todos', '0')->toArray();

            $atmStatus =  \DB::table('atm_status_history')
                ->select('atms.id', 'atms.name', 'users.description', 'atm_status_history.comments', 'atm_status_history.status', 'atm_status_history.diferencia', 'atm_status_history.created_at', 'atm_status_history.updated_at')
                ->join('atms', 'atms.id', '=', 'atm_status_history.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups','business_groups.id','=','branches.group_id')
                ->leftjoin('users', 'users.id', '=', 'branches.user_id')
                ->where(function ($query) use ($branche_id) {
                    if (!empty($branche_id) &&  $branche_id <> 0) {
                        $query->where('branches.id', '=', $branche_id);
                    }
                })
                ->where(function ($query) use ($owner_id) {
                    if (!empty($owner_id) &&  $owner_id <> 0) {
                        $query->where('atms.owner_id', '=', $owner_id);
                    }
                })
                ->where(function ($query) use ($atm_id) {
                    if (!empty($atm_id) &&  $atm_id <> 0) {
                        $query->where('atm_status_history.atm_id', '=', $atm_id);
                    }
                })
                ->where(function ($query) use ($tipo_id) {
                    if (!empty($tipo_id) &&  $tipo_id <> 0) {
                        if($tipo_id ==  1){
                        $query->where('business_groups.manager_id', '=', 1);
                        }else{
                         $query->where('business_groups.manager_id', '<>', 1); 
                        }
                    }
                })
                ->where(function ($query) use ($status_id) {
                    if (!empty($status_id) &&  $status_id <> 'Todos') {
                        $query->where('atm_status_history.status', $status_id);
                    }
                })
                ->whereBetween('atm_status_history.created_at', [$desde, $hasta])
                ->orderBy('atm_status_history.id', 'DESC')
                ->get();
            
       
           if(isset($input['search'])){
                if ($input['search'] == 'download') {
                    $excelData = json_decode(json_encode($atmStatus), true);

                    $filename = 'atmsEstadoHistoricos_' . time();
                    $columnas = array(
                        '#', 'Nombre', 'Encargado', 'Comentario', 'Estado', 'Tiempo Transcurrido (Min)', 'Inicio', 'Fin'
                    );
                    if ($excelData && !empty($excelData)) {

                        $excel = new ExcelExport($excelData,$columnas);
                        return Excel::download($excel, $filename . '.xls')->send();
                        // Excel::create($filename, function ($excel) use ($excelData) {
                        //     $excel->sheet('Estados', function ($sheet) use ($excelData) {
                        //         $sheet->rows($excelData, false);
                        //         $sheet->prependRow(array(
                        //             '#', 'Nombre', 'Encargado', 'Comentario', 'Estado', 'Tiempo Transcurrido (Min)', 'Inicio', 'Fin'

                        //         ));
                        //     });
                        // })->export('xls');
                        // exit();
                    }
                }
           }

            $tipoStatus = AtmStatusHistory::pluck('status', 'status')->prepend('Todos', 'Todos')->toArray();
           
            $result = [
                'target'          => 'Historico estados ATM',
                'owners'          => $owners,
                'status'          => $tipoStatus,
                'atmStatus'       => $atmStatus,
                'reservationtime' => $reservationtime,
                'owner_id'        => $owner_id,
                'status_id'       => $status_id,
                'atm_id'          => $atm_id,
                'branche_id'      => $branche_id,
                'branches'        => $branches,
                'atms'            => $atms,
                'tipo_id'         => $tipo_id
            ];
           
            return $result;
        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
        }
    }
}
