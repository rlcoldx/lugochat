<?php

use CoffeeCode\Router\Router;

$router = new Router(DOMAIN);

// INICIA O CHAT
$router->namespace("Agencia\Close\Controllers\Widget");
$router->get("/widget", "WidgetController:index");
$router->post("/chat/historico", "WidgetController:historico");
$router->post("/chat/historico/save", "WidgetController:saveHistorico");

// PAGE HOME
$router->namespace("Agencia\Close\Controllers\Home");
$router->get("/", "HomeController:index");
$router->get("/tempermissao", "HomeController:tempermissao");
$router->get("/sempermissao", "HomeController:sempermissao");
$router->get("/agendamentos/check", "HomeController:check_agendamentos");

// LOGIN
$router->namespace("Agencia\Close\Controllers\Login");
$router->get("/login", "LoginController:index");
$router->get("/login/recover", "LoginController:recover");
$router->post("/login/sign", "LoginController:sign");
$router->get("/login/logout", "LoginController:logout");

// PAGE EQUIPE
$router->namespace("Agencia\Close\Controllers\Equipe");
$router->get("/equipe", "EquipeController:index");
$router->get("/equipe/add", "EquipeController:criar");
$router->get("/equipe/edit/{id}", "EquipeController:editar");
$router->post("/equipe/add/save", "EquipeController:criarSalvar");
$router->post("/equipe/edit/save", "EquipeController:editarSalvar");

// PAGE EQUIPE CARGOS
$router->namespace("Agencia\Close\Controllers\Cargos");
$router->get("/cargos", "CargosController:index");
$router->get("/cargos/edit/{id}", "CargosController:editar");
$router->post("/cargos/add/save", "CargosController:criarSalvar");
$router->post("/cargos/edit/save", "CargosController:editarSalvar");

// PAGE BOT
$router->namespace("Agencia\Close\Controllers\Bot");
$router->get("/chatbot", "BotController:index");
$router->post("/chatbot/save/bem_vindo", "BotController:saveBemVindo");
$router->get("/chatbot", "BotController:index");
$router->post("/chatbot/save/question/edit", "BotController:editQuestion");
// PAGE BOT OPTION
$router->get("/chatbot/options/new/{pergunta}", "BotController:newOptionsOpen");
$router->get("/chatbot/options/edit/{pergunta}/{opcional}", "BotController:editOptionsOpen");

// PAGE SUITES
$router->namespace("Agencia\Close\Controllers\Suites");
$router->get("/suites", "SuitesController:index");
$router->get("/suites/new", "SuitesController:criar");
$router->get("/suites/edit/{id}", "SuitesController:editar");
$router->post("/suites/save_draft", "SuitesController:save_draft");
$router->post("/suites/editar/save", "SuitesController:save_edit");
$router->post("/suites/excluir", "SuitesController:excluir_suite");

// PAGE SUITES WIDGET
$router->namespace("Agencia\Close\Controllers\SuitesWidget");
$router->post("/chat/suites/lista", "SuitesWidgetController:suites_lista");
$router->post("/chat/suites/detalhes", "SuitesWidgetController:suites_detalhes");
$router->post("/chat/suites/detalhes/horas", "SuitesWidgetController:listarHorasRestantesDoDia");
$router->post("/chat/suites/detalhes/periodos", "SuitesWidgetController:getperiodos");

// AGENDAMENTO E PAGAMENTO
$router->namespace("Agencia\Close\Controllers\Agendamento");
$router->post("/chatbot/save/agendamento", "AgendamentoController:saveAgendamentos");
$router->post("/chat/suite/agendamento/espera", "AgendamentoController:getAgendamentoEspera");

// ERROR
$router->group("error")->namespace("Agencia\Close\Controllers\Error");
$router->get("/{errorCode}", "ErrorController:show", 'error');

$router->dispatch();
if ($router->error()) {
    echo "Página não encontrada.";
}