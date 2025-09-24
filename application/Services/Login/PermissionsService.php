<?php 

namespace Agencia\Close\Services\Login;

class PermissionsService {

    public function setPermissionsByDB($permissionsList) {
        $permissionArray = [];
        
        foreach($permissionsList as $permission) {
            $permissionArray[] = $permission['nome'];
        }
        $this->setPermissions($permissionArray);
    }

    public function setPermissions($permissions) {
        $_SESSION['permissoes'] = json_encode($permissions);
    }

    public function getPermissions() {
        return json_decode($_SESSION['permissoes']);
    }

    public function verifyPermissions($permissionRequired) {
        // Usuário tipo 1 (admin) e tipo 2 (empresa/motel) têm acesso a tudo
        if (isset($_SESSION['busca_perfil_tipo']) && 
            ($_SESSION['busca_perfil_tipo'] == 1 || $_SESSION['busca_perfil_tipo'] == 2)) {
            return true;
        }
        
        $permissions = $this->getPermissions();
        if (!$permissions) {
            return false;
        }
        
        $found = false;
        foreach($permissions as $permission) {
            if($permissionRequired === $permission) {
                $found = true;
                break;
            }
        }
        return $found;
    }
    
    /**
     * Método de debug para verificar permissões
     */
    public function debugPermissions() {
        return [
            'session_permissoes' => $_SESSION['permissoes'] ?? 'não definido',
            'decoded_permissions' => $this->getPermissions(),
            'user_type' => $_SESSION['busca_perfil_tipo'] ?? 'não definido',
            'user_id' => $_SESSION['busca_perfil_id'] ?? 'não definido'
        ];
    }
}

