<?php

namespace Agencia\Close\Controllers\Feriados;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Feriados\Feriados;
use Agencia\Close\Services\Login\PermissionsService;

class FeriadosController extends Controller
{
    /**
     * Sincroniza feriados (API Nager.Date) na tabela `feriados` com id_motel = 0.
     * Query opcional: ?year=2026&country=BR
     */
    public function syncNager($params)
    {
        $this->setParams($params);

        $year = isset($_GET['year']) ? (int) $_GET['year'] : (int) date('Y');
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }
        $country = isset($_GET['country']) ? (string) $_GET['country'] : 'BR';

        $model = new Feriados();
        $result = $model->syncFromNagerAt($year, $country);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    public function index($params)
    {
        $this->setParams($params);
        $this->requireFeriadosMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $model = new Feriados();
        $lista = $model->listarParaMotel($idMotel);

        $this->render('pages/feriados/index.twig', [
            'titulo' => 'Feriados',
            'feriados' => $lista,
        ]);
    }

    public function criar($params)
    {
        $this->setParams($params);
        $this->requireFeriadosMenuPermission();
        if ($this->idMotelLogado() === null) {
            $this->router->redirect('/');
            return;
        }

        $this->render('components/feriados/form.twig', [
            'item' => null,
        ]);
    }

    public function editar($params)
    {
        $this->setParams($params);
        $this->requireFeriadosMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $date = isset($params['date']) ? trim((string) $params['date']) : '';
        if (!$this->dataValidaYmd($date)) {
            $this->router->redirect('/feriados');
            return;
        }

        $model = new Feriados();
        $row = $model->obterProprioDoMotel($idMotel, $date);
        if ($row === null) {
            $this->router->redirect('/feriados');
            return;
        }

        $this->render('components/feriados/form.twig', [
            'item' => $row,
        ]);
    }

    public function salvar($params)
    {
        $this->setParams($params);
        $this->requireFeriadosMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            $this->router->redirect('/');
            return;
        }

        $date = isset($_POST['date']) ? trim((string) $_POST['date']) : '';
        $feriado = isset($_POST['feriado']) ? trim((string) $_POST['feriado']) : '';
        $dateOriginal = isset($_POST['date_original']) ? trim((string) $_POST['date_original']) : '';
        $jsonResponse = isset($_POST['json_response']) && $_POST['json_response'] === '1';

        if (!$this->dataValidaYmd($date) || $feriado === '') {
            if ($jsonResponse) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'code' => 'validacao'], JSON_UNESCAPED_UNICODE);
                return;
            }
            $this->redirectUrl(DOMAIN . '/feriados?erro=validacao');
            return;
        }

        $model = new Feriados();

        if ($dateOriginal === '') {
            if ($model->existeProprioDoMotel($idMotel, $date)) {
                if ($jsonResponse) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'code' => 'duplicado'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $this->redirectUrl(DOMAIN . '/feriados?erro=duplicado');
                return;
            }
            $ok = $model->inserirProprio($idMotel, $date, $feriado);
        } else {
            if (!$this->dataValidaYmd($dateOriginal)) {
                if ($jsonResponse) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'code' => 'validacao'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $this->redirectUrl(DOMAIN . '/feriados?erro=validacao');
                return;
            }
            if ($date !== $dateOriginal && $model->existeProprioDoMotel($idMotel, $date)) {
                if ($jsonResponse) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'code' => 'duplicado'], JSON_UNESCAPED_UNICODE);
                    return;
                }
                $this->redirectUrl(DOMAIN . '/feriados?erro=duplicado');
                return;
            }
            $ok = $model->atualizarProprio($idMotel, $dateOriginal, $date, $feriado);
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

        $this->redirectUrl(DOMAIN . '/feriados' . ($ok ? '?ok=1' : '?erro=salvar'));
    }

    public function excluir($params)
    {
        $this->setParams($params);
        $this->requireFeriadosMenuPermission();
        $idMotel = $this->idMotelLogado();
        if ($idMotel === null) {
            echo 'error';
            return;
        }

        $date = isset($_POST['date']) ? trim((string) $_POST['date']) : '';
        if (!$this->dataValidaYmd($date)) {
            echo 'error';
            return;
        }

        $model = new Feriados();
        $ok = $model->excluirProprio($idMotel, $date);
        echo $ok ? 'success' : 'error';
    }

    private function requireFeriadosMenuPermission(): void
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

    private function dataValidaYmd(string $data): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
}
