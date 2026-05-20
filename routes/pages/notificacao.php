<?php
// PAINEL NOTIFICACAO
$router->namespace("Agencia\Close\Controllers\Notificacao");
$router->get("/notificacao/criar", "NotificacaoController:index");
$router->post("/notificacao/enviar", "NotificacaoController:enviarNotificacao");
$router->post("/notificacao/salvar-push", "NotificacaoController:salvarPush");
$router->post("/notificacao/remover-push", "NotificacaoController:removerPush");
$router->post("/notificacao/testar-push-reserva", "NotificacaoController:testarPushUltimaReservaPaga");
