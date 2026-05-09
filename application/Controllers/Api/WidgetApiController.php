<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Bot\Bot;
use Agencia\Close\Models\SuitesWidget\SuitesWidget;
use Agencia\Close\Models\Widget\Widget;

class WidgetApiController extends Controller
{
    private function cors(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Origin, Accept");
        header("Content-Type: application/json; charset=UTF-8");
    }

    private function motelByCode(?string $cc): ?array
    {
        $cc = is_string($cc) ? trim($cc) : '';
        if ($cc === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro cc é obrigatório'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        $model = new Widget();
        $empresa = $model->getEmpresaByCode($cc)->getResultSingle();
        if (!$empresa || empty($empresa['id'])) {
            http_response_code(404);
            echo json_encode(['erro' => 'Motel não encontrado ou inativo'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        return $empresa;
    }

    public function bootstrap(): void
    {
        $this->cors();

        $empresa = $this->motelByCode($_GET['cc'] ?? null);
        if ($empresa === null) {
            return;
        }

        $bot = new Bot();
        $items = $bot->getQuestions((int) $empresa['id'])->getResult() ?? [];

        echo json_encode([
            'motel' => [
                'id' => (int) $empresa['id'],
                'nome' => $empresa['nome'] ?? '',
                'codigo' => $empresa['codigo'] ?? '',
            ],
            'bot' => [
                'items' => $items,
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    public function suites(): void
    {
        $this->cors();

        $empresa = $this->motelByCode($_GET['cc'] ?? null);
        if ($empresa === null) {
            return;
        }

        $model = new SuitesWidget();
        $suites = $model->getSuites((int) $empresa['id'])->getResult() ?? [];

        echo json_encode([
            'motel' => [
                'id' => (int) $empresa['id'],
            ],
            'suites' => $suites,
        ], JSON_UNESCAPED_UNICODE);
    }

    public function suiteDetalhes(): void
    {
        $this->cors();

        $empresa = $this->motelByCode($_GET['cc'] ?? null);
        if ($empresa === null) {
            return;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['erro' => 'Parâmetro id é obrigatório'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $suiteModel = new SuitesWidget();
        $suiteRead = $suiteModel->getSuite($id, (int) $empresa['id'])->getResult();
        $suite = $suiteRead[0] ?? null;

        if (!$suite) {
            http_response_code(404);
            echo json_encode(['erro' => 'Suíte não encontrada'], JSON_UNESCAPED_UNICODE);
            return;
        }

        // preços e imagens (mesma lógica do controller do widget)
        $diaDaSemana = date('D');
        $map = ['Mon' => 'seg', 'Tue' => 'ter', 'Wed' => 'qua', 'Thu' => 'qui', 'Fri' => 'sex', 'Sat' => 'sab', 'Sun' => 'dom'];
        $dia = $map[$diaDaSemana] ?? 'seg';

        $precosall = (new SuitesWidget())->getSuitePrecosAll($id, (int) $empresa['id'])->getResult() ?? [];
        $precos = (new SuitesWidget())->getSuitePrecos($id, (int) $empresa['id'], $dia)->getResult() ?? [];
        $imagens = (new SuitesWidget())->getSuiteImages($id, (int) $empresa['id'])->getResult() ?? [];

        echo json_encode([
            'motel' => ['id' => (int) $empresa['id']],
            'suite' => $suite,
            'precosall' => $precosall,
            'precos' => $precos,
            'imagens' => $imagens,
            'dia' => $dia,
        ], JSON_UNESCAPED_UNICODE);
    }
}

