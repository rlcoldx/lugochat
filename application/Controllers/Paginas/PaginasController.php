<?php

namespace Agencia\Close\Controllers\Paginas;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Paginas\Paginas;

class PaginasController extends Controller
{
  public int $id = 0;

  public function index($params)
  {
    if (!isset($_SESSION['busca_perfil_tipo']) || (int) $_SESSION['busca_perfil_tipo'] !== 0) {
      $this->router->redirect('/');
      return;
    }

    $this->setParams($params);
    $paginas = new Paginas();
    $paginas = $paginas->getPaginas()->getResult();

    $this->render('pages/paginas/index.twig', ['menu' => 'paginas', 'paginas' => $paginas]);
  }

  public function criar($params)
  {
    $this->setParams($params);
    $this->render('pages/paginas/form.twig', ['menu' => 'paginas']);
  }

  public function editar($params)
  {
    $this->setParams($params);

    $pagina = new Paginas();
    $pagina = $pagina->getPagina($this->params['id']);
    $pagina = $pagina->getResult()[0];

    $this->render('pages/paginas/form.twig', ['menu' => 'paginas', 'pagina' => $pagina]);

  }

  //CRIAR O PAGINAS EM RASCUNHO
  public function save_draft($params)
  {
    $this->setParams($params);
    $paginas = new Paginas();
    $result = $paginas->createDraft($this->params);
    $pagina_draft = $result->getResult();

    header("Content-Type: application/json");
    echo json_encode($pagina_draft);
  }

  //SALVA O EDITAR DO PAGINAS
  public function save_edit($params)
  {
    $this->setParams($params);
    $paginas = new Paginas();
    $result = $paginas->saveEdit($this->params)->getResult();
  
    if($result){
      echo 'success';
    }else{
      echo 'error';
    }

  }

  //EXCLUI O PRODUTO
  public function excluir_pagina($params){
    $this->setParams($params);
    $excluir = new Paginas();
    $excluir->excluirPagina($this->params['id_pagina']);
  }

  public function banners()
  {
    $imagens = new Paginas();
    $imagens = $imagens->getBanners()->getResult();
    $this->render('pages/paginas/banners.twig', ['menu' => 'paginas', 'imagens' => $imagens]);
  }

}