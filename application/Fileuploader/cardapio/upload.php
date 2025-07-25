<?php
	include('../../../config/db.php');
	include('../class.fileuploader.php');
	require '../vendor/autoload.php';

	$isAfterEditing = false;

	if (!file_exists('../../../uploads/cardapios/')) {
		mkdir('../../../uploads/cardapios/', 0777, true);
	}

	$caminho = '../../../uploads/cardapios/';

	if (isset($_POST['fileuploader']) && isset($_POST['_editingg'])) {
		$isAfterEditing = true;
		$nome     		= $_POST['_namee'];
	}else{
		$extensao 		= pathinfo($_POST['_namee'], PATHINFO_EXTENSION);
		$nome     		= md5(microtime($_POST['_namee'])).'.jpg';
	}

	$FileUploader = new FileUploader('files', array(
		'limit' => null,
		'maxSize' => null,
		'fileMaxSize' => null,
		'extensions' => null,
		'required' => false,
		'uploadDir' => $caminho,
		'title' => ''.$nome.'',
		'replace' => $isAfterEditing,
		'listInput' => true,
		'files' => null
	));

	if (isset($_POST['fileuploader']) && isset($_POST['_editingg'])) {}else{

		$foto_completa	= DOMAIN.'/uploads/cardapios/'.strtolower($nome);
		$dados = array($_SESSION['busca_perfil_empresa'], $foto_completa, $nome, $_POST['_namee']);
		$sql = $db->prepare("INSERT INTO cardapios (id_motel, imagem, nome, original) VALUES (?,?,?,?)");
		$sql->execute($dados);

	}

	$upload = $FileUploader->upload();

	echo json_encode($upload);

	exit;