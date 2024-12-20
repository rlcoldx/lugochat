<?php

namespace Agencia\Close\Controllers\Saques;

use Agencia\Close\Models\User\User;
use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Saques\SaquesPainel;

class SaquesController extends Controller
{

    public function index($params)
    {
        $this->setParams($params);

        $contas_bancarias = $this->contas_bancarias();
        $carteira = $this->carteira();
        $saques = $this->saques();
        $totalSaques = $this->saquesMes()[0]['total_saques'];

        $this->render('pages/saques/saques.twig', ['menu' => 'saques', 'contas' => $contas_bancarias, 'carteira' => $carteira, 'saques' => $saques, 'totalSaques' => $totalSaques]);
    }

    public function SaqueModal()
    {
        $carteira = $this->carteira();
        $contas_bancarias = $this->contas_bancarias();
        $this->render('components/saques/criar.twig', ['contas' => $contas_bancarias, 'carteira' => $carteira]);
    }

    public function SaveSaque($params)
    {
        $this->setParams($params);
        $conta = new SaquesPainel();

        $CarteiraCheck = $this->CarteiraCheckReturn($params['valor']);

        if($CarteiraCheck == '0') {
            $conta->createSaque($params, $_SESSION['busca_perfil_empresa']);
            echo "0";
        } else {
            echo '1';
        }
    }

    public function ContaCriar()
    {
        $this->render('components/conta/criar.twig');
    }

    public function ContaEditar($params)
    {
        $this->setParams($params);
        $conta = new SaquesPainel();
        $conta = $conta->getContaID($_SESSION['busca_perfil_empresa'], $params['id'])->getResult()[0];
        $this->render('components/conta/editar.twig', ['conta' => $conta]);
    }

    public function ContaSalvar($params)
    {
        $this->setParams($params);
        $conta = new SaquesPainel();
        if($params['id'] != '-1') {
            $conta->updateConta($params, $_SESSION['busca_perfil_empresa'], $params['id']);
        } else {
            $conta->createConta($params, $_SESSION['busca_perfil_empresa']);
        }
        echo '1';
    }

    private function contas_bancarias()
    {
        $result = new SaquesPainel();
        return $result->getContas_Bancarias($_SESSION['busca_perfil_empresa'])->getResult();
    }

    //PEGAR O VALOR EM CAIXA
    private function carteira(){

        $model = new User();
        $contrato = $model->getUserByID($_SESSION['busca_perfil_empresa'])->getResult()[0]['contrato'];
        $contrato = (100 - $contrato);

        $vendas_agendamentos = new SaquesPainel();
        $vendas_agendamentos = $vendas_agendamentos->getTotalReservas($_SESSION['busca_perfil_empresa']);
        if ($vendas_agendamentos->getResult()) {
            $vendas_agendamentos_total = $vendas_agendamentos->getResult()[0]['total'];
        }else{
            $vendas_agendamentos_total = 0.00;
        }

        $saques_realizados = new SaquesPainel();
        $saques_realizados = $saques_realizados->getTotalSaques($_SESSION['busca_perfil_empresa']);
        if ($saques_realizados->getResult()) {
            $saques_realizados_total = $saques_realizados->getResult()[0]['total'];
        }else{
            $saques_realizados_total = 0.00;
        }

        //$arrecadado = $total_valor_vendas * (VALOR_GANHO / 100);
        $arrecadado = $vendas_agendamentos_total * ($contrato / 100);
        $valor_base = $vendas_agendamentos_total - $arrecadado;

        $carteira = ($valor_base - $saques_realizados_total);

        return $carteira;

    }

    public function CarteiraCheck(){

        $model = new User();
        $contrato = $model->getUserByID($_SESSION['busca_perfil_empresa'])->getResult()[0]['contrato'];
        $contrato = (100 - $contrato);

        $vendas_agendamentos = new SaquesPainel();
        $vendas_agendamentos = $vendas_agendamentos->getTotalReservas($_SESSION['busca_perfil_empresa']);
        if ($vendas_agendamentos->getResult()) {
            $vendas_agendamentos_total = $vendas_agendamentos->getResult()[0]['total'];
        }else{
            $vendas_agendamentos_total = 0.00;
        }

        $saques_realizados = new SaquesPainel();
        $saques_realizados = $saques_realizados->getTotalSaques($_SESSION['busca_perfil_empresa']);
        if ($saques_realizados->getResult()) {
            $saques_realizados_total = $saques_realizados->getResult()[0]['total'];
        }else{
            $saques_realizados_total = 0.00;
        }

        //$arrecadado = $total_valor_vendas * (VALOR_GANHO / 100);
        $arrecadado = $vendas_agendamentos_total * ($contrato / 100);
        $valor_base = $vendas_agendamentos_total - $arrecadado;

        $carteira = ($valor_base - $saques_realizados_total);
        $carteira = round($carteira, 2);
        $valor_solicitado = str_replace(['.', ','], ['', '.'], $_POST['valor']);

        if($valor_solicitado > $carteira){
            echo '1';
        }else{
            echo '0';
        }
        
    }

    public function CarteiraCheckReturn($valor_enviado){

        $model = new User();
        $contrato = $model->getUserByID($_SESSION['busca_perfil_empresa'])->getResult()[0]['contrato'];
        $contrato = (100 - $contrato);

        $vendas_agendamentos = new SaquesPainel();
        $vendas_agendamentos = $vendas_agendamentos->getTotalReservas($_SESSION['busca_perfil_empresa']);
        if ($vendas_agendamentos->getResult()) {
            $vendas_agendamentos_total = $vendas_agendamentos->getResult()[0]['total'];
        }else{
            $vendas_agendamentos_total = 0.00;
        }

        $saques_realizados = new SaquesPainel();
        $saques_realizados = $saques_realizados->getTotalSaques($_SESSION['busca_perfil_empresa']);
        if ($saques_realizados->getResult()) {
            $saques_realizados_total = $saques_realizados->getResult()[0]['total'];
        }else{
            $saques_realizados_total = 0.00;
        }

        $arrecadado = $vendas_agendamentos_total * ($contrato / 100);
        $valor_base = $vendas_agendamentos_total - $arrecadado;

        $carteira = ($valor_base - $saques_realizados_total);
        $carteira = round($carteira, 2);
        $valor_solicitado = str_replace(['.', ','], ['', '.'], $valor_enviado);

        if($valor_solicitado > $carteira){
            return '1';
        }else{
            return '0';
        }
        
    }

    private function saquesMes()
    {
        $result = new SaquesPainel();
        return $result->getSaquesMes($_SESSION['busca_perfil_empresa'])->getResult();
    }

    private function saques()
    {
        $result = new SaquesPainel();
        return $result->getSaques($_SESSION['busca_perfil_empresa'])->getResult();
    }


}