<?php
$router->namespace("Agencia\Close\Controllers\Rubens");
$router->get("/api/rubens/suites", "RubensController:suites");
$router->get("/api/rubens/suite/disponibilidade", "RubensController:disponibilidade");