<?php

$router->namespace("Agencia\Close\Controllers\MotelFechamentos");
$router->get("/admin/motel-fechamentos", "MotelFechamentosController:index");
$router->get("/admin/motel-fechamentos/add", "MotelFechamentosController:criar");
$router->get("/admin/motel-fechamentos/edit/{id_grupo}", "MotelFechamentosController:editar");
$router->get("/admin/motel-fechamentos/moteis-por-proprietario", "MotelFechamentosController:moteisPorProprietario");
$router->post("/admin/motel-fechamentos/save", "MotelFechamentosController:salvar");
$router->post("/admin/motel-fechamentos/delete", "MotelFechamentosController:excluir");
