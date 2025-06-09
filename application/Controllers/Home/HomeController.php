<?php

namespace Agencia\Close\Controllers\Home;

use Agencia\Close\Models\Home\Home;
use Agencia\Close\Models\User\User;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Reserva\Reserva;
use Agencia\Close\Models\Saques\SaquesPainel;

class HomeController extends Controller
{	
  public function index()
  {
    $model = new Home();
    $idMotel = null;
    if ($_SESSION['busca_perfil_tipo'] == 2) {
      $idMotel = $_SESSION['busca_perfil_empresa'] ?? null;
    }

    $totalReservas = $model->getTotalReservas($idMotel);
    if ($totalReservas->getResult()){
      $totalReservas = $totalReservas->getResultSingle();
    }

    $totalValorMes = $model->getTotalValorMes($idMotel)->getResultSingle();
    $totalValor = $model->getTotalValor($idMotel)->getResultSingle();

    $reservasDias = $model->getRegistrosPorDiaDaSemana($idMotel);

    $suidesReservadas = $model->getSuidesReservadas($idMotel)->getResult();

    $reservas = new Reserva();
    $limit = 5;
    $reservas = $reservas->getReservas($limit)->getResult();

    $moteis = new Moteis();
    $moteislista = $moteis->getMoteisList()->getResult();

    if($_SESSION['busca_perfil_tipo'] == 2){

      $user = new User();
      $maxSaques = $user->getUserByID($_SESSION['busca_perfil_empresa'])->getResultSingle()['saques'];
      $carteira = $this->carteira();
      $totalSaques = $this->saquesMes()['total_saques'];
      $totalSaques = ($totalSaques - $maxSaques);

      $contratoPercentual = $this->contratoPercentual();
      $totalValorMes['total_vendas'] = $totalValorMes['total_vendas'] * ($contratoPercentual / 100);
      $totalValor['total_vendas'] = $totalValor['total_vendas'] * ($contratoPercentual / 100);

    }else{
      $carteira = 0;
      $totalSaques = 0;
    }

    if($_SESSION['busca_perfil_tipo'] == 0){
      $model = new Home();
      $lucroMes = $model->getLucroMes();
      $lucroTotal = $model->getLucroTotal();
    }else{
      $lucroMes = 0;
      $lucroTotal = 0;
    }

    $this->render('pages/home/home.twig', [
      'page' => 'home', 
      'titulo' => 'Página Inicial', 
      'totalReservas' => $totalReservas, 
      'totalValorMes' => $totalValorMes, 
      'totalValor' => $totalValor, 
      'reservasDias' => $reservasDias,
      'suidesReservadas' => $suidesReservadas,
      'reservas' => $reservas,
      'moteislista' => $moteislista,
      'carteira' => $carteira,
      'totalSaques' => $totalSaques,
      'lucroMes' => $lucroMes,
      'lucroTotal' => $lucroTotal
    ]);
  }

  public function changeMotel($params)
  {
    $this->setParams($params);
    $model = new User;
    $empresa = $model->getUserByID($_POST['motel'])->getResultSingle();
    $_SESSION['busca_perfil_empresa'] = $empresa['id'];
    $_SESSION['busca_perfil_nome'] = $empresa['nome'];
    $_SESSION['busca_perfil_logo'] = $empresa['logo'];
    $_SESSION['busca_perfil_tipo'] =  $empresa['tipo'];
  }


  private function contratoPercentual()
  {
    $model = new User();
    $contrato = $model->getUserByID($_SESSION['busca_perfil_empresa'])->getResultSingle()['contrato'];
    $percentual_motel = 100 - $contrato;
    return $percentual_motel;
  }


  //PEGAR O VALOR EM CAIXA
  private function carteira()
  {

    $model = new User();
    $contrato = $model->getUserByID($_SESSION['busca_perfil_empresa'])->getResultSingle()['contrato'];
    $percentual_motel = 100 - $contrato;

    $vendas_agendamentos = new SaquesPainel();
    $vendas_agendamentos = $vendas_agendamentos->getTotalReservas($_SESSION['busca_perfil_empresa']);
    if ($vendas_agendamentos->getResult()) {
      $vendas_agendamentos_total = $vendas_agendamentos->getResultSingle()['total'];
    } else {
      $vendas_agendamentos_total = 0.00;
    }

    $saques_realizados = new SaquesPainel();
    $saques_realizados = $saques_realizados->getTotalSaques($_SESSION['busca_perfil_empresa']);
    if ($saques_realizados->getResult()) {
      $saques_realizados_total = $saques_realizados->getResultSingle()['total'];
    } else {
      $saques_realizados_total = 0.00;
    }

    $carteira = ($vendas_agendamentos_total * ($percentual_motel / 100)) - $saques_realizados_total;

    return $carteira;
  }

  private function saquesMes()
  {
    $result = new SaquesPainel();
    return $result->getSaquesMes($_SESSION['busca_perfil_empresa'])->getResultSingle();
  }

  private function saques()
  {
    $result = new SaquesPainel();
    return $result->getSaques($_SESSION['busca_perfil_empresa'])->getResultSingle();
  }

}