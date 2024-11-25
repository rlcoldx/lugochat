<?php
	include('../../config/db.php');

	if (isset($_POST['file'])) {

		$sql_item = $db->prepare("SELECT * FROM suites_imagens WHERE `id_suite` = '".$_GET['id_suite']."' AND nome = '".$_POST['file']."'");
		$sql_item->execute();
		$item = $sql_item->fetch(PDO::FETCH_ASSOC);

		$sql_usuario = $db->prepare("SELECT * FROM usuarios WHERE id = '".$_SESSION['busca_perfil_empresa']."' LIMIT 1");
		$sql_usuario->execute();
		$usuario = $sql_usuario->fetch(PDO::FETCH_ASSOC);

		$caminho = explode('-', $item['data']);
		$nome_arquivo_thumb = explode('.', $item['nome']);

		$diretorio = '../../uploads/suites/'.$usuario['slug'].'/'.$caminho[0].'/'.$caminho[1].'/';
		$diretorio_thumbnail = '../../uploads/suites_thumbnail/'.$usuario['slug'].'/'.$caminho[0].'/'.$caminho[1].'/';

		$file = $diretorio.$item['nome'];
		$file_thumbnail = $diretorio_thumbnail.$nome_arquivo_thumb[0].'.jpg';
		
		if(file_exists($file))
			unlink($file);
			unlink($file_thumbnail);

		$sql = $db->prepare("DELETE FROM `suites_imagens` WHERE `id_suite` = '".$_GET['id_suite']."' AND id = '".$item['id']."'");
		$sql->execute();

	}

	echo $file;