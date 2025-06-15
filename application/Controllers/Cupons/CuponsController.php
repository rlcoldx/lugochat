<?php

namespace Agencia\Close\Controllers\Cupons;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Cupons\CuponsModel;

class CuponsController extends Controller
{	
    private function getMotelId()
    {
        return (isset($_SESSION['busca_perfil_tipo']) && $_SESSION['busca_perfil_tipo'] != 0) ? $_SESSION['busca_perfil_empresa'] : null;
    }

    public function index($params)
    {
        $this->setParams($params);
        $cupons = new CuponsModel();
        $cupons = $cupons->getCupons($this->getMotelId())->getResult();
        $this->render('pages/cupons/index.twig', ['titulo' => 'Lista de Cupons', 'cupons' => $cupons]);
    }

    public function criar()
    {
        $this->render('components/cupons/form.twig');
    }

    public function editar($params)
    {
        $this->setParams($params);
        $cupom = new CuponsModel();
        $cupom = $cupom->getCupomID($params['id'], $this->getMotelId())->getResult()[0];
        $this->render('components/cupons/form.twig', ['cupom' => $cupom]);
    }

    public function cupomSalvar($params)
    {
        $this->setParams($params);
        $cupom = new CuponsModel();

        if($params['id'] != '-1') {
            $cupom->updateCupom($params, $params['id'], $this->getMotelId());
        } else {
            $cupom->createCupom($params, $this->getMotelId());
        }

        echo '1';
    }
}