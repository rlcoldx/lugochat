<?php
// PAGE EQUIPE
$router->namespace("Agencia\Close\Controllers\Equipe");
$router->get("/equipe", "EquipeController:index");
$router->get("/equipe/add", "EquipeController:criar");
$router->get("/equipe/edit/{id}", "EquipeController:editar");
$router->post("/equipe/add/save", "EquipeController:criarSalvar");
$router->post("/equipe/edit/save", "EquipeController:editarSalvar");