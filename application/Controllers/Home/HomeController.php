<?php

namespace Agencia\Close\Controllers\Home;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Home\Home;
use Agencia\Close\Enums\Permissions\ProductsPermissions;

class HomeController extends Controller
{	
  public function index($params)
  {
    $this->setParams($params);
    $this->render('pages/home/home.twig', ['page' => 'home', 'titulo' => 'Página Inicial']);
  }

  public function tempermissao() {
    echo 'tem permissao';
    $this->requirePermission(ProductsPermissions::$listProduct);
    die();
  }

  public function sempermissao() {
    $this->requirePermission(ProductsPermissions::$createProduct);
    echo 'você não pode ver isso';
    die();
  }

  public function check_agendamentos($params) 
  {
    $this->setParams($params);
    $check = new Home();
    $result = $check->checkAgendamentos($this->dataCompany['id']);
    $this->responseJson($result->getResult());

  }

}