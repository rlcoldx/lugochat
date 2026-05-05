<?php

namespace Agencia\Close\Middleware\Login;

use Agencia\Close\Middleware\Middleware;
use Agencia\Close\Services\Login\LoginSession;
use Agencia\Close\Services\Login\Logon;

class LoginCheckMiddleware extends Middleware
{
    /**
     * Rotas e recursos estáticos acessíveis sem login (ex.: widget embutido em sites de motéis).
     */
    public function run()
    {
        $loginSession = new LoginSession();
        if ($loginSession->userIsLogged()) {
            return;
        }

        $path = $this->getCurrentUrl();
        if ($this->isPublicPath($path)) {
            return;
        }

        header('Location: ' . DOMAIN . '/login');
    }

    private function isPublicPath(string $path): bool
    {
        $needles = [
            'login',
            'api',
            'check-expiradas',
            '/widget',
            '/chat/',
            '/chatbot/',
            '/view/assets/',
            '/view/widget/',
        ];

        foreach ($needles as $needle) {
            if (strpos($path, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function getCurrentUrl(): string
    {
        return parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", PHP_URL_PATH);
    }

}