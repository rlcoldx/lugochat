<?php
namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Api\Api;
use Agencia\Close\Models\ApiIntegracao\ApiIntegracao;

class ApiController extends Controller
{
    /**
     * Valida o token e retorna o ID do motel
     * Incrementa contador de acessos
     * @param string|null $token
     * @return int|null
     */
    private function autenticarToken($token = null)
    {
        if (!$token) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token não fornecido. Use o parâmetro motel=SEU_TOKEN'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        $modelIntegracao = new ApiIntegracao();
        $validacao = $modelIntegracao->validarToken($token);

        if (!$validacao) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token inválido'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        if ($validacao['motel_status'] !== 'Ativo') {
            http_response_code(403);
            echo json_encode(['erro' => 'Motel inativo'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        // Incrementa o contador de acessos
        $modelIntegracao->incrementarAcessos($validacao['id']);

        return (int)$validacao['id_motel'];
    }
    public function suites()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $model = new Api;
        $suites = $model->getSuites($id_motel)->getResult();
        $json_result = $suites[0]["json_result"];
        echo $json_result;
    }

    public function disponibilidade($params)
    {
        $this->setParams($params);
        
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $model = new Api;
        
        // Substitui o token pelo id_motel nos parâmetros
        $_GET['motel'] = $id_motel;
        
        $resultado = $model->updateDisponibilidade($_GET);
        if ($resultado === true) {
            echo 'ok';
        } else {
            echo $resultado;
        }
    }

    public function qtde_disp()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        // Substitui o token pelo id_motel nos parâmetros
        $_GET['motel'] = $id_motel;

        $model = new Api;
        $disponibilidade = $model->getDisponibilidade($_GET)->getResult();
        $json_result = $disponibilidade[0]["json_result"];
        echo $json_result;
    }

