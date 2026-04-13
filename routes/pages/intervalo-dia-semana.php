<?php

$router->namespace("Agencia\Close\Controllers\IntervaloDiaSemana");
$router->get("/intervalos-dia-semana/add", "IntervaloDiaSemanaController:criar");
$router->get("/intervalos-dia-semana/edit/{id}", "IntervaloDiaSemanaController:editar");
$router->post("/intervalos-dia-semana/save", "IntervaloDiaSemanaController:salvar");
$router->post("/intervalos-dia-semana/delete", "IntervaloDiaSemanaController:excluir");
$router->get("/intervalos-dia-semana", "IntervaloDiaSemanaController:index");
