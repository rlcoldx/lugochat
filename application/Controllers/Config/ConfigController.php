<?php

namespace Agencia\Close\Controllers\Config;

use Agencia\Close\Models\Config\Config;
use Agencia\Close\Controllers\Controller;

class ConfigController extends Controller
{	

  public function index()
  {

    $model = new Config();
    $config = $model->getConfig()->getResult()[0];
    $this->render('pages/config/index.twig', ['page' => 'configuracoes', 'titulo' => 'Configurações', 'config' => $config]);

  }

  public function save($params)
  {
    $this->setParams($params);

    $update = new Config();
    $update = $update->getConfigSave($this->params);
    echo 'success';

  }

}