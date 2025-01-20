<?php
// PAINEL NOTIFICACAO
$router->namespace("Agencia\Close\Controllers\Notificacao");
$router->get("/notificacao/criar", "NotificacaoController:index");
$router->post("/notificacao/enviar", "NotificacaoController:enviarNotificacao");
