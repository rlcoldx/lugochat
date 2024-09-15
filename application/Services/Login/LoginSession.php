<?php

namespace Agencia\Close\Services\Login;

class LoginSession
{
    public function loginUser(array $login)
    {
        $_SESSION = [
            'lugo_perfil_id' => $login['id'],
            'lugo_perfil_empresa' => $login['empresa'],
            'lugo_perfil_tipo' => $login['tipo'],
            'lugo_perfil_cargo' => $login['cargo'],
            'lugo_perfil_slug' => $login['slug'],
            'lugo_perfil_nome' => $login['nome'],
            'lugo_perfil_email' => $login['email'],
            'lugo_perfil_avatar' => $login['avatar']
        ];
    }

    public function userIsLogged(): bool
    {
        if (isset($_SESSION['lugo_perfil_id'])){
            return true;
        }
        return false;
    }

    public function getUserId() {
        return $_SESSION['lugo_perfil_id'];
    }
}