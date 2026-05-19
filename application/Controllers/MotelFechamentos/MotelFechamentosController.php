<?php

namespace Agencia\Close\Controllers\MotelFechamentos;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\MotelFechamentos\MotelFechamentos;
use Agencia\Close\Models\Moteis\Moteis;

class MotelFechamentosController extends Controller
{
    public function index($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $model = new MotelFechamentos();
        $this->render('pages/motel-fechamentos/index.twig', [
            'titulo' => 'Fechamentos de Motéis',
            'grupos' => $model->listarGrupos(),
        ]);
    }

    public function criar($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $moteis = new Moteis();
        $proprietarios = array_values(array_filter(array_column(
            $moteis->getProprietariosList()->getResult() ?: [],
            'proprietario'
        )));

        $this->render('components/motel-fechamentos/form.twig', [
            'item' => null,
            'proprietarios' => $proprietarios,
        ]);
    }

    public function editar($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $idGrupo = isset($params['id_grupo']) ? trim((string) $params['id_grupo']) : '';
        if ($idGrupo === '') {
            $this->router->redirect('/admin/motel-fechamentos');
            return;
        }

        $model = new MotelFechamentos();
        $item = $model->obterGrupo($idGrupo);
        if ($item === null) {
            $this->router->redirect('/admin/motel-fechamentos');
            return;
        }

        $moteis = new Moteis();
        $proprietarios = array_values(array_filter(array_column(
            $moteis->getProprietariosList()->getResult() ?: [],
            'proprietario'
        )));

        $this->render('components/motel-fechamentos/form.twig', [
            'item' => $item,
            'proprietarios' => $proprietarios,
        ]);
    }

    public function moteisPorProprietario($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $proprietario = isset($_GET['proprietario']) ? trim((string) $_GET['proprietario']) : '';
        $model = new MotelFechamentos();
        $lista = $model->listarMoteisPorProprietario($proprietario);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['moteis' => $lista], JSON_UNESCAPED_UNICODE);
    }

    public function salvar($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $date = isset($_POST['date_fechamento']) ? trim((string) $_POST['date_fechamento']) : '';
        $proprietario = isset($_POST['proprietario']) ? trim((string) $_POST['proprietario']) : '';
        $idGrupo = isset($_POST['id_grupo']) ? trim((string) $_POST['id_grupo']) : '';
        $jsonResponse = isset($_POST['json_response']) && $_POST['json_response'] === '1';

        $idMoteis = [];
        if (isset($_POST['id_motel']) && is_array($_POST['id_motel'])) {
            $idMoteis = $_POST['id_motel'];
        }

        if (!$this->dataValidaYmd($date) || $proprietario === '' || $idMoteis === []) {
            $this->responderSalvar(['ok' => false, 'code' => 'validacao'], $jsonResponse);
            return;
        }

        $model = new MotelFechamentos();
        $idGrupoEdicao = $idGrupo !== '' ? $idGrupo : null;
        $result = $model->salvarGrupo($date, $proprietario, $idMoteis, $idGrupoEdicao);

        $this->responderSalvar($result, $jsonResponse);
    }

    public function excluir($params)
    {
        $this->requireAdmin();
        $this->setParams($params);

        $idGrupo = isset($_POST['id_grupo']) ? trim((string) $_POST['id_grupo']) : '';
        if ($idGrupo === '') {
            echo 'error';
            return;
        }

        $model = new MotelFechamentos();
        echo $model->excluirGrupo($idGrupo) ? 'success' : 'error';
    }

    private function responderSalvar(array $result, bool $jsonResponse): void
    {
        if ($jsonResponse) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!empty($result['ok'])) {
            $this->redirectUrl(DOMAIN . '/admin/motel-fechamentos?ok=1');
            return;
        }

        $code = $result['code'] ?? 'salvar';
        $this->redirectUrl(DOMAIN . '/admin/motel-fechamentos?erro=' . rawurlencode($code));
    }

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['busca_perfil_tipo']) || (int) $_SESSION['busca_perfil_tipo'] !== 0) {
            $this->router->redirect('/');
            exit;
        }
    }

    private function dataValidaYmd(string $data): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }
}
