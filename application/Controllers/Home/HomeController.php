<?php

namespace Agencia\Close\Controllers\Home;

use Agencia\Close\Models\Home\Home;
use Agencia\Close\Models\User\User;
use Agencia\Close\Models\Moteis\Moteis;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Reserva\Reserva;
use Agencia\Close\Models\Saques\SaquesPainel;
use Agencia\Close\Helpers\Pagination\Pagination;

class HomeController extends Controller
{	
  public function index()
  {
    $model = new Home();
    $idMotel = null;
    if (($_SESSION['busca_perfil_tipo'] == 2) || ($_SESSION['busca_perfil_tipo'] == 3)) {
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

    $moteis = new Moteis();
    $moteislista = $moteis->getMoteisList()->getResult();

    // Para usuários admin, processar filtros e paginação
    $reservas = [];
    $pagination = null;
    $filters = [];
    
    if($_SESSION['busca_perfil_tipo'] == 0){
      // Capturar filtros da URL
      $filters = [
        'pagamento_status' => $_GET['pagamento_status'] ?? '',
        'id_motel' => $_GET['id_motel'] ?? '',
        'search' => $_GET['search'] ?? '',
        'order' => $_GET['order'] ?? 'recente'
      ];
      
      // Paginação
      $page = $_GET['page'] ?? 1;
      $limit = 20;
      $offset = ($page - 1) * $limit;
      
      $reservaModel = new Reserva();
      $reservas = $reservaModel->getReservasWithFilters($filters, $limit, $offset)->getResult();
      $totalReservasCount = $reservaModel->countReservasWithFilters($filters);
      
      // Configurar paginação
      $pagination = new Pagination();
      $pagination->setActualPage($page);
      $pagination->setLastPage(ceil($totalReservasCount / $limit));
      $pagination->setUrl(DOMAIN . '/home');
      
      $model = new Home();
      $lucroMes = $model->getLucroMes();
      $lucroTotal = $model->getLucroTotal();
      $carteira = 0;
      $totalSaques = 0;
    } else {
      // Para motéis, manter comportamento anterior
      $reservas = new Reserva();
      $limit = 5;
      $reservas = $reservas->getReservas($limit)->getResult();
      $lucroMes = 0;
      $lucroTotal = 0;
      
      $user = new User();
      $maxSaques = $user->getUserByID($_SESSION['busca_perfil_empresa'])->getResultSingle()['saques'];
      $carteira = $this->carteira();
      $totalSaques = $this->saquesMes()['total_saques'];
      $totalSaques = ($totalSaques - $maxSaques);

      $contratoPercentual = $this->contratoPercentual();
      $totalValorMes['total_vendas'] = $totalValorMes['total_vendas'] * ($contratoPercentual / 100);
      $totalValor['total_vendas'] = $totalValor['total_vendas'] * ($contratoPercentual / 100);
    }

    // Define o template baseado no tipo de usuário
    $template = ($_SESSION['busca_perfil_tipo'] == 0) 
      ? 'pages/home/home-admin.twig' 
      : 'pages/home/home-motel.twig';

    $this->render($template, [
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
      'lucroTotal' => $lucroTotal,
      'pagination' => $pagination ? $pagination->getArray() : null,
      'filters' => $filters
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