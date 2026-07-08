<?php
// PAGE CLIENTES
$router->namespace("Agencia\Close\Controllers\Clientes");
$router->get("/clientes", "ClientesController:index");
$router->post("/clientes/banir", "ClientesController:banir");
$router->post("/clientes/desbanir", "ClientesController:desbanir");