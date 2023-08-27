<?php 

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\Validator;
use App\Models\BotTelegram;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TelegramController extends Controller{

    protected $user;

    public function __construct()
    {
        $this->user = Sentinel::getUser();
    }

    public function index(Request $request)
    {

        $users = \DB::table('users as u')
        ->select('u.id', 'u.username')
        ->whereNull('u.deleted_at')
        ->get();

        $userId = $this->user->id;

        //$bots = BotTelegram::where('user_id', $userId)->get();
        $bots = BotTelegram::get();

        return view('telegram.telegram',compact('users','bots'));
    }

    public function guardar_bot_telegram(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'name' => 'required',
            'token' => 'required'
        ]);
    
        if ($validator->fails()) {
            return ["error"];
        }
     
        BotTelegram::create($request->all());

        /* Registrar el chat id */

        $this->registrarChatId($request->input('token'),$request->input('user_id'));

        $this->registrarGrupo($request->input('token'),$request->input('user_id'));

        return redirect()->route('generar_token_telegram')->with('success', 'Los datos se han guardado correctamente.');

    }

    public function show($id)
    {
        $bot = BotTelegram::findOrFail($id);
        return response()->json($bot);
    }

    public function update(Request $request)
    {
        // Validar los datos del formulario
        
        $this->validate($request, [
            'name' => 'required',
            'token' => 'required',
            'id' => 'required',
        ]);

        // Buscar el bot y actualizar sus campos
        $bot = BotTelegram::findOrFail($request->input('id'));
        $bot->name = $request->input('name');
        $bot->token = $request->input('token');
        $bot->save();

        return redirect()->route('generar_token_telegram')->with('success', 'Los datos se han actualizado correctamente.');
    }

    public function destroy($id)
    {
        try {
            $bot = BotTelegram::findOrFail($id);
            $bot->delete();
            
            return response('', 204);

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Bot no encontrado'], 404);
        } catch (\Exception $e) {
            // Aquí puedes registrar el error o devolver un mensaje más específico
            return response()->json(['message' => 'Error al eliminar el bot'], 500);
        }
    }

    public function registrarChatId($token,$userId)
    {
        try{

            $client = new Client(['base_uri' => 'https://api.telegram.org']);

            $response = $client->request('GET', "/bot$token/getUpdates");

            $json = json_decode($response->getBody()->getContents(), true);
 
            $usuario = [
                "token" => $token,
                "chat_id" => $json['result'][0]['message']['chat']['id'],
                "first_name" => $json['result'][0]['message']['chat']['first_name']
            ];

            \DB::table('bot_telegram')
            ->where('user_id', '=', $userId)
            ->update([
                'chat_id' => $usuario['chat_id']
            ]);

            return true;

        }catch(Exception $e){
            return false;
        }   
        
    }

    public function registrarGrupo($token,$userId)
    {
        try{

            $client = new Client(['base_uri' => 'https://api.telegram.org/bot' . $token . '/']);

            $response = $client->get('getUpdates');

            $data = json_decode($response->getBody(), true);
            //return $data['result'];
            $chat_id = null;

            foreach ($data['result'] as $update) {
                if (isset($update['message']['chat']['id']) && $update['message']['chat']['type'] === 'supergroup' && $update['message']['chat']['title'] === 'Eglobal_api') {
                    $chat_id = $update['message']['chat']['id'];
                    break;
                }
            }

            if ($chat_id != null) {
                // El chat no se encontró
                \DB::table('bot_telegram')
                ->where('user_id', '=', $userId)
                ->update([
                    'group_chat_id' => $chat_id 
                ]);

                return true;
            }

            return false;

            
        }catch(Exception $e){
            return false;
        }
    }


}