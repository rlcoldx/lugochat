<?php

namespace Agencia\Close\Controllers\Home;

use Agencia\Close\Models\Home\Home;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Reserva\Reserva;

class HomeController extends Controller
{	
  public function index()
  {

    $model = new Home();
    $totalReservas = $model->getTotalReservas();
    if ($totalReservas->getResult()){
      $totalReservas = $totalReservas->getResult()[0];
    }

    $totalValorMes = $model->getTotalValorMes()->getResult()[0];
    $totalValor = $model->getTotalValor()->getResult()[0];

    $reservasDias = $model->getRegistrosPorDiaDaSemana();

    $suidesReservadas = $model->getSuidesReservadas()->getResult();

    $reservas = new Reserva();
    $limit = 5;
		$reservas = $reservas->getReservas($limit)->getResult();

    $moteis = new Moteis();
    $moteislista = $moteis->getMoteisList()->getResult();

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
    ]);
  }

  public function changeMotel($params)
  {
    $this->setParams($params);
    $_SESSION['busca_perfil_empresa'] = $_POST['motel'];
    $_SESSION['busca_perfil_tipo'] = 2;
  }

}