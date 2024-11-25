<?php

namespace Agencia\Close\Controllers\Moteis;
use Agencia\Close\Helpers\Upload;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Controllers\Controller;

class MoteisController extends Controller
{

  public int $id = 0;

  public function index($params)
  {
    $this->setParams($params);

    if ($_SESSION['busca_perfil_tipo'] != 1 && $_SESSION['busca_perfil_tipo'] != 2) {
      header("Location: ".DOMAIN);
      exit();
    }

    $moteis = new Moteis();
    $moteis = $moteis->getMoteis()->getResult();

    $this->render('pages/moteis/index.twig', ['menu' => 'moteis', 'moteis' => $moteis]);
  }

  public function criar($params)
  {
    $this->setParams($params);

    $this->render('pages/moteis/form.twig', ['menu' => 'moteis']);
  }

  public function editar($params)
  {
    $this->setParams($params);

    if ($_SESSION['busca_perfil_tipo'] != 1 && $_SESSION['busca_perfil_tipo'] != 5) {
      header("Location: ".DOMAIN);
      exit();
    }

    $motel = new Moteis();
    $motel = $motel->getMotel($params['id']);
    $motel = $motel->getResult()[0];

    $this->render('pages/moteis/form.twig', ['menu' => 'moteis', 'motel' => $motel]);

  }

  //CRIAR O PRODUTO EM RASCUNHO
  public function criarSalvar($params)
  {
    $this->setParams($params);
    $moteis = new Moteis();

    $result = $moteis->createDraft($params);
    $save_motel = $result->getResult();

    if($save_motel){
      if(isset($_FILES['logo'])) {
        $params['id'] = $save_motel;
        $this->editarSalvar($params);
      }
    }else{
      echo 'error';
    }

    echo $save_motel;
  }

  //SALVA O EDITAR DO PRODUTO
  public function editarSalvar($params)
  {
    $this->setParams($params);
    $moteis = new Moteis();

    if(isset($_FILES['logo'])) {
      $upload = new Upload;
      $upload->Image($_FILES['logo'], microtime(), null, 'moteis');
    }
    if(isset($upload) && $upload->getResult()) {
      $params['logo'] = DOMAIN.'/uploads/'.$upload->getResult();
    }

    $result = $moteis->saveEdit($params)->getResult();
  
    if($result){
      echo 'success';
    }else{
      echo 'error';
    }
  }

  //EXCLUI O PRODUTO
  public function excluir_motel($params){
    $this->setParams($params);
    $excluir = new Moteis();
    $excluir->excluirMotel($params['id_motel'], $params['status']);
  }

}