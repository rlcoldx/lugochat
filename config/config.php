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

// SISTEMA SIS
define('SIS_API', 'http://177.156.7.7:55005');
define('SOFTHOUSE', 'e7ff638fe581513d36f9743936d83a90-8e6702fcc16d42059686a50ce429b52a');

define('TOKEN', 'qgvCMRoHCTujhHswhiTyRALSY8QzRQyvlB0PWPqmuUpELHDf5vjirY04JQsaj3xx');
define('ACCESSTOKEN', 'APP_USR-6619019503196756-111110-960e8da08d80ad993e73d4b3d423bd9f-263421911');

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