<?php
// PAGE RESTAURANTES
$router->namespace("Agencia\Close\Controllers\Paginas");
$router->get("/paginas", "PaginasController:index");
$router->get("/paginas/new", "PaginasController:criar");
$router->get("/paginas/edit/{id}", "PaginasController:editar");
$router->post("/paginas/save_draft", "PaginasController:save_draft");
$router->post("/paginas/editar/save", "PaginasController:save_edit");
$router->post("/paginas/excluir", "PaginasController:excluir_restaurante");

$router->get("/paginas/banners", "PaginasController:banners");