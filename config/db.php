<?php
	@session_start();
	try {
		$db = new PDO('mysql:host=177.234.145.178;dbname=rafael_lugochat', 'rafael_lugochat', 'wU$S2Fam]N~{V4Ffia');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		if($e->getCode() == 1049){
			echo "Banco de dados errado.";
		}else{
			echo $e->getMessage();
		}
	}

    define('DOMAIN', 'http://localhost/lugochat');
    define('PATH', 'http://localhost/lugochat');
    define('NAME', 'Lugo Chat');
    define('PRODUCTION', false);