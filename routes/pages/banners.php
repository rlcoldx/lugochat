<?php

// PAGE BANNERS
$router->namespace("Agencia\Close\Controllers\Banners");
$router->get("/banners", "BannersController:index", "banners");
$router->get("/banners/{id}", "BannersController:banners", "banners_id");
$router->post("/banners/link", "BannersController:link", "banners_link");
$router->post("/banners/link/save", "BannersController:linkSave", "banners_link_save");