<?php

namespace Agencia\Close\Controllers\Equipe;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Equipe\Equipe;
use Agencia\Close\Enums\Permissions\ProductsPermissions;

class EquipeController extends Controller
{	
  public function index($params)
  {
    $this->setParams($params);
    $this->render('pages/equipe/index.twig', ['titulo' => 'Lista de Equipe']);
  }

  public function criar($params)
  {
    $this->setParams($params);
    $this->render('pages/equipe/form.twig', ['titulo' => 'Criar Membro']);
  }

  public function editar($params)
  {
    $this->setParams($params);
    $this->render('pages/equipe/form.twig', ['titulo' => 'Editar Membro']);
  }

}