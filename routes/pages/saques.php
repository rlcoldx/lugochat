<?php
// PAINEL SAQUES
$router->namespace("Agencia\Close\Controllers\Saques");
$router->get("/saques", "SaquesController:index");
$router->get("/saques/conta/add-saque", "SaquesController:SaqueModal");
$router->post("/saques/conta/check", "SaquesController:CarteiraCheck");
$router->post("/saques/conta/save-saque", "SaquesController:SaveSaque");

$router->get("/saques/conta/criar", "SaquesController:ContaCriar");
$router->get("/saques/conta/editar/{id}", "SaquesController:ContaEditar");
$router->post("/saques/conta/salvar", "SaquesController:ContaSalvar");

    
//SAQUES ADMIN
$router->namespace("Agencia\Close\Controllers\SaquesAdmin");
$router->get("/admin/saques", "SaquesAdminController:index", "index");
$router->post("/admin/saques/statusSave", "SaquesAdminController:statusSave", "statusSave");