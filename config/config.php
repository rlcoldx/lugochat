<?php

define('DOMAIN', 'http://localhost/lugochat');
define('PATH', 'http://localhost/lugochat');
define('NAME', 'LUGO CHAT');
define('PRODUCTION', FALSE);

//// CONFIGURAÇÕES DO BANCO ########################
define('HOST_MAIN', '177.234.145.178');
define('USER_MAIN', 'rafael_lugoapp');
define('PASS_MAIN', 'GKN7{dSx~tL}pO7UK1');
define('DBSA_MAIN', 'rafael_lugoapp');

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

// OneSignal — API (envio pelo servidor) sempre ativa com APP_ID + REST_API_KEY abaixo.
// Web Push no navegador: configure em onesignal.com → Settings → Platforms → Web
// Site URL = DOMAIN (ex.: http://localhost/lugochat). Depois mude WEB_ENABLED para true.
define('ONESIGNAL_APP_ID', '05596de6-efb1-4699-8019-66c627701617');
define('ONESIGNAL_SAFARI_WEB_ID', 'web.onesignal.auto.4ed285de-faf5-4c6c-a346-3ff91e5aded6');
define('ONESIGNAL_REST_API_KEY', 'os_v2_app_avmw3zxpwfdjtaazm3dco4awc4g7dfgo4csuoz5uky7y3q4vznurhdj73ewvtmv6tuggthhnokz7qrgjizkmcuvv7ohkxvu535qgwyq');
define('ONESIGNAL_WEB_ENABLED', true);
$_onesignalSitePath = parse_url(DOMAIN, PHP_URL_PATH);
define('ONESIGNAL_SITE_PATH', $_onesignalSitePath ? rtrim($_onesignalSitePath, '/') : '');

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