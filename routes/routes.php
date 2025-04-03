<?php

use CoffeeCode\Router\Router;

$router = new Router(DOMAIN);

//PAGINAS
require  __DIR__ . '/pages/login.php';
require  __DIR__ . '/pages/home.php';
require  __DIR__ . '/pages/moteis.php';
require  __DIR__ . '/pages/suites.php';
require  __DIR__ . '/pages/equipe.php';
require  __DIR__ . '/pages/cargos.php';
require  __DIR__ . '/pages/chatbot.php';
require  __DIR__ . '/pages/cupons.php';
require  __DIR__ . '/pages/saques.php';
require  __DIR__ . '/pages/clientes.php';
require  __DIR__ . '/pages/config.php';
require  __DIR__ . '/pages/reservas.php';
require  __DIR__ . '/pages/notificacao.php';

//WIDGET
require  __DIR__ . '/chatbot/inicio.php';
require  __DIR__ . '/chatbot/suites.php';
require  __DIR__ . '/chatbot/agendamento.php';

//API
require  __DIR__ . '/API/rubens.php';
require  __DIR__ . '/API/sis.php';

// ERROR
$router->group("error")->namespace("Agencia\Close\Controllers\Error");
$router->get("/{errorCode}", "ErrorController:show", 'error');

$router->dispatch();
if ($router->error()) {
    echo "Página não encontrada.";
}