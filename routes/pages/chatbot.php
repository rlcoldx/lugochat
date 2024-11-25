<?php
// PAGE BOT
$router->namespace("Agencia\Close\Controllers\Bot");
$router->get("/chatbot", "BotController:index");
$router->post("/chatbot/save/bem_vindo", "BotController:saveBemVindo");
$router->get("/chatbot", "BotController:index");
$router->post("/chatbot/save/question/edit", "BotController:editQuestion");
// PAGE BOT OPTION
$router->get("/chatbot/options/new/{pergunta}", "BotController:newOptionsOpen");
$router->get("/chatbot/options/edit/{pergunta}/{opcional}", "BotController:editOptionsOpen");