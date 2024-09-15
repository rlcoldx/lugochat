<?php

namespace Agencia\Close\Controllers\SuitesWidget;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\SuitesWidget\SuitesWidget;

class SuitesWidgetController extends Controller
{

  	public function suites_lista($params)
  	{
		$this->setParams($params);
		
		$suites = $suites = new SuitesWidget();
		$suites = $suites->getSuites($this->params['id_empresa']);

		if ($suites->getResult()) {
			$this->render('pages/widget/suites_lista.twig', ['suites' => $suites->getResult()]);
		}
  	}

	public function suites_detalhes($params)
  	{
		$this->setParams($params);
	
		$suite = new SuitesWidget();
		$result = $suite->getSuite($this->params['id'], $_SESSION['lugo_widget_empresa']);
		$suite = $result->getResult()[0];

		// Obtém o dia da semana abreviado (seg, ter, qua, qui, sex, sáb, dom)
		$diaDaSemana = $this->diaSemana(date('Y-m-d'));

		$precosall = new SuitesWidget();
		$precosall = $precosall->getSuitePrecosAll($this->params['id'], $_SESSION['lugo_widget_empresa'])->getResult();
	
		$precos = new SuitesWidget();
		$precos = $precos->getSuitePrecos($this->params['id'], $_SESSION['lugo_widget_empresa'], $diaDaSemana)->getResult();
	
		$imagens = new SuitesWidget();
		$imagem = $imagens->getSuiteImages($this->params['id'], $_SESSION['lugo_widget_empresa'])->getResult();

		$listaHoras = $this->listarHorasRestantesDoDia(time());

		$this->render('pages/widget/suites_detalhes.twig', ['suite' => $suite, 'precosall' => $precosall, 'precos' => $precos, 'imagens' => $imagem, 'listaHoras' => $listaHoras]);
		
  	}

	public function diaSemana($dataAtual)
	{
		// Obtém o dia da semana em inglês
		$diaDaSemanaEmIngles = date('D', strtotime($dataAtual));

		// Mapeia os nomes em inglês para os nomes em português
		$traducaoDiasDaSemana = array(
			'Mon' => 'seg',
			'Tue' => 'ter',
			'Wed' => 'qua',
			'Thu' => 'qui',
			'Fri' => 'sex',
			'Sat' => 'sab',
			'Sun' => 'dom'
		);
		
		// Obtém o dia da semana em português usando o array de tradução
		return $traducaoDiasDaSemana[$diaDaSemanaEmIngles];
	}

	function listarHorasRestantesDoDia($dataHoraSelect)
	{
		if(!empty($_POST['dataselect'])) {

			$dataAtual = date('Y-m-d');
			if ($dataAtual != $_POST['dataselect']) {
				$horaAtual = strtotime($_POST['dataselect'].' 00:00:00');
				$finalDoDia = strtotime($_POST['dataselect'].' 23:59:59');
				
			}else{
				
				$horaAtual = time();
				$finalDoDia = strtotime($_POST['dataselect'].' 23:59:59');
			}

		}else{

			$horaAtual = $dataHoraSelect;
			$finalDoDia = strtotime(date('Y-m-d 23:59:59'));

		}
	
		// Arredonda a hora atual para a próxima hora cheia
		$proximaHoraCheia = ceil($horaAtual / 3600) * 3600;
	
		// Inicializa um array para armazenar as horas restantes
		$horasRestantes = [];
	
		// Loop para gerar a lista de horas restantes
		while ($proximaHoraCheia <= $finalDoDia) {
			// Adiciona a hora atual arredondada para a hora cheia ao array
			$horasRestantes[] = date('H:00', $proximaHoraCheia);
	
			// Incrementa 1 hora ao timestamp
			$proximaHoraCheia += 3600; // 3600 segundos em 1 hora
		}
	
		// Retorna a lista de horas restantes
		if(!empty($_POST['dataselect'])){
			$return = json_encode($horasRestantes);
			echo $return;
		}else{
			return $horasRestantes;
		}
	}

	function getPeriodos()
	{
		$diaDaSemana = $this->diaSemana($_POST['dataselect']);
		$precos = new SuitesWidget();
		$precos = $precos->getSuitePrecos($_POST['id'], $_POST['id_empresa'], $diaDaSemana)->getResult();
		$return = json_encode($precos);
		echo $return;
	}

	function saveAgendamentos($params)
	{
		$this->setParams($params);

		$save = new SuitesWidget();
		$save = $save->saveAgendamentos($this->params);
		if($save->getResult()){
			$this->responseJson($save->getResult());
		}else{
			echo '0';
		}
	}

}