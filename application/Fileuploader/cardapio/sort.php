<?php
	include('../../../config/db.php');

    $list = isset($_POST['_list']) ? json_decode($_POST['_list'], true) : null;
   
	foreach ($list as $key => $item){

		$sql = $db->prepare("UPDATE `cardapios` SET `order` = '".$item['index']."' WHERE nome = '".$item['name']."' AND id_motel = '".$_SESSION['busca_perfil_empresa']."'");
		$sql->execute();
		
	}