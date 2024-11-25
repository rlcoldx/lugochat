<?php
// INICIA O CHAT
$router->namespace("Agencia\Close\Controllers\Widget");
$router->get("/widget", "WidgetController:index");
$router->post("/chat/historico", "WidgetController:historico");
$router->post("/chat/historico/save", "WidgetController:saveHistorico");