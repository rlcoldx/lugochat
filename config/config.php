<?php

define('DOMAIN', 'http://localhost/lugochat');
define('PATH', 'http://localhost/lugochat');
define('NAME', 'LUGO CHAT');
define('PRODUCTION', FALSE);

//// CONFIGURAÇÕES DO BANCO ########################
define('HOST_MAIN', '177.234.145.178');
define('USER_MAIN', 'rafael_lugochat');
define('PASS_MAIN', 'wU$S2Fam]N~{V4Ffia');
define('DBSA_MAIN', 'rafael_lugochat');

// CONFIGURAÇÕES DO EMAIL ########################
define('MAIL_ADMIN', 'rl.cold.dev@gmail.com');
define('MAIL_HOST', 'smtp.uni5.net');
define('MAIL_EMAIL', 'no_replay@buscademoteis.com.br');
define('MAIL_USER', 'no_replay@buscademoteis.com.br');
define('MAIL_PASSWORD', 'senha$Total2026');
define('MAIL_PORT', 587);

// SISTEMA SIS
define('SIS_API', 'https://api.sismotel.com.br');
define('SOFTHOUSE', 'c283ecc655bf074fc99ff95f1d51dc6c-721b224025ef117caabeaf76d58f3d50');

define('TOKEN', 'qgvCMRoHCTujhHswhiTyRALSY8QzRQyvlB0PWPqmuUpELHDf5vjirY04JQsaj3xx');
define('ACCESSTOKEN', 'APP_USR-7344792450155289-042218-098d04ea1b43278887ef425218689d8f-270137611');

@session_start();
try {
	$db = new PDO('mysql:host='.HOST_MAIN.';dbname='.USER_MAIN, USER_MAIN, PASS_MAIN);
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	if($e->getCode() == 1049){
		echo "Banco de dados errado.";
	}else{
		echo $e->getMessage();
	}
}
include('functions_app.php');