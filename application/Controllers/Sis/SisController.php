<?php
namespace Agencia\Close\Controllers\Sis;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Sis\Sis;
use Agencia\Close\Services\Sis\CategoriesSis;

class SisController extends Controller
{
    public function disponibilidade($params)
    {
        $this->setParams($params);

        $moteis = new Sis;
        $lista_moteis = $moteis->getMotelSis()->getResult();
        
        if($lista_moteis) {
            foreach ($lista_moteis as $motel){                
                $sis_categories = [];
                if($motel['token'] != null){
                    $sis_categories = new CategoriesSis();
                    $sis_categories = $sis_categories->listCategories($motel['token']);
                    $sis_categories = $sis_categories["result"];
                }

                foreach ($sis_categories as $suite){
                    $moteis->updateDisponibilidade($motel['id'], $suite);
                }
            }
        }        
    }

    public function disponibilidadeMotel($params)
    {
        $this->setParams($params);

        $moteis = new Sis;
        $motel = $moteis->getMotelSisSingle($params['motel'])->getResultSingle();
        
        if($motel) {               
            $sis_categories = [];
            if($motel['token'] != null){
                $sis_categories = new CategoriesSis();
                $sis_categories = $sis_categories->listCategories($motel['token']);
                $sis_categories = $sis_categories["result"];
            }

            foreach ($sis_categories as $suite){
                $moteis->updateDisponibilidade($motel['id'], $suite);
            }
        }        
    }

}