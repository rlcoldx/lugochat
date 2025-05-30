<?php

namespace Agencia\Close\Controllers\Suites;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Suites\Suites;
use Agencia\Close\Services\Sis\CategoriesSis;

class SuitesController extends Controller
{
  public function index($params)
  {
    $this->setParams($params);

    $suites = new Suites();
    $suites = $suites->getSuites($this->dataCompany['id'])->getResult();

    $this->render('pages/suites/index.twig', ['titulo' => 'Minhas Suítes', 'suites' => $suites]);
  }

  public function criar($params)
  {
    $this->setParams($params);
    $this->render('pages/suites/form.twig', ['titulo' => 'Criar Suíte']);
  }

  public function editar($params)
  {
    $this->setParams($params);

    $sis_categories = [];
    if($this->dataCompany['token'] != null){
      $sis_categories = new CategoriesSis();
      $sis_categories = $sis_categories->listCategories($this->dataCompany['token']);
      $sis_categories = $sis_categories["result"];
    }

    $suite = new Suites();
    $result = $suite->getSuite($this->params['id'], $this->dataCompany['id']);
    $suite = $result->getResult()[0];

    $precos = new Suites();
    $precos = $precos->getSuitePrecos($this->params['id'], $this->dataCompany['id'])->getResult();

    $imagens = new Suites();
    $imagem = $imagens->getSuiteImages($this->params['id'], $this->dataCompany['id'])->getResult();

    $this->render('pages/suites/form.twig', ['titulo' => 'Editar Suíte', 'suite' => $suite, 'precos' => $precos, 'imagens' => $imagem, 'sis_categories' => $sis_categories]);
  }

  //CRIAR O PRODUTO EM RASCUNHO
  public function save_draft($params)
  {
    $this->setParams($params);
    $suites = new Suites();
    $result = $suites->createDraft($this->params, $this->dataCompany['id']);
    $suite_draft = $result->getResult()[0];

    header("Content-Type: application/json");
    echo json_encode($suite_draft);
  }

  //SALVA O EDITAR DA SUITE
  public function save_edit($params)
  {
    $this->setParams($params);
    $suites = new Suites();
    $result = $suites->saveEdit($this->params, $this->dataCompany['id'])->getResult();
    
    if($this->params['price_chance'] == 'sim') {
      $precos = new Suites();
      $precos->saveEditPrecos($this->params['preco'], $this->params['id'], $this->dataCompany['id']);
    }

    if($result){
      echo 'success';
    }else{
      echo 'error';
    }

  }

  //EXCLUI A SUITE
  public function excluir_suite($params)
  {
    $this->setParams($params);
    $excluir = new Suites();
    $excluir->excluirSuite($this->params['id_suite'], $this->dataCompany['id']);
  }

  public function duplicar($params)
  {
    $this->setParams($params);
    $duplicar = new Suites();
    $duplicar->duplicarSuite($_GET['id'], $this->dataCompany['id']);
  }

}