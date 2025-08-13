<?php

namespace Agencia\Close\Middleware\Login;

use Agencia\Close\Middleware\Middleware;
use Agencia\Close\Services\Login\LoginSession;
use Agencia\Close\Services\Login\Logon;

class LoginCheckMiddleware extends Middleware
{
    
    public function run()
    {
        $loginSession = new LoginSession();
        if ( !$loginSession->userIsLogged() AND (strpos($this->getCurrentUrl(), 'login') === false) AND (strpos($this->getCurrentUrl(), 'api') === false) and (strpos($this->getCurrentUrl(), 'check-expiradas') === false)) {
           header('Location: '. DOMAIN .'/login');
        }
    }

    protected function getCurrentUrl(): string
    {
        return parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
    }

}