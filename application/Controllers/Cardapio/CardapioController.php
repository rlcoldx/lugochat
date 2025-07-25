<?php

namespace Agencia\Close\Controllers\Cardapio;

use Agencia\Close\Models\Suites\Suites;
use Agencia\Close\Controllers\Controller;

class CardapioController extends Controller
{
  public function index($params)
  {
    $this->setParams($params);
    $imagens = new Suites();
    $imagem = $imagens->getCardadio($this->dataCompany['id'])->getResult();

    $this->render('pages/cardapio/index.twig', ['titulo' => 'Cardápio', 'imagens' => $imagem]);
  }

}