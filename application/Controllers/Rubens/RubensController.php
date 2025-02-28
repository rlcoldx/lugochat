<?php
namespace Agencia\Close\Controllers\Rubens;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Rubens\Rubens;

class RubensController extends Controller
{
    public function suites()
    {
        $model = new Rubens;
        $suites = $model->getSuites()->getResult();
        $json_result = $suites[0]["json_result"];
        echo $json_result;
    }

    public function disponibilidade($params)
    {
        $this->setParams($params);

        $model = new Rubens;
        $model->updateDisponibilidade($_GET);
        $dados = array("Atualizado");

        print_r($dados);
        
    }
}