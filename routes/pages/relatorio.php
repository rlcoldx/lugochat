<?php
// RELATÓRIO DE RESERVAS (ADMIN)
$router->namespace("Agencia\Close\Controllers\Relatorio");
$router->get("/admin/relatorio/reservas", "RelatorioReservasController:index", "admin_relatorio_reservas");
