<?php
	include('../../config/db.php');
	include('class.fileuploader.php');
	require 'vendor/autoload.php';

	$sql_usuario = $db->prepare("SELECT * FROM usuarios WHERE empresa = '".$_SESSION['lugo_perfil_empresa']."' LIMIT 1");
	$sql_usuario->execute();
	$usuario = $sql_usuario->fetch(PDO::FETCH_ASSOC);

	$isAfterEditing = false;

	$sql_imagem = $db->prepare("SELECT * FROM suites_imagens WHERE nome = '".$_POST['_namee']."'");
	$sql_imagem->execute();
	$imagem = $sql_imagem->fetch(PDO::FETCH_ASSOC);

	if (isset($_POST['fileuploader']) && isset($_POST['_editingg'])) {
		$data = explode('-', $imagem['data']);
		$mes = $data[1];
		$ano = $data[0];
	}else{
		$mes = date('m');
		$ano = date('Y');
	}

	if (!file_exists('../../uploads/suites/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/')) {
		mkdir('../../uploads/suites/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/', 0777, true);
	}

	$caminho = '../../uploads/suites/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/';

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
		'editor' => array(
            'maxWidth' => 1000,
            'maxHeight' => 665,
            'crop' => true,
            'quality' => null
		),
		'listInput' => true,
		'files' => null
	));

	if (isset($_POST['fileuploader']) && isset($_POST['_editingg'])) {}else{

		$foto_completa	= DOMAIN.'/uploads/suites/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/'.strtolower($nome);
		$dados = array($usuario['id'], $_GET['id_suite'], $foto_completa, $nome, $_POST['_namee']);
		$sql = $db->prepare("INSERT INTO suites_imagens (id_empresa, id_suite, imagem, nome, original) VALUES (?,?,?,?,?)");
		$sql->execute($dados);

	}

	$upload = $FileUploader->upload();

	if($upload){
	
			//THUMBNAIL
			$sql_imagens = $db->prepare("SELECT * FROM suites_imagens ORDER BY id DESC LIMIT 1");
			$sql_imagens->execute();
			$dados_imagens = $sql_imagens->fetch(PDO::FETCH_ASSOC);

			$dados_imagens['imagem'] = str_replace(DOMAIN."/","../../",$dados_imagens['imagem']);
			$nome = explode('.', $dados_imagens['nome']);

			if (!file_exists('../../uploads/suites_thumbnail/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/')) {
				mkdir('../../uploads/suites_thumbnail/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/', 0777, true);
			}

			// create a new instance of the class
			$image = new Zebra_Image();
			$image->auto_handle_exif_orientation = false;

			$image->source_path = '../../uploads/suites/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/'.strtolower($dados_imagens['nome']);

			$image->target_path = '../../uploads/suites_thumbnail/'.$usuario['slug'].'/'.$ano.'/'.$mes.'/'.$nome[0].'.jpg';

			$imagem_thumbnail = DOMAIN.'/'.substr($image->target_path, 12);
			$sql_update = $db->prepare("UPDATE `suites_imagens` SET `thumbnail` = '".$imagem_thumbnail."' WHERE `id` = '".$dados_imagens['id']."'");
			$sql_update->execute();

			$image->jpeg_quality = 20;
			$image->preserve_aspect_ratio = true;
			$image->enlarge_smaller_images = true;
			$image->preserve_time = true;
			$image->handle_exif_orientation_tag = true;

			// resize the image to exactly 100x100 pixels by using the "crop from center" method
			if (!$image->resize(400, 400, ZEBRA_IMAGE_CROP_CENTER)) {};
		
	}

	echo json_encode($upload);

	exit;