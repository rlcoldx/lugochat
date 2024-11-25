<?php
// PAGE SUITES WIDGET
$router->namespace("Agencia\Close\Controllers\SuitesWidget");
$router->post("/chat/suites/lista", "SuitesWidgetController:suites_lista");
$router->post("/chat/suites/detalhes", "SuitesWidgetController:suites_detalhes");
$router->post("/chat/suites/detalhes/horas", "SuitesWidgetController:listarHorasRestantesDoDia");
$router->post("/chat/suites/detalhes/periodos", "SuitesWidgetController:getperiodos");