<?php
namespace Agencia\Close\Controllers\Reserva;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Reserva\Reserva;
use Agencia\Close\Services\Login\LoginSession;
use Agencia\Close\Services\Notificacao\ReservaPushNotificationService;

class ReservaController extends Controller
{

	public function check_reservas($params)
	{
	  $this->setParams($params);
	  if($_SESSION['busca_perfil_tipo'] != '0'){
		$check = new Reserva();
		$result = $check->checkReservas();
		echo count($result->getResult());
	  }else{
		echo 0;
	  }

	}

	public function check_reservas_expiradas($params)
	{
		$this->setParams($params);
		$check = new Reserva();
		$check->checkReservasExpiradas();
	}

	/**
	 * Verifica reservas pagas sem push enviado (notificao = no) e dispara notificação.
	 */
	public function check_reservas_pagamento($params)
	{
		$this->setParams($params);

		$loginSession = new LoginSession();
		if (!$loginSession->userIsLogged()) {
			echo 0;
			return;
		}

		$service = new ReservaPushNotificationService();
		echo (int) $service->processarReservasPagasPendentes();
	}

	public function get_reservas($params)
  	{
		$this->setParams($params);

		$reservas = new Reserva();
		$reservas = $reservas->getReservas()->getResult();

		$this->render('pages/reservas/index.twig', ['reservas' => $reservas]);
		
  	}

	public function get_reserva_id($params)
  	{
		$this->setParams($params);

		$reserva = new Reserva();
		$reserva = $reserva->statusReserva($params['id']);
		if($reserva->getResult()) {
			$reserva = $reserva->getResult()[0];
		}

		$this->render('pages/reservas/view.twig', ['reserva' => $reserva]);
		
  	}

	public function status_reserva($params)
  	{
		$this->setParams($params);

		$reserva = new Reserva();
		$reserva = $reserva->statusReserva($params['id']);
		if($reserva->getResult()) {
			$reserva = $reserva->getResult()[0];
		}

		$this->render('pages/reservas/form.twig', ['reserva' => $reserva]);
		
  	}

	  public function status_reserva_save($params)
  	{
		$this->setParams($params);
		$model = new Reserva();

		if ($model->pagamentoAprovado($params['id'])) {
			echo 'bloqueado';
			return;
		}

		$save = $model->statusReservaSave($params);
		if ($save->getResult()) {
			echo '1';
		}
		
  	}

}