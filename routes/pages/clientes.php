<?php
// PAGE CLIENTES
$router->namespace("Agencia\Close\Controllers\Clientes");
$router->get("/clientes", "ClientesController:index");