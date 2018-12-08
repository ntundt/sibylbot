<?php

	require_once 'WebInterface.php';

	define('DATABASE_LOGIN', 'your-database-login');
	define('DATABASE_PASSWORD', 'your-database-password');
	define('DATABASE_HOST', 'localhost'); //your database host
	define('ACCESS_TOKEN', 'replace-me-with-real-vk-community-access-token');

	$wi = new WebInterface($_POST);
	
	if(isset($_POST['command'])) {
		$wi->perform();
	
		if(!$wi->errno) {
			$error = array(
				'type' => $wi->error['type'],
				'obj' => isset($wi->error['vkobject'])?$wi->error['vkobject']:$wi->error['sqlerror']
			);
		}
	}

	include 'markup/webinterface.phtml';

?>