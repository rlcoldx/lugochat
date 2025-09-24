<?php

namespace Agencia\Close\Middleware;

use Agencia\Close\Middleware\Login\LoginMiddleware;
use Agencia\Close\Middleware\Login\LoginCheckMiddleware;
use Agencia\Close\Middleware\Login\UserPermissionMiddleware;
use Agencia\Close\Middleware\LoadPermissionsMiddleware;

class MiddlewareCollection
{
    private array $middlewares = [];

    public function default()
    {
        $this->push(new LoginMiddleware());
        $this->push(new LoginCheckMiddleware());
        $this->push(new UserPermissionMiddleware());
        $this->push(new LoadPermissionsMiddleware());
    }

    public function run()
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->run();
        }
    }

    private function push(Middleware $middleware)
    {
        array_push($this->middlewares, $middleware);
    }
}