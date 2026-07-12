<?php

// API do aplicativo Desktop
$router->namespace('Agencia\Close\Controllers\Api');

$router->post('/api/desktop/login', 'DesktopApiController:login');
