<?php
$router->namespace("Agencia\Close\Controllers\ApiIntegracao");
$router->get("/api/integracao/list", "ApiIntegracaoController:index");
$router->get("/api/integracao/add", "ApiIntegracaoController:criar");
$router->get("/api/integracao/edit/{id}", "ApiIntegracaoController:editar");
$router->post("/api/integracao/salvar", "ApiIntegracaoController:salvar");
$router->post("/api/integracao/deletar/{id}", "ApiIntegracaoController:deletar");

