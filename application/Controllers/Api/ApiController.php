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

        $reserva_id = $model->criarPreReservaTeste($id_motel, $id_suite);

        if (!$reserva_id) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao criar reserva.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(['reserva_id' => $reserva_id]);
    }

    public function verReserva()
    {
        $codigo = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $dados = $model->getReservaComPagamento($codigo);
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
        $codigo = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularPagamentoReserva($codigo);
        if ($ok) {
            echo json_encode(['result' => 'atualizado'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function cancelarReserva()
    {
        $codigo = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularCancelamentoReserva($codigo);
        if ($ok) {
            echo json_encode(['result' => 'cancelada'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function naoPagarReserva()
    {
        $codigo = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$codigo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro codigo é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Api;
        $ok = $model->simularNaoPagamentoReserva($codigo);
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
        $id_reserva = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$id_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro código é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model = new Api;
        $model->marcarReservasComoProcessadasPorMotel($id_reserva);
        echo 'ok';
    }

    public function confirmarCheckin()
    {
        $id_reserva = isset($_GET['codigo']) ? intval($_GET['codigo']) : null;
        if (!$id_reserva) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro código é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model = new Api;
        $model->confirmarCheckinReserva($id_reserva);
        echo json_encode(['result' => 'ok'], JSON_UNESCAPED_UNICODE);
        
    }
}

