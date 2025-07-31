<?php
$router->namespace("Agencia\Close\Controllers\Rubens");
$router->get("/api/rubens/suites", "RubensController:suites");
$router->get("/api/rubens/suite/disponibilidade", "RubensController:disponibilidade");
$router->get("/api/rubens/suite/qtde_disp", "RubensController:qtde_disp");

// Rotas de teste para a integração Rubens
$router->get("/api/rubens/receber_reservas", "RubensController:receberReservas");
$router->get("/api/rubens/reserva/criar/teste", "RubensController:CriarReservaTeste");
$router->get("/api/rubens/reserva/ver", "RubensController:verReserva");
$router->get("/api/rubens/reserva/processado", "RubensController:reservaProcessado");
$router->get("/api/rubens/reserva/reserva_paga", "RubensController:reservaPaga");
$router->get("/api/rubens/reserva/cancelar", "RubensController:cancelarReserva");
$router->get("/api/rubens/reserva/nao_paga", "RubensController:naoPagarReserva");
$router->get("/api/rubens/reserva/checkin", "RubensController:confirmarCheckin");