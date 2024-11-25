<?php
// PAGE EQUIPE
$router->namespace("Agencia\Close\Controllers\Moteis");
$router->get("/moteis", "MoteisController:index");
$router->get("/moteis/add", "MoteisController:criar");
$router->get("/moteis/edit/{id}", "MoteisController:editar");
$router->post("/moteis/add/save", "MoteisController:criarSalvar");
$router->post("/moteis/edit/save", "MoteisController:editarSalvar");