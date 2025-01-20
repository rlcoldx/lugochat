<?php
// PAGE EQUIPE
$router->namespace("Agencia\Close\Controllers\Moteis");
$router->get("/moteis", "MoteisController:index");
$router->get("/moteis/add", "MoteisController:criar");
$router->get("/moteis/edit/{id}", "MoteisController:editar");
$router->post("/moteis/add/save", "MoteisController:criarSalvar");
$router->post("/moteis/edit/save", "MoteisController:editarSalvar");


// PAGE CATEGORIAS
$router->namespace("Agencia\Close\Controllers\Moteis");
$router->get("/moteis/categorias", "CategoriasController:index");
$router->get("/moteis/categorias/editar/{id}", "CategoriasController:editar");
$router->post("/moteis/categorias/save", "CategoriasController:save");
$router->post("/moteis/categorias/save_edit", "CategoriasController:save_edit");