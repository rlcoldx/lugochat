<?php
namespace Agencia\Close\Controllers\Rubens;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Rubens\Rubens;

class RubensController extends Controller
{
    public function suites()
    {
        $model = new Rubens;
        $suites = $model->getSuites()->getResult();
        $json_result = $suites[0]["json_result"];
        echo $json_result;
    }

    public function disponibilidade($params)
    {
        $this->setParams($params);
        $model = new Rubens;

        $checkMotel = $model->checkMotelRubens($_GET['motel'])->getResult();

        if (!empty($checkMotel)) {
            $resultado = $model->updateDisponibilidade($_GET);
            if ($resultado === true) {
                echo 'ok';
            } else {
                echo $resultado;
            }
        }else{
            echo 'Erro: Motel não encontrado';
        }
    }

    public function qtde_disp()
    {
        $model = new Rubens;
        $disponibilidade = $model->getDisponibilidade($_GET)->getResult();
        $json_result = $disponibilidade[0]["json_result"];
        echo $json_result;
    }

    public function CriarReservaTeste()
    {
        $id_motel = isset($_GET['motel']) ? intval($_GET['motel']) : null;
        $id_suite = isset($_GET['suite']) ? intval($_GET['suite']) : null;
        if ($id_motel <= 0 || $id_suite <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetros motel e suite são obrigatórios.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $model = new Rubens;
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

        $model = new Rubens;
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

        $model = new Rubens;
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

        $model = new Rubens;
        // Busca a reserva para obter o id_motel
        $reserva = $model->getReservaComPagamento($codigo);
        if (!$reserva || !isset($reserva['id_motel'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $ok = $model->simularCancelamentoReserva($codigo, $reserva['id_motel']);
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

        $model = new Rubens;
        // Busca a reserva para obter o id_motel
        $reserva = $model->getReservaComPagamento($codigo);
        if (!$reserva || !isset($reserva['id_motel'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $ok = $model->simularNaoPagamentoReserva($codigo, $reserva['id_motel']);
        if ($ok) {
            echo json_encode(['result' => 'recusada'], JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada ou erro ao atualizar.'], JSON_UNESCAPED_UNICODE);
        }
    }

    public function receberReservas()
    {
        $id_motel = isset($_GET['motel']) ? intval($_GET['motel']) : null;
        if (!$id_motel) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro motel é obrigatório.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model = new Rubens;
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
        $model = new Rubens;
        // Busca a reserva para obter o id_motel
        $reserva = $model->getReservaComPagamento($id_reserva);
        if (!$reserva || !isset($reserva['id_motel'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model->marcarReservasComoProcessadasPorMotel($id_reserva, $reserva['id_motel']);
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
        $model = new Rubens;
        // Busca a reserva para obter o id_motel
        $reserva = $model->getReservaComPagamento($id_reserva);
        if (!$reserva || !isset($reserva['id_motel'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'Reserva não encontrada.'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $model->confirmarCheckinReserva($id_reserva, $reserva['id_motel']);
        echo json_encode(['result' => 'ok'], JSON_UNESCAPED_UNICODE);
        
    }
}