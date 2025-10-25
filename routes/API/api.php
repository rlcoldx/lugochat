<?php
$router->namespace("Agencia\Close\Controllers\Api");
$router->get("/api/integracao/suites", "ApiController:suites");
$router->get("/api/integracao/suite/disponibilidade", "ApiController:disponibilidade");
$router->get("/api/integracao/suite/qtde_disp", "ApiController:qtde_disp");

// Rotas de teste para a integração API
$router->get("/api/integracao/receber_reservas", "ApiController:receberReservas");
$router->get("/api/integracao/reserva/criar/teste", "ApiController:CriarReservaTeste");
$router->get("/api/integracao/reserva/ver", "ApiController:verReserva");
$router->get("/api/integracao/reserva/processado", "ApiController:reservaProcessado");
$router->get("/api/integracao/reserva/reserva_paga", "ApiController:reservaPaga");
$router->get("/api/integracao/reserva/cancelar", "ApiController:cancelarReserva");
$router->get("/api/integracao/reserva/nao_paga", "ApiController:naoPagarReserva");
$router->get("/api/integracao/reserva/checkin", "ApiController:confirmarCheckin");

