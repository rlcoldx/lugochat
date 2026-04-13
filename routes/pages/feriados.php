<?php

$router->namespace("Agencia\Close\Controllers\Feriados");
$router->get("/feriados/sync-feriados", "FeriadosController:syncNager");
$router->get("/feriados/add", "FeriadosController:criar");
$router->get("/feriados/edit/{date}", "FeriadosController:editar");
$router->post("/feriados/save", "FeriadosController:salvar");
$router->post("/feriados/delete", "FeriadosController:excluir");
$router->get("/feriados", "FeriadosController:index");
