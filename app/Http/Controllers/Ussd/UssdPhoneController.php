<?php

namespace App\Http\Controllers\Ussd;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//use App\Services\UssdPhoneServices;
use Carbon\Carbon;

class UssdPhoneController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_phone_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        //$this->ussd_phone_service = new UssdPhoneServices();
    }

    /**
     * Informe que muestra los teléfonos con saldo actual
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_phone_report(Request $request)
    {
        $records_list = \DB::table('ussd.menu_ussd_phone as mup')
            ->select(
                'muo.description as operator',
                'mup.id',
                'mup.phone_number',
                'mup.signal',
                'mup.port',
                'mup.messages',
                'mup.final_message',
                'mup.current_amount',
                'mup.minimum_amount',
                't.id as transaction_id',
                't.status as transaction_status',
                \DB::raw("(case when (mup.occupied) then 'Ocupado' else 'Libre' end) as occupied"),
                \DB::raw("(case when (mup.reg = 'REGISTER_OK') then 'Activo' else 'Inactivo' end) as reg"),
                \DB::raw("(case when (mup.status) then 'Activo' else 'Inactivo' end) as status"),
                \DB::raw("trim(replace(to_char(mup.current_amount, '999G999G999G999'), ',', '.')) as current_amount_view"),
                \DB::raw("trim(replace(to_char(mup.minimum_amount, '999G999G999G999'), ',', '.')) as minimum_amount_view"),
                \DB::raw("to_char(mup.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                \DB::raw("
                    coalesce((
                        select 
                            count(t.id)
                        from transactions as t 
                        join ussd.menu_ussd_detail_client as mudc on t.id = mudc.transaction_id and mup.id = mudc.menu_ussd_phone_id                    
                        where to_char(t.created_at, 'DD/MM/YYYY') = to_char(now(), 'DD/MM/YYYY')
                        group by mudc.menu_ussd_phone_id
                    ), 0) as transaction_count_of_day
                ")
            )
            ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mup.menu_ussd_operator_id')
            ->leftjoin('transactions as t', 't.id', '=', 'mup.current_transaction_id')
            ->orderBy('muo.description', 'desc')
            ->orderBy('transaction_count_of_day', 'desc')
            ->get();

        $records_list = array_map(function ($value) {
            return (array) $value;
        }, $records_list->toArray());

        $list = $records_list;

        $data = [
            'list' => $list,
        ];

        return view('ussd.ussd_phone_report', compact('data'));
    }

    /**
     * Metodo para modificar el servicio del ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_phone_set_status(Request $request)
    {
        $message = 'Teléfono actualizado correctamente.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $menu_ussd_phone_status = $request['status'];

            \DB::beginTransaction();

            //Verificar si el operador está activo para habilitarse.
            $menu_ussd_phone = \DB::table('ussd.menu_ussd_phone')
                ->select('menu_ussd_operator_id')
                ->where('id', $id)
                ->get();

            if (count($menu_ussd_phone) > 0) {
                $menu_ussd_operator_id = $menu_ussd_phone[0]->menu_ussd_operator_id;

                $menu_ussd_operator = \DB::table('ussd.menu_ussd_operator')
                    ->where('id', $menu_ussd_operator_id)
                    ->where('status', true)
                    ->get();

                $menu_ussd_operator = (array) $menu_ussd_operator;

                if (count($menu_ussd_operator) <= 0) {
                    \DB::table('ussd.menu_ussd_operator')
                        ->where('id', $menu_ussd_operator_id)
                        ->update([
                            'status' => true,
                            'user_id' => $this->user->id,
                            'updated_at' => Carbon::now()
                        ]);
                } else {
                    \Log::info('Operador no actualizado...');
                }
            }

            \DB::table('ussd.menu_ussd_phone')
                ->where('id', $id)
                ->update([
                    'status' => $menu_ussd_phone_status,
                    'user_id' => $this->user->id,
                    'updated_at' => Carbon::now()
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            $message = 'Ocurrió un problema al modificar el registro';
            $error = true;

            \DB::rollback();
        }

        $data = [
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail
        ];

        return $data;
    }
}