    public function CriarReservaTeste()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $id_suite = isset($_GET['suite']) ? intval($_GET['suite']) : null;
        if ($id_suite <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro suite é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        if (!$model->verificarDisponibilidadeSuite($id_motel, $id_suite)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Suíte Indisponível.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // Formato padrão: ISO 8601 (2025-11-29T22:36:00)
        $inicio = date('Y-m-d\TH:i:s');
        $periodo = '4:00';
        $chegada = '18:00';

        // Se ambos inicio e chegada estiverem presentes, combina no formato ISO 8601
        if(isset($_GET['inicio']) && isset($_GET['chegada'])){
            $data_inicio = $_GET['inicio'];
            $hora_chegada = $_GET['chegada'];
            
            // Se chegada estiver vazio, usa hora atual + 1h
            if(empty($hora_chegada)){
                $hora_chegada = date('H:i:s', strtotime('+1 hour'));
            } else {
                // Normaliza a hora de chegada (adiciona segundos se necessário para o formato ISO)
                if (preg_match('/^\d{2}:\d{2}$/', $hora_chegada)) {
                    $hora_chegada .= ':00';
                }
                // Usa o valor fornecido SEM somar 1 hora
            }
            
            // Se inicio já estiver no formato ISO completo, usa diretamente
            if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/', $data_inicio)) {
                $inicio = $data_inicio;
            }
            // Se inicio for apenas data (Y-m-d), combina com chegada
            elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $data_inicio)) {
                $inicio = $data_inicio . 'T' . $hora_chegada;
            }
            // Tenta converter outros formatos
            else {
                $timestamp = strtotime($data_inicio);
                if ($timestamp !== false) {
                    $data_formatada = date('Y-m-d', $timestamp);
                    $inicio = $data_formatada . 'T' . $hora_chegada;
                }
            }
            // Formata chegada como H:i (sem segundos) para exibição
            $chegada = preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora_chegada) ? substr($hora_chegada, 0, 5) : $hora_chegada;
        }
        // Se apenas inicio estiver presente
        elseif(isset($_GET['inicio'])){
            $inicio_input = $_GET['inicio'];
            $hora_atual_mais_1h = date('H:i:s', strtotime('+1 hour'));
            
            // Se já estiver no formato ISO, substitui a hora pela hora atual + 1h
            if (preg_match('/^(\d{4}-\d{2}-\d{2})T\d{2}:\d{2}:\d{2}$/', $inicio_input, $matches)) {
                $inicio = $matches[1] . 'T' . $hora_atual_mais_1h;
            } 
            // Se estiver no formato Y-m-d, combina com hora atual + 1h
            elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio_input)) {
                $inicio = $inicio_input . 'T' . $hora_atual_mais_1h;
            }
            // Se tiver outro formato, tenta converter
            else {
                $timestamp = strtotime($inicio_input);
                if ($timestamp !== false) {
                    $data_formatada = date('Y-m-d', $timestamp);
                    $inicio = $data_formatada . 'T' . $hora_atual_mais_1h;
                }
            }
        }
        
        if(isset($_GET['periodo'])){
            $periodo = $_GET['periodo'];
        }
        
        // Se chegada não foi informado ou estiver vazio, usa hora atual + 1h (formato H:i sem segundos)
        if(!isset($_GET['chegada']) || empty($_GET['chegada'])){
            $chegada = date('H:i', strtotime('+1 hour'));
        } else {
            // Se chegada tiver valor, usa o valor fornecido SEM somar 1h
            $chegada = $_GET['chegada'];
            // Remove segundos se existirem (formato H:i)
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $chegada)) {
                $chegada = substr($chegada, 0, 5); // Remove os segundos
            }
        }

        $reserva_id = $model->criarPreReservaTeste($id_motel, $id_suite, $inicio, $periodo, $chegada);

        if (!$reserva_id) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao criar reserva.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['reserva_id' => $reserva_id]);
    }

    public function getReservaReturnID($codigo_reserva)
    {
        // Valida se o código foi fornecido
        if (!$codigo_reserva || empty(trim($codigo_reserva))) {
            return null;
        }

        // Garante que o código seja tratado como string
        $codigo_reserva = (string) $codigo_reserva;
        
        $model = new Api;
        $id_reserva = $model->getReservaByCodigo($codigo_reserva);
        
        // Retorna null se não encontrou, ou o ID se encontrou
        return $id_reserva ? (int) $id_reserva : null;
    }

    public function verReserva()
    {
        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $dados = $model->getReservaComPagamento($id_reserva);
        if (!$dados) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        header('Content-Type: application/json');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public function reservaPaga()
    {
        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;

        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularPagamentoReserva($id_reserva);
        if ($ok) {
            echo json_encode(['result' => 'atualizado'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cancelarReserva()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularCancelamentoReserva($id_reserva, $id_motel);

        if ($ok) {
            echo json_encode(['result' => 'cancelada'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cancelarReservaAutomaticamente($id_reserva, $id_motel)
    {
 
        $model = new Api;
        $ok = $model->simularCancelamentoReserva($id_reserva, $id_motel);

        if ($ok) {
            echo json_encode(['result' => 'cancelada'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function naoPagarReserva()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularNaoPagamentoReserva($id_reserva, $id_motel);
        if ($ok) {
            echo json_encode(['result' => 'recusada'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function receberReservas()
    {
        // Autentica e obtém ID do motel via token
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $model = new Api;
        $reservas = $model->getReservasNaoProcessadasPorMotel($id_motel);
        header('Content-Type: application/json');
        echo json_encode([
            'reservas' => $reservas
        ], JSON_UNESCAPED_UNICODE);
    }

    public function reservaProcessado()
    {
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro código é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (isset($_GET['status_reserva'])) {
            $status_reserva = $_GET['status_reserva'];
        }else{
            $status_reserva = 'Aceito';
        }
        $model = new Api;
        $model->marcarReservasComoProcessadasPorMotel($id_reserva, $id_motel, $status_reserva);

        if ($status_reserva == 'Recusado' || $status_reserva == 'Cancelado') {   
            $this->cancelarReservaAutomaticamente($id_reserva, $id_motel);
        }
        
        echo 'ok';
    }

    public function confirmarCheckin()
    {
        $id_motel = $this->autenticarToken($_GET['motel'] ?? null);
        if (!$id_motel) return;

        $codigo_reserva = isset($_GET['codigo']) ? $_GET['codigo'] : null;
        
        if (!$codigo_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro código é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        $id_reserva = $this->getReservaReturnID($codigo_reserva);
        if (!$id_reserva) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model = new Api;
        $model->confirmarCheckinReserva($id_reserva, $id_motel);
        echo json_encode(['result' => 'ok'], JSON_UNESCAPED_UNICODE);
        
    }
}

