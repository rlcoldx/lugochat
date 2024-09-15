<?php

namespace Agencia\Close\Controllers\Cargos;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Cargos\Cargos;
use Agencia\Close\Enums\Permissions\ProductsPermissions;

class CargosController extends Controller
{	
  public function index($params)
  {
    $this->setParams($params);
    $this->render('pages/equipe/cargos.twig', ['titulo' => 'Lista de Cargos']);
  }

  public function editar($params)
  {
    $this->setParams($params);
    $this->render('pages/equipe/cargos.twig', ['titulo' => 'Editar Cargo']);
  }

}