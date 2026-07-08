<?php

namespace Agencia\Close\Controllers\Clientes;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Clientes\Clientes;
use Agencia\Close\Helpers\Pagination\Pagination;

class ClientesController extends Controller
{	

  public function index($params)
  {
    $this->setParams($params);

    $busca = trim((string) ($_GET['search'] ?? ''));
    $page = (int) ($_GET['page'] ?? 1);
    if ($page < 1) {
      $page = 1;
    }
    $limit = 25;
    $offset = ($page - 1) * $limit;

    $model = new Clientes();

    if ($_SESSION['busca_perfil_tipo'] != '0') {
      $clientes = $model->getClientesByCompany($limit, $offset, $busca)->getResult();
      $total = $model->contarClientesByCompany($busca);
    } else {
      $clientes = $model->getClientes($limit, $offset, $busca)->getResult();
      $total = $model->contarClientes($busca);
    }

    $pagination = new Pagination();
    $pagination->setActualPage($page);
    $pagination->setLastPage((int) ceil(($total ?: 1) / $limit));
    $pagination->setUrl(DOMAIN . '/clientes');

    $this->render('pages/clientes/index.twig', [
      'titulo' => 'Lista de Clientes',
      'clientes' => $clientes,
      'pagination' => $pagination->getArray(),
      'filters' => ['search' => $busca],
      'total' => $total,
      'offset' => $offset,
    ]);
  }

  public function banir($params)
  {
    $this->setParams($params);

    $id = $_POST['id'] ?? null;
    $motivo = trim((string) ($_POST['motivo'] ?? ''));

    if (!$id) {
      $this->responseJson(['success' => false, 'message' => 'Cliente inválido.']);
      return;
    }

    $model = new Clientes();
    $usuario = $model->getUsuarioById($id)->getResultSingle();

    if (empty($usuario['id'])) {
      $this->responseJson(['success' => false, 'message' => 'Cliente não encontrado.']);
      return;
    }

    if ($model->banimentoAtivo($id)->getResult()) {
      $this->responseJson(['success' => false, 'message' => 'Este cliente já está banido.']);
      return;
    }

    $banidoPor = $_SESSION['busca_perfil_id'] ?? null;
    $idBanido = $model->banirCliente($id, $usuario, $banidoPor, $motivo);

    if (!$idBanido) {
      $this->responseJson(['success' => false, 'message' => 'Não foi possível banir o cliente.']);
      return;
    }

    $this->responseJson(['success' => true, 'message' => 'Cliente banido com sucesso.']);
  }

  public function desbanir($params)
  {
    $this->setParams($params);

    $id = $_POST['id'] ?? null;

    if (!$id) {
      $this->responseJson(['success' => false, 'message' => 'Cliente inválido.']);
      return;
    }

    $model = new Clientes();
    $result = $model->desbanirCliente($id);

    if (!$result->getResult()) {
      $this->responseJson(['success' => false, 'message' => 'Não foi possível remover o banimento.']);
      return;
    }

    $this->responseJson(['success' => true, 'message' => 'Banimento removido com sucesso.']);
  }

}