<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Info;

use Excel;

class StatActivityServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
    }

    /**
     * Función inicial
     */
    public function index($request)
    {
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $this->user = \Sentinel::getUser();

        if (!$this->user->hasAccess('info_stat_activity')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        $message = '';
        $headers = [];
        $records = [];
        $get_info = true;

        try {

            $query = "
                select
                    sq.procesos as \"Procesos\",
                    sq.json ->> 'pid' as \"ID-Consulta\",
                    sq.json ->> 'consulta' as \"Consulta\",
                    sq.json ->> 'inicio' as \"Inicio\",
                    (sq.json ->> 'duracion')::numeric as \"Duración (Segundos)\",
                    sq.json ->> 'usuario' as \"Usuario\",
                    sq.json ->> 'aplicacion' as \"Aplicacion\",
                    sq.json ->> 'ip' as \"IP\"
                from (
                    select
                        json_build_object(
                            'consulta',
                                case
                                    when psa.query = '' then 'Consulta vacía.'
                                    else lower(psa.query)
                                end, 
                            'inicio', to_char(psa.query_start, 'DD/MM/YYYY HH24:MI:SS'), 
                            'duracion', abs(date_part('epoch', now() - psa.query_start)), 
                            'usuario', psa.usename, 
                            'aplicacion', psa.application_name, 
                            'ip', psa.client_addr,
                            'pid', 'select pg_terminate_backend(' || psa.pid || ');'
                        ) as json,
                        (
                            select
                                case when count(psa2.query) > 0 then count(psa2.query) else 1 end
                            from pg_stat_activity psa2
                            where psa2.query = psa.query
                            and psa2.query_start = psa.query_start
                        ) as procesos
                    from
                        pg_stat_activity psa
                    where
                        psa.state = 'active'
                        and psa.query !~~* '%view_pg_stat_activity%'
                        and psa.query !~~* '%ID-Consulta%'
                ) sq
                where (sq.json ->> 'ip') is not null
                group by
                    (sq.json ->> 'consulta'),
                    (sq.json ->> 'inicio'),
                    (sq.json ->> 'duracion'),
                    (sq.json ->> 'usuario'),
                    (sq.json ->> 'aplicacion'),
                    (sq.json ->> 'ip'),
                    (sq.json ->> 'pid'),
                    sq.procesos
                order by sq.procesos desc;
            ";

            $records = \DB::select($query);

            if (count($records) > 0) {
                $records = json_decode(json_encode($records), true);
                $headers = array_keys($records[0]);

                $data['lists']['records'] = $records;
                $data['lists']['headers'] = $headers;
            } else {
                $message = 'No hay consultas activas.';
            }

        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));

            $message = 'Ocurrió un error: (' . $e->getMessage() . ')';
        }

        $data = [
            'lists' => [
                'headers' => $headers,
                'records' => $records,
            ],
            'message' => $message
        ];

        return view('info.stat_activity', compact('data'));
    }
}
