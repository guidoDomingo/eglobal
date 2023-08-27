<?php

namespace App\Http\Controllers\TerminalInteractionMonitoring;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;

class ChangePinController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Valida inicialmente al usuario
     */
    public function index(Request $request)
    {
        $user_id = $this->user->id;
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction_monitoring_change_pin')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        //-----------------------------------------------------

        $data = [
            'user_id' => $user_id,
            'status' => false,
            'pin_status' => 'new',
            'message' => ''
        ];

        try {
            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'status',
                    'pin'
                )
                ->where('user_id', $user_id)
                ->get();

            if (count($terminal_interaction_login) > 0) {

                $terminal_interaction_login_id = $terminal_interaction_login[0]->id;
                $status = $terminal_interaction_login[0]->status;
                $pin = $terminal_interaction_login[0]->pin;

                if ($pin !== null and $pin !== '') {
                    //$data['pin_status'] = 'update';
                }
            } else {
                $data['pin_status'] = 'not_enabled';
                $data['message'] = 'El usuario no está habilitado para hacer interacciones en la terminal.';
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];
    
            \Log::error('Ocurrió un error. Detalles: ' . json_encode($error_detail));
        }

        //$data['pin_status'] = 'not_enabled';
        //$data['message'] = 'El usuario no está habilitado para hacer interacciones en la terminal.';

        if ($data['message'] !== '') {
            \Session::flash('error_message', $data['message']);
        }

        return view('terminal_interaction_monitoring.change_pin.index', compact('data'));
    }

    /**
     * Modificar pin
     */
    public function edit(Request $request)
    {
        $data = [
            'error' => false,
            'message' => '',
            'list' => []
        ];

        try {

            $parameters = $request['parameters'];
            $user_id = $parameters['user_id'];
            $pin = $parameters['pin'];
            $pin_repeat = $parameters['pin_repeat'];
            $pin_status = $parameters['pin_status'];

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'pin'
                )
                ->where('user_id', $user_id)
                ->get();

            if (count($terminal_interaction_login) > 0) {

                if ($pin !== null and $pin !== '' and $pin_repeat !== null and $pin_repeat !== '') {

                    $run = false;
                    $terminal_interaction_login_id = $terminal_interaction_login[0]->id;

                    /*if ($pin_status == 'new') {

                        if ($pin == $pin_repeat) {

                            $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                ->select(
                                    'id'
                                )
                                ->whereRaw("pin = md5('$pin')")
                                ->whereRaw("id != $terminal_interaction_login_id")
                                ->get();

                            if (count($terminal_interaction_pin) <= 0) {
                                $run = true;
                            } else {
                                $data['message'] = 'Otro usuario ya tiene ese pin.';
                            }
                        } else {
                            $data['message'] = 'Los campos no son iguales, volver a ingresar.';
                        }
                    } else if ($pin_status == 'update') {

                        if ($pin !== $pin_repeat) {

                            $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                ->select(
                                    'id'
                                )
                                ->whereRaw("pin = md5('$pin')")
                                ->whereRaw("id = $terminal_interaction_login_id")
                                ->get();

                            if (count($terminal_interaction_pin) > 0) {

                                $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                    ->select(
                                        'id'
                                    )
                                    ->whereRaw("pin = md5('$pin_repeat')")
                                    ->whereRaw("id != $terminal_interaction_login_id")
                                    ->get();
        
                                if (count($terminal_interaction_pin) <= 0) {
                                    $run = true;
                                } else {
                                    $data['message'] = 'El pin nuevo ingresado no es válido.';
                                }
                            } else {
                                $data['message'] = 'Pin antiguo no coincide.';
                            }
                        } else {
                            $data['message'] = 'Pin nuevo no debe ser igual al antiguo.';
                        }
                    }

                    if ($run) {
                        \DB::table('terminal_interaction_login')
                            ->where('id', $terminal_interaction_login_id)
                            ->update([
                                'pin' => \DB::raw("md5('$pin')"),
                                'updated_at' => \DB::raw("now()"),
                            ]);
                    }*/

                    \DB::table('terminal_interaction_login')
                        ->where('id', $terminal_interaction_login_id)
                        ->update([
                            'pin' => \DB::raw("md5('$pin')"),
                            'updated_at' => \DB::raw("now()"),
                        ]);
                } else {
                    $data['message'] = 'Los campos deben estar completos.';
                }
            } else {
                $data['message'] = 'El usuario no cuenta con acceso a interacciones de terminal.';
            }

            if ($data['message'] !== '') {
                $data['error'] = true;
            } else {
                $data['message'] = 'Pin guardado correctamente.';
            }
            
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];
    
            \Log::error('Ocurrió un error. Detalles: ' . json_encode($error_detail));
        }

        return $data;
    }
}
