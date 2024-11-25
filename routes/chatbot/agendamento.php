<?php
// AGENDAMENTO E PAGAMENTO
$router->namespace("Agencia\Close\Controllers\Agendamento");
$router->post("/chatbot/save/agendamento", "AgendamentoController:saveAgendamentos");
$router->post("/chat/suite/agendamento/espera", "AgendamentoController:getAgendamentoEspera");
