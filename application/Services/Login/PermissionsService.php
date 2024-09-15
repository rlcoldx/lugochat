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
        $permissions = $this->getPermissions();
        $found = false;
        foreach($permissions as $permission) {
            if($permissionRequired === $permission) {
                $found = true;
            }
        }
        return $found;
    }
}

