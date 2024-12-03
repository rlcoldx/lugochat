<?php
// PAGE CUPONS
$router->namespace("Agencia\Close\Controllers\Cupons");
$router->get("/cupons", "CuponsController:index");
$router->get("/cupons/add", "CuponsController:criar");
$router->get("/cupons/edit/{id}", "CuponsController:editar");
$router->post("/cupons/save", "CuponsController:cupomSalvar");