<?php
	include('../../../config/db.php');

	if (isset($_POST['file'])) {

		$sql_item = $db->prepare("SELECT * FROM admin_banners WHERE `local` = '".$_GET['id']."' AND nome = '".$_POST['file']."'");
		$sql_item->execute();
		$item = $sql_item->fetch(PDO::FETCH_ASSOC);

		$diretorio = '../../../uploads/admin_banners/';

		$file = $diretorio.$item['nome'];
		
		if(file_exists($file))
			unlink($file);

		$sql = $db->prepare("DELETE FROM `admin_banners` WHERE `local` = '".$_GET['id']."' AND id = '".$item['id']."'");
		$sql->execute();

	}

	echo $file;