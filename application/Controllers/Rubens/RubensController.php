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
        
        // Converte o JSON em objeto PHP
        echo $json_result;
        // $obj = json_decode($json_result);
        
        // echo '<pre>';
        // print_r($obj);
        // echo '</pre>';
    }

    public function disponibilidade()
    {
        // $model = new Rubens;
        // $suites = $model->getSuites()->getResult();
        // $json_result = $suites[0]["json_result"];
        
        // Converte o JSON em objeto PHP
        //echo $json_result;
        // $obj = json_decode($json_result);

        $dados = array("Aguardando informações...");
        
        echo '<pre>';
        print_r($dados);
        echo '</pre>';
    }
}