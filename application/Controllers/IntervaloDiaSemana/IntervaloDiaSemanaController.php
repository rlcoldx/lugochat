<?php

namespace Agencia\Close\Controllers\IntervaloDiaSemana;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Feriados\MotelIntervaloDiaSemana;
use Agencia\Close\Services\Login\PermissionsService;

class IntervaloDiaSemanaController extends Controller
{
    public function index($params)
    {
        $this->setParams($params);
        $this->requireIntervaloMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $model = new MotelIntervaloDiaSemana();
        $raw = $model->listarPorMotel($idMotel);
        $regras = [];
        foreach ($raw as $row) {
            $row['periodo_txt'] = $model->textoPeriodo($row);
            $regras[] = $row;
        }

        $this->render('pages/intervalo-dia-semana/index.twig', [
            'titulo' => 'Intervalos por dia da semana',
            'regras' => $regras,
        ]);
    }

    public function criar($params)
    {
        $this->setParams($params);
        $this->requireIntervaloMenuPermission();
        if ($this->idMotelLogado() === null) {
            $this->router->redirect('/');
            return;
        }

        $this->render('components/intervalo-dia-semana/form.twig', [
            'regra' => null,
            'inv' => [
                'dia_semana_inicio' => '',
                'dia_semana_fim' => '',
                'hora_inicio' => '',
                'hora_fim' => '',
            ],
        ]);
    }

    public function editar($params)
    {
        $this->setParams($params);
        $this->requireIntervaloMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) {
            $this->router->redirect('/intervalos-dia-semana');
            return;
        }

        $model = new MotelIntervaloDiaSemana();
        $row = $model->obterDoMotel($idMotel, $id);
        if ($row === null) {
            $this->router->redirect('/intervalos-dia-semana');
            return;
        }

        $hi = $row['hora_inicio'] ?? '';
        $hf = $row['hora_fim'] ?? '';
        if (is_string($hi) && strlen($hi) > 5) {
            $hi = substr($hi, 0, 5);
        }
        if (is_string($hf) && strlen($hf) > 5) {
            $hf = substr($hf, 0, 5);
        }

        $this->render('components/intervalo-dia-semana/form.twig', [
            'regra' => $row,
            'inv' => [
                'dia_semana_inicio' => MotelIntervaloDiaSemana::normalizarDiaAbrev($row['dia_semana_inicio'] ?? ''),
                'dia_semana_fim' => MotelIntervaloDiaSemana::normalizarDiaAbrev($row['dia_semana_fim'] ?? ''),
                'hora_inicio' => $hi ?: '',
                'hora_fim' => $hf ?: '',
            ],
        ]);
    }

    public function salvar($params)
    {
        $this->setParams($params);
        $this->requireIntervaloMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $nome = isset($_POST['nome']) ? trim((string) $_POST['nome']) : '';
        $regraId = isset($_POST['regra_id']) ? (int) $_POST['regra_id'] : 0;
        $jsonResponse = isset($_POST['json_response']) && $_POST['json_response'] === '1';

        $intervalo = $this->parseIntervaloPost();

        if ($intervalo === null) {
            if ($jsonResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'code' => 'validacao'], JSON_UNESCAPED_UNICODE);
                return;
            }
            $this->redirectUrl(DOMAIN . '/intervalos-dia-semana?erro=validacao');
            return;
        }

        $model = new MotelIntervaloDiaSemana();

        if ($regraId > 0) {
            $ok = $model->atualizar($idMotel, $regraId, $nome, $intervalo);
        } else {
            $ok = $model->inserirLote($idMotel, $nome, [$intervalo]);
        }

        if ($jsonResponse) {
            header('Content-Type: application/json; charset=utf-8');
            if ($ok) {
                echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['ok' => false, 'code' => 'salvar'], JSON_UNESCAPED_UNICODE);
            }
            return;
        }

        $this->redirectUrl(DOMAIN . '/intervalos-dia-semana' . ($ok ? '?ok=1' : '?erro=salvar'));
    }

    public function excluir($params)
    {
        $this->setParams($params);
        $this->requireIntervaloMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            echo 'error';
            return;
        }

        $id = isset($_POST['id_regra']) ? (int) $_POST['id_regra'] : 0;
        if ($id <= 0) {
            echo 'error';
            return;
        }

        $model = new MotelIntervaloDiaSemana();
        $ok = $model->excluir($idMotel, $id);
        echo $ok ? 'success' : 'error';
    }

    /**
     * @return array{dia_semana_inicio: string, hora_inicio: string, dia_semana_fim: string, hora_fim: string}|null
     */
    private function parseIntervaloPost(): ?array
    {
        $di = MotelIntervaloDiaSemana::normalizarDiaAbrev($_POST['dia_semana_inicio'] ?? null);
        $df = MotelIntervaloDiaSemana::normalizarDiaAbrev($_POST['dia_semana_fim'] ?? null);
        if ($di === '' || $df === '') {
            return null;
        }
        $hi = isset($_POST['hora_inicio']) ? trim((string) $_POST['hora_inicio']) : '';
        $hf = isset($_POST['hora_fim']) ? trim((string) $_POST['hora_fim']) : '';
        if (strlen($hi) > 5) {
            $hi = substr($hi, 0, 5);
        }
        if (strlen($hf) > 5) {
            $hf = substr($hf, 0, 5);
        }
        if ($hi === '' || $hf === '') {
            return null;
        }
        return [
            'dia_semana_inicio' => $di,
            'hora_inicio' => $hi,
            'dia_semana_fim' => $df,
            'hora_fim' => $hf,
        ];
    }

    private function requireIntervaloMenuPermission(): void
    {
        $permissionService = new PermissionsService();
        if (
            $permissionService->verifyPermissions('suites_ver')
            || $permissionService->verifyPermissions('config_ver')
        ) {
            return;
        }
        echo 'você não tem permissão para acessar esse serviço!';
        die();
    }

    private function idMotelLogado(): ?int
    {
        if (!isset($_SESSION['busca_perfil_tipo']) || (int) $_SESSION['busca_perfil_tipo'] === 0) {
            return null;
        }
        $id = isset($_SESSION['busca_perfil_empresa']) ? (int) $_SESSION['busca_perfil_empresa'] : 0;
        return $id > 0 ? $id : null;
    }
}
