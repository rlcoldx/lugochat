<?php

namespace Agencia\Close\Controllers\Api;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Api\Desktop;

class DesktopApiController extends Controller
{
    private function cors(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Origin, Accept');
        header('Content-Type: application/json; charset=UTF-8');
    }

    private function responder(string $resultado, array $dados = []): void
    {
        echo json_encode(array_merge(['result' => $resultado], $dados), JSON_UNESCAPED_UNICODE);
    }

    /**
     * Login do app desktop — apenas usuários tipo 2 (motel).
     *
     * POST JSON: { "email": "...", "senha": "..." }
     * Respostas: result = Ok | Inativo | Erro
     */
    public function login(): void
    {
        $this->cors();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            $payload = $_POST;
        }

        $email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';
        $senha = isset($payload['senha']) ? (string) $payload['senha'] : '';

        if ($email === '' || $senha === '') {
            $this->responder('Erro', ['message' => 'E-mail e senha são obrigatórios.']);
            return;
        }

        $model = new Desktop();
        $usuario = $model->getUsuarioByEmail($email)->getResultSingle();

        if (empty($usuario['id'])) {
            $this->responder('Erro', ['message' => 'Usuário ou senha incorretos.']);
            return;
        }

        if (($usuario['status'] ?? '') !== 'Ativo') {
            $this->responder('Inativo', ['message' => 'Usuário inativo.']);
            return;
        }

        $senhaHash = sha1($senha);

        if (!$model->senhaConfere($email, $senhaHash)) {
            $this->responder('Erro', ['message' => 'Usuário ou senha incorretos.']);
            return;
        }

        $this->responder('Ok', [
            'usuario' => [
                'id'      => (int) $usuario['id'],
                'nome'    => $usuario['nome'] ?? '',
                'email'   => $usuario['email'] ?? '',
                'empresa' => (int) ($usuario['empresa'] ?? 0),
                'tipo'    => (int) ($usuario['tipo'] ?? 0),
                'logo'    => $usuario['logo'] ?? '',
                'codigo'  => $usuario['codigo'] ?? '',
                'cargo'   => $usuario['cargo'] ?? '',
            ],
        ]);
    }
}
