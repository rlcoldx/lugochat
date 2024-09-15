<?php

namespace Agencia\Close\Controllers\Agendamento;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Agendamento\Agendamento;

class AgendamentoController extends Controller
{

	function saveAgendamentos($params)
	{
		$this->setParams($params);

		$save = new Agendamento();
		$save = $save->saveAgendamentos($this->params);
		if($save->getResult()){
			echo $save->getResult()[0]['codigo'];
		}else{
			echo '0';
		}
	}

	public function getAgendamentoEspera($params)
  	{
		$this->setParams($params);

		$agendamento = new Agendamento();
		$agendamento = $agendamento->getAgendamentoEspera($this->params['codigo']);

		$this->render('pages/widget/user/espera.twig', ['agendamento' => $agendamento]);
		
  	}

}