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

        $checkMotel = $model->checkMotelRubens($_GET['motel'])->getResult();

        if (!empty($checkMotel)) {
            $resultado = $model->updateDisponibilidade($_GET);
            if ($resultado === true) {
                echo 'ok';
            } else {
                echo $resultado;
            }
        }else{
            echo 'Erro: Motel não encontrado';
        }
    }

    public function qtde_disp()
    {
        $model = new Rubens;
        $disponibilidade = $model->getDisponibilidade($_GET)->getResult();
        $json_result = $disponibilidade[0]["json_result"];
        echo $json_result;
    }
}