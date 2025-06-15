<?php

namespace Agencia\Close\Controllers\Banners;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Banners\Banners;

class BannersController extends Controller
{

  public function index()
  {
    $this->render('pages/banners/index.twig', ['menu' => 'banners']);
  }

  public function link($params)
  {
    $this->setParams($params);

    $link = new Banners();
    $link = $link->getLink($params['id'])->getResultSingle();

    $this->render('pages/banners/link.twig', ['menu' => 'banners', 'id' => $params['id'], 'nome' => $params['nome'], 'link' => $link['link']]);
  }

  public function linkSave($params)
  {
    $this->setParams($params);
    $save = new Banners();
    $save = $save->saveLink($params)->getResult();
  }

  public function banners($params)
  {
    $this->setParams($params);
    $size = $this->tamanhos($params['id']);
    $sizeBloco = $this->tamanhosBloco($params['id']);

    $imagens = new Banners();
    $imagens = $imagens->getImagens($params['id'])->getResult();

    $this->render('pages/banners/banners.twig', ['menu' => 'banners', 'size' => $size, 'sizebloco' => $sizeBloco, 'id' => $params['id'], 'imagens' => $imagens]);
  }

  public function tamanhos($size)
  {
    switch ($size) {
      case '1': return '1000x350'; break;
    }
  }

  public function tamanhosBloco($size)
  {
    switch ($size) {
      case '1': return 'width: 300px; height:100px;'; break;
      default: return '1000x350'; break;
    }
  }

}