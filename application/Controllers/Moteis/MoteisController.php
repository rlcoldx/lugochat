<?php

namespace Agencia\Close\Controllers\Moteis;
use Agencia\Close\Helpers\Upload;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Moteis\Categorias;

class MoteisController extends Controller
{

  private Categorias $categories;
  public int $id = 0;

  public function index($params)
  {
    $this->setParams($params);

    $moteis = new Moteis();
    $moteis = $moteis->getMoteis()->getResult();

    $this->render('pages/moteis/index.twig', ['menu' => 'moteis', 'moteis' => $moteis]);
  }

  public function criar($params)
  {
    $this->setParams($params);

    $categorias_lista = $this->getCategoryList();

    $this->render('pages/moteis/form.twig', ['menu' => 'moteis', 'categorias' => $categorias_lista]);
  }

  public function editar($params)
  {
    $this->setParams($params);

    if ($_SESSION['busca_perfil_tipo'] != 1 && $_SESSION['busca_perfil_tipo'] != 2) {
      header("Location: ".DOMAIN);
      exit();
    }

    $motel = new Moteis();
    $motel = $motel->getMotel($params['id']);
    $motel = $motel->getResult()[0];

    $categorias_lista = $this->getCategoryList();

    $this->render('pages/moteis/form.twig', ['menu' => 'moteis', 'motel' => $motel, 'categorias' => $categorias_lista]);

  }

  //CRIAR O MOTEL EM RASCUNHO
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

  //SALVA O EDITAR DO MOTEL
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

  public function getCategoryList(): array
  {
    $this->categories = new Categorias();
    $result = $this->categories->getCategory();
    if ($result->getResult()) {
      return $this->buildTree($result->getResult());
    } else {
      return [];
    }
  }

  public function buildTree($categories, $parentId = 0): array
  {
    $branch = array();
    foreach ($categories as $item) {
      if ($item['parent'] == $parentId) {
        $children = $this->buildTree($categories, $item['id']);
        if ($children) {
            $item['children'] = $children;
        }
        $branch[] = $item;
      }
    }
    return $branch;
  }

}