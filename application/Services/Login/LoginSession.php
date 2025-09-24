<?php

namespace Agencia\Close\Services\Login;

use Agencia\Close\Models\User\Permissions;

class LoginSession
{
    public function loginUser(array $login)
    {
        $_SESSION = [
            'busca_perfil_id' => $login['id'],
            'busca_perfil_empresa' => $login['empresa'],
            'busca_perfil_tipo' => $login['tipo'],
            'busca_perfil_master' => $login['master'],
            'busca_perfil_cargo' => $login['cargo'],
            'busca_perfil_slug' => $login['slug'],
            'busca_perfil_nome' => $login['nome'],
            'busca_perfil_email' => $login['email'],
            'busca_perfil_logo' => $login['logo']
        ];
        
        // Carrega as permissões do usuário
        $this->loadUserPermissions($login['id'], $login['tipo']);
    }
    
    private function loadUserPermissions($userId, $userType)
    {
        // Se for admin (tipo 1) ou empresa (tipo 2), não precisa carregar permissões específicas
        if ($userType == 1 || $userType == 2) {
            $_SESSION['permissoes'] = json_encode([]); // Array vazio, pois tem acesso total
            return;
        }
        
        // Para outros tipos, carrega as permissões do cargo
        $permissionsModel = new Permissions();
        $permissions = $permissionsModel->getPermissionsByUser($userId);
        
        if ($permissions->getResult()) {
            $permissionNames = array_column($permissions->getResult(), 'nome');
            $_SESSION['permissoes'] = json_encode($permissionNames);
        } else {
            $_SESSION['permissoes'] = json_encode([]);
        }
    }
    
    /**
     * Método público para recarregar permissões quando necessário
     */
    public function reloadPermissions()
    {
        if (!isset($_SESSION['busca_perfil_id']) || !isset($_SESSION['busca_perfil_tipo'])) {
            return false;
        }
        
        $this->loadUserPermissions($_SESSION['busca_perfil_id'], $_SESSION['busca_perfil_tipo']);
        return true;
    }

    public function userIsLogged(): bool
    {
        if (isset($_SESSION['busca_perfil_id'])){
            return true;
        }
        return false;
    }

    public function getUserId() {
        return $_SESSION['busca_perfil_id'];
    }
}