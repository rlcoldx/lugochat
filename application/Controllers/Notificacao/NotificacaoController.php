<?php

namespace Agencia\Close\Controllers\Notificacao;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\Notificacao\NotificacaoModel;
use Agencia\Close\Services\Login\LoginSession;
use Agencia\Close\Services\Notificacao\OneSignalService;

class NotificacaoController extends Controller
{
    public function index($params)
    {
        $this->setParams($params);
        $this->render('components/notificacao/criar.twig', []);
    }

    public function enviarNotificacao($params)
    {
        $this->setParams($params);

        $model = new NotificacaoModel();
        $oneSignal = new OneSignalService();
        $offset = 0;
        $limit = 10000;

        do {
            $usuarios = $model->getUsersID($offset, $limit)->getResult();

            if (empty($usuarios)) {
                break;
            }

            $codes = [];
            foreach ($usuarios as $usuario) {
                if (!empty($usuario['pushKey'])) {
                    $codes[] = $usuario['pushKey'];
                }
            }

            if (!empty($codes)) {
                $oneSignal->send($codes, $params['titulo'], $params['mensagem']);
            }

            $offset += $limit;
        } while (count($usuarios) === $limit);

        $this->responseJson([
            'status' => 'success',
            'message' => 'Notificações enviadas com sucesso!',
        ]);
    }

    /**
     * Salva o subscription ID (OneSignal) do usuário logado no painel.
     */
    public function salvarPush($params)
    {
        $this->setParams($params);

        $loginSession = new LoginSession();
        if (!$loginSession->userIsLogged()) {
            http_response_code(401);
            $this->responseJson(['status' => 'error', 'message' => 'Não autenticado.']);
            return;
        }

        $subscriptionId = trim($_POST['subscription_id'] ?? $_POST['pushKey'] ?? '');
        if ($subscriptionId === '') {
            http_response_code(400);
            $this->responseJson(['status' => 'error', 'message' => 'Subscription ID inválido.']);
            return;
        }

        $model = new NotificacaoModel();
        $ok = $model->salvarPushKey((int) $loginSession->getUserId(), $subscriptionId);

        $this->responseJson([
            'status' => $ok ? 'success' : 'error',
            'message' => $ok ? 'Notificações ativadas.' : 'Não foi possível salvar.',
        ]);
    }
}
