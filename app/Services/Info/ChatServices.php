<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:25 am
 */

namespace App\Services\Info;

use HttpClient;

class ChatServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
        $this->openai_api_key = '';

        $select = \DB::table('openai_key')
            ->select(
                'user_id',
                'key'
            )
            ->where('user_id', $this->user->id)
            ->get();

        if (count($select) > 0) {
            $this->openai_api_key = $select[0]->key;
        }

        \Log::info("openai_api_key: " . $this->openai_api_key, [$this->user->id]);

        if ($this->openai_api_key == '') {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin Key',
                'explanation' => 'El usuario: ' . $this->user->username . ' no tiene una key de openai.'
            ];

            return view('messages.index', compact('data'));
        }
    }

    /**
     * Función inicial
     */
    public function index($request)
    {
        $user_id = $this->user->id;

        if (!$this->user->hasAccess('info_chat')) {
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

        $content_text_response = '';
        $content_list_text = [];
        $content_text_tables = [];
        $error_detail = [];

        try {

            $t = "'transactions', 'atms', 'balance_atms', 'mt_recibos', 'mt_movimientos'";

            /**
             * Lista de tablas con sus esquemas
             */
            $tables = \DB::select("
                select 
                    (ci.table_name) || ' (' || string_agg(ci.column_name, ', ') || ') ' as description
                from information_schema.columns ci
                where ci.table_name in ($t)
                group by (ci.table_name)
            ");

            $content_list_text = json_encode($content_list_text);
            $content_text_tables = json_encode(json_decode(json_encode($tables), true));

            //$tables = json_encode($tables);

            /*$item = [
                'description' => ''
            ];

            // Agregar el nuevo elemento al principio del arreglo
            array_unshift($tables, $item);

            $tables_aux = "\n";

            for ($i = 0; $i < count($tables); $i++) {
                $item = $tables[$i]['description'];
                $tables_aux .= "# $item\n";
            }

            $tables_descriptions = "
                ### Postgres SQL tables, with their properties:
                #
                $tables_aux
                #
                ### [question]
            ";

            \Log::info("Pregunta generada: \n$tables_descriptions");

            $content_list_text = json_encode($content_list_text);*/

            /*$content_list_text = [
                [
                    "role" => "user",
                    "content" => $tables_descriptions
                ]
            ];

            //\Log::info("Tablas: $tables_aux");

            $parameters = [
                'content_text' => $tables_aux,
                'content_text_list' => $content_list_text
            ];

            $content_text_response_aux = $this->send($parameters);

            $item = [
                "role" => "assistant",
                "content" => $content_text_response_aux
            ];

            \Log::info("Respuesta de ChatGPT: $content_text_response_aux");

            array_push($content_list_text, $item);

            $content_list_text = json_encode($content_list_text);*/
        } catch (\Exception $e) {

            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
        }

        //\Log::info('content_list_text:', [$content_list_text]);

        $data = [
            'inputs' => [
                'user_name' => $this->user->description,
                'content_text' => isset($request['content_text']) ? $request['content_text'] : null,
                'content_text_response' => $content_text_response,
                'content_text_list' => $content_list_text,
                'content_text_tables' => $content_text_tables
            ],
            'error_detail' => $error_detail
        ];

        \Log::info('data:', [$data]);

        return view('info.chat', compact('data'));
    }

    public function send($request)
    {

        $content_text_response = '';

        try {

            $content_text = $request['content_text'];
            $content_text_list = $request['content_text_list'];
            $content_text_tables = $request['content_text_tables'];

            $tables_aux = "";

            for($i = 0; $i < count($content_text_tables); $i++) {
                $item = $content_text_tables[$i]['description'];
                $tables_aux .= "# $item\n";

            }

            \Log::info("content_text_list:", [$content_text_list]);

            $tables_descriptions = "
                ### Postgres SQL tables, with their properties:
                #
                $tables_aux
                #
                ### $content_text
            ";

            //\Log::info("tables_descriptions: $tables_descriptions");
        
            $content_text_list[count($content_text_list) - 1]['content'] = $tables_descriptions;

            \Log::info('content_text_list convertido:', [$content_text_list]);

            if (isset($request['content_text'])) {

                if ($request['content_text'] !== '') {

                    $content = $request['content_text'];

                    $url = 'https://api.openai.com/v1/chat/completions';

                    $headers = [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->openai_api_key
                    ];

                    //\Log::info("lista envíada:", [$content_text_list]);

                    $json = [
                        "model" => "gpt-3.5-turbo",
                        "messages" => $content_text_list
                    ];

                    $body = [
                        'headers' => $headers,
                        'json' => $json
                    ];

                    $petition = HttpClient::post($url, $body);
                    $petition_json = json_decode($petition->getBody()->getContents(), true);

                    $content_text_response = $petition_json['choices'][0]['message']['content'];
                    //$content_text_response = str_replace("\n", "", $content_text_response);


                }
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

            $content_text_response = 'Ocurrió un error con la api de OpenAI';
        }

        return $content_text_response;
    }
}
