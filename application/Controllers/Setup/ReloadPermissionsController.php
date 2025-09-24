<?php

namespace Agencia\Close\Controllers\Setup;

use Agencia\Close\Controllers\Controller;
use Agencia\Close\Models\User\Permissions;

class ReloadPermissionsController extends Controller
{
    public function reload($params)
    {
        // Verifica se o usuário está logado
        if (!isset($_SESSION['busca_perfil_id'])) {
            $this->responseJson(['success' => false, 'message' => 'Usuário não está logado']);
            return;
        }

        $userId = $_SESSION['busca_perfil_id'];
        $userType = $_SESSION['busca_perfil_tipo'];

        // Se for admin (tipo 1) ou empresa (tipo 2), não precisa carregar permissões específicas
        if ($userType == 1 || $userType == 2) {
            $_SESSION['permissoes'] = json_encode([]); // Array vazio, pois tem acesso total
            $this->responseJson([
                'success' => true, 
                'message' => 'Permissões recarregadas (Admin/Empresa - acesso total)',
                'user_type' => $userType,
                'permissions' => []
            ]);
            return;
        }

        // Para outros tipos, carrega as permissões do cargo
        $permissionsModel = new Permissions();
        $permissions = $permissionsModel->getPermissionsByUser($userId);
        
        // Debug: verifica informações do cargo
        $debugCargo = $permissionsModel->debugCargoPermissions($userId);

        if ($permissions->getResult()) {
            $permissionNames = array_column($permissions->getResult(), 'nome');
            $_SESSION['permissoes'] = json_encode($permissionNames);
            
            $this->responseJson([
                'success' => true, 
                'message' => 'Permissões recarregadas com sucesso',
                'user_type' => $userType,
                'permissions' => $permissionNames,
                'count' => count($permissionNames),
                'debug_cargo' => $debugCargo->getResult()
            ]);
        } else {
            $_SESSION['permissoes'] = json_encode([]);
            $this->responseJson([
                'success' => false, 
                'message' => 'Nenhuma permissão encontrada para este usuário',
                'user_type' => $userType,
                'permissions' => [],
                'debug_cargo' => $debugCargo->getResult()
            ]);
        }
    }
}
