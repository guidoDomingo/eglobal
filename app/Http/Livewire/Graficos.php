<?php

namespace App\Http\Livewire;

use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Livewire\Component;

class Graficos extends Component
{

    public $valores, $redes = "todos";

    protected $listeners = ['actualizaratms' => 'atmsgenerales'];

    public function render()
    {
        return view('livewire.graficos');
    }

    public function atmsgenerales($redes){
        
        $controller = new DashboardController();
        $collection = "atms_general";
        $response = $controller->monitoringCollections($collection, new Request(array('_redes' => $redes)));

        $data = json_decode($response->getContent(), false);

        $valor = $data->result->data;
        $this->dispatchBrowserEvent('cms', ["data" => $valor, "redes" => $this->redes]);

    }

    public function buscaratms(){
       $this->atmsgenerales($this->redes);
    }

    
}
