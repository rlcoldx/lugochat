<?php

namespace Agencia\Close\Controllers\Config;

use Agencia\Close\Helpers\Upload;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Models\Moteis\Categorias;

class ConfigController extends Controller
{

  private Categorias $categories;
  public int $id = 0;

  public function index($params)
  {
    $this->setParams($params);

    $motel = new Moteis();
    $motel = $motel->getMotel($this->dataCompany['id']);
    $motel = $motel->getResultSingle();

    $categorias_lista = $this->getCategoryList();
  
    $this->render('pages/config/index.twig', ['page' => 'configuracoes', 'titulo' => 'Configurações', 'motel' => $motel, 'categorias' => $categorias_lista]);

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