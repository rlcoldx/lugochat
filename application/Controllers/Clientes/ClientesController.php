<?php

namespace Agencia\Close\Controllers\Clientes;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Clientes\Clientes;

class ClientesController extends Controller
{	

  public function index($params)
  {
    $clientes = new Clientes();
    $clientes = $clientes->getClientes()->getResult();

    $this->setParams($params);
    $this->render('pages/clientes/index.twig', ['titulo' => 'Lista de Clientes', 'clientes' => $clientes]);
  }

}