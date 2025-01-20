<?php
// PAGE CONFIG
$router->namespace("Agencia\Close\Controllers\Config");
$router->get("/configuracoes", "ConfigController:index");
$router->post("/configuracoes/save", "ConfigController:save");