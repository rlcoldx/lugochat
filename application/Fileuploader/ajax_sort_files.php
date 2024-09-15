<?php
	include('../../config/db.php');

    $list = isset($_POST['_list']) ? json_decode($_POST['_list'], true) : null;
   
	foreach ($list as $key => $item){

		$sql = $db->prepare("UPDATE `suites_imagens` SET `order` = '".$item['index']."' WHERE `id_suite` = '".$_GET['id_suite']."' AND nome = '".$item['name']."'");
		$sql->execute();
		
	}