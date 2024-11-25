<?php

namespace Agencia\Close\Services\Login;

class LoginSession
{
    public function loginUser(array $login)
    {
        $_SESSION = [
            'busca_perfil_id' => $login['id'],
            'busca_perfil_empresa' => $login['empresa'],
            'busca_perfil_tipo' => $login['tipo'],
            'busca_perfil_cargo' => $login['cargo'],
            'busca_perfil_slug' => $login['slug'],
            'busca_perfil_nome' => $login['nome'],
            'busca_perfil_email' => $login['email'],
            'busca_perfil_logo' => $login['logo']
        ];
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