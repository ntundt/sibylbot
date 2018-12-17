<?php 

	require_once 'config.php';

	if(isset($_GET['code'])) {
		$parameters = array(
			'client_id' => APP_ID, 
			'client_secret' => CLIENT_SECRET, 
			'redirect_uri' => HOST.'/oauth.php',
			'code' => $_GET['code']
		);
		$curl = curl_init();
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	    curl_setopt($curl, CURLOPT_URL, 'https://oauth.vk.com/access_token?'.http_build_query($parameters));
	    $response = curl_exec($curl);
	    curl_close($curl);
	    $response = json_decode($response, true);

		setcookie(
			'access_token', 
			$response['access_token'], 
			($response['expires_in'] == 0) ? time() + 60 * 60 * 24 * 31:$response['expires_in']
		);
		setcookie(
			'user_id', 
			$response['user_id'], 
			($response['expires_in'] == 0) ? time() + 60 * 60 * 24 * 31:$response['expires_in']
		);
	}

	if(isset($_GET['state'])) {
		switch($_GET['state']) {
			case 'sevilweblogin':
				header('Location: '.HOST.'/sevilla/index.php', true, 301);
				break;
			default:
				header('Location: '.HOST.'/index.html', true, 301);
				break;
		}
	} else {
		header('Location: '.HOST.'/index.html', true, 301);
	}