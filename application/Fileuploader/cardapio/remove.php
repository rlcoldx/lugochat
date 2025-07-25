<?php
	include('../../../config/db.php');

	if (isset($_POST['file'])) {

		$sql_item = $db->prepare("SELECT * FROM cardapios WHERE nome = '".$_POST['file']."' AND id_motel = '".$_SESSION['busca_perfil_empresa']."'");
		$sql_item->execute();
		$item = $sql_item->fetch(PDO::FETCH_ASSOC);
		
		$caminho = explode('-', $item['data']);

		$diretorio = '../../../uploads/cardapios/';

		$file = $diretorio.$item['nome'];
		
		if(file_exists($file))
			unlink($file);

		$sql = $db->prepare("DELETE FROM `cardapios` WHERE id = '".$item['id']."' AND id_motel = '".$_SESSION['busca_perfil_empresa']."'");
		$sql->execute();

	}

	echo $file;