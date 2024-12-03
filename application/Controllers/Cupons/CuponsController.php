<?php

namespace Agencia\Close\Controllers\Cupons;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Cupons\CuponsModel;

class CuponsController extends Controller
{	

  public function index($params)
  {
    $this->setParams($params);
    $cupons = new CuponsModel();
    $cupons = $cupons->getCupons()->getResult();
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
    $cupom = $cupom->getCupomID($params['id'])->getResult()[0];
    $this->render('components/cupons/form.twig', ['cupom' => $cupom]);
  }

  public function cupomSalvar($params)
  {
    $this->setParams($params);
    $cupom = new CuponsModel();

    if($params['id'] != '-1') {
      $cupom->updateCupom($params, $params['id']);
    } else {
      $cupom->createCupom($params);
    }

    echo '1';
  }

}