<?php

namespace Agencia\Close\Middleware;

use Agencia\Close\Services\Login\LoginSession;

class LoadPermissionsMiddleware extends Middleware
{
    public function run()
    {
        // Verifica se o usuário está logado
        if (!isset($_SESSION['busca_perfil_id'])) {
            return;
        }
        
        // Se as permissões não estão carregadas, carrega elas
        if (!isset($_SESSION['permissoes']) || $_SESSION['permissoes'] === '[]' || $_SESSION['permissoes'] === '') {
            $loginSession = new LoginSession();
            $loginSession->reloadPermissions();
        }
    }
}
