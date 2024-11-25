<?php
// PAGE HOME
$router->namespace("Agencia\Close\Controllers\Home");
$router->get("/", "HomeController:index");
$router->get("/tempermissao", "HomeController:tempermissao");
$router->get("/sempermissao", "HomeController:sempermissao");
$router->get("/agendamentos/check", "HomeController:check_agendamentos");
$router->post("/change-motel", "HomeController:changeMotel");