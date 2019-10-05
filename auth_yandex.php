<?php
function getEmail($token){
	$url = 'https://login.yandex.ru/info?format=json&oauth_token='.$token;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$res = curl_exec($ch);
	curl_close($ch);
	
	$email = false;
	
	if($res){
		$resData = json_decode($res,true);
		$email = $resData['default_email'];
	}
	
	return $email;
}

$client_app = ''; //ид приложения
$client_url = 'https://domain.com/auth_yandex.php'; //путь к скрипту

if(isset($_GET['response_type'],$_GET['client_id'],$_GET['state']) && ($_GET['response_type'] == 'code') && ($client_app == $_GET['client_id'])){
	$url = 'https://oauth.yandex.ru/authorize?response_type=token&client_id='.$client_app.'&redirect_uri='.urlencode($client_url).'&state='.$_GET['state'];
	header('Location: '.$url);
}elseif(isset($_GET['token'],$_GET['state']) && $_GET['token']){
	$url = 'https://social.yandex.net/broker/redirect?client_id='.$client_app.'&response_type=code&code='.$_GET['token'].'&state='.$_GET['state'];
	header('Location: '.$url);
}elseif(isset($_POST['code']) && $_POST['code']){
	header('Content-Type: application/json; charset=utf-8');
	/*{
       "access_token":"2YotnFZFEjr1zCsicMWpAA",
       "token_type":"example",
       "expires_in":3600,
       "refresh_token":"tGzv3JOkF0XG5Qx2TlKWIA",
       "example_parameter":"example_value"
     }
	*/
	$email = getEmail($_POST['code']);
	
	if(!$email){
		$data = array(
			'error' => 'access_denied'
		);
	}else{
		$data = array(
			'access_token' => base64_encode($_POST['code'].'//'.$email)
		);
	}
	
	echo json_encode($data);
	
}else{
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
<meta id="myViewport" name="viewport" content="initial-scale=1">
</head>
<body>
	<script>
	var token = /access_token=([^&]+)/.exec(document.location.hash)[1];
	var state = /state=([^&]+)/.exec(document.location.hash)[1];
	if(token && state){
		var url = '<?echo $client_url?>?token='+token+'&state='+state;
		document.location.href = url;
		var div = document.createElement('div');
		div.className = 'redirect';
		div.innerHTML = '<a href="'+url+'">нажмите если не работает перенаправление</a>';
		document.body.append(div);
	}
	</script>
</body>
</html>
<?
}