<?php
// PAGE EQUIPE CARGOS
$router->namespace("Agencia\Close\Controllers\Cargos");
$router->get("/cargos", "CargosController:index");
$router->get("/cargos/edit/{id}", "CargosController:editar");
$router->post("/cargos/add/save", "CargosController:criarSalvar");
$router->post("/cargos/edit/save", "CargosController:editarSalvar");