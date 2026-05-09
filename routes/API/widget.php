<?php

// API pública do Widget (JSON) baseada no código (cc)
$router->namespace("Agencia\\Close\\Controllers\\Api");

$router->get("/api/widget/bootstrap", "WidgetApiController:bootstrap");
$router->get("/api/widget/suites", "WidgetApiController:suites");
$router->get("/api/widget/suite", "WidgetApiController:suiteDetalhes");

