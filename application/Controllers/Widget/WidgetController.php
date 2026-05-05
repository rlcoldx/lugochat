<?php

namespace Agencia\Close\Controllers\Widget;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Widget\Widget;

class WidgetController extends Controller
{
  public function index($params)
  {

    $this->setParams($params);

    //VERIFICA A EMPRESA PARA INICIAR O CHAT
    $empresa = new Widget();
    $empresa = $empresa->getEmpresaByCode($_GET['cc'])->getResultSingle();
    $_SESSION['lugo_widget_empresa'] = $empresa['id'];
    
    $this->render('pages/widget/chat.twig', ['titulo' => 'Widget', 'empresa' => $empresa]);

  }

  public function historico($params)
  {
    $this->setParams($params);

    $motelId = $this->widgetMotelId();
    if ($motelId === null) {
      $this->render('pages/widget/historico.twig', ['historicos' => [], 'inical' => 'N']);
      return;
    }

    $historico = new Widget();
    $historico = $historico->checkHistorico($motelId, $params['userID'])->getResult();
    $inical = 'N';
    
    if($historico == null) {
      $historico = new Widget();
      $historico = $historico->loadPrimeira($motelId)->getResult();
      $inical = 'S';
    }

    $this->render('pages/widget/historico.twig', ['historicos' => $historico, 'inical' => $inical]);
  }

  public function buildTree($historico, $parentId = 0): array
  {
    $branch = array();
    foreach ($historico as $item) {
      if ($item['parent'] == $parentId) {
        $children = $this->buildTree($historico, $item['id']);
        if ($children) {  $item['children'] = $children; }
        $branch[] = $item;
      }
    }
    return $branch;
  }

  public function saveHistorico($params)
  {
    $this->setParams($params);

    $motelId = $this->widgetMotelId();
    if ($motelId === null) {
      return;
    }

    // PERGUNTA
    if($params['inicial'] == 'S'){
      $pergunta = new Widget();
      $pergunta = $pergunta->getPergunta($motelId, $params['id_pergunta'])->getResult();
      
      foreach ($pergunta as $salvar){
        $save = new Widget();
        $save = $save->saveHistorico($salvar, $params['userID']);
      }
    }

    // RESPOSTA
    $resposta = new Widget();
    $resposta = $resposta->getResposta($motelId, $params['id_resposta'])->getResult()[0];

    $save = new Widget();
    $save = $save->saveHistorico($resposta, $params['userID'], 'resposta');

    // PROXIMA PERGUNTA
    $proximaPergunta = new Widget();
    $proximaPergunta = $proximaPergunta->getProximaPergunta($motelId, $params['id_resposta'])->getResult()[0];

    $save = new Widget();
    $save = $save->saveHistorico($proximaPergunta, $params['userID']);
  
    // PROXIMA RESPOSTA
    $proximaResposta = new Widget();
    $proximaResposta = $proximaResposta->getProximaResposta($motelId, $proximaPergunta['id'])->getResult();

    foreach ($proximaResposta as $salvar){
      $save = new Widget();
      $save = $save->saveHistorico($salvar, $params['userID']);
    }


   
    //$save = new Widget();
    //$save = $save->saveHistorico($this->params, $_SESSION['lugo_widget_empresa']);



  }

}