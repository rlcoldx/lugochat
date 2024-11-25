<?php
// PAGE SUITES
$router->namespace("Agencia\Close\Controllers\Suites");
$router->get("/suites", "SuitesController:index");
$router->get("/suites/new", "SuitesController:criar");
$router->get("/suites/edit/{id}", "SuitesController:editar");
$router->post("/suites/save_draft", "SuitesController:save_draft");
$router->post("/suites/editar/save", "SuitesController:save_edit");
$router->post("/suites/excluir", "SuitesController:excluir_suite");