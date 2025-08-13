<?php
namespace Agencia\Close\Controllers\Reserva;

use Agencia\Close\Models\Home\Home;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Reserva\Reserva;

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
		$save = new Reserva();
		$save = $save->statusReservaSave($params);
		if($save->getResult()) {
			echo '1';
		}
		
  	}

}