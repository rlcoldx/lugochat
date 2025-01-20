<?php
// PAGE AGENDAMENTOS
$router->namespace("Agencia\Close\Controllers\Reserva");
$router->get("/reservas", "ReservaController:get_reservas");
$router->get("/reserva/{id}", "ReservaController:get_reserva_id");
$router->get("/reservas/check", "ReservaController:check_reservas");
$router->get("/reservas/status/{id}", "ReservaController:status_reserva");
$router->post("/reservas/status/save", "ReservaController:status_reserva_save");
