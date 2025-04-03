<?php
$router->namespace("Agencia\Close\Controllers\Sis");
$router->get("/api/sis/disponibilidade", "SisController:disponibilidade");
$router->get("/api/sis/motel/disponibilidade/{motel}", "SisController:disponibilidadeMotel");
