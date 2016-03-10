<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
require 'index.php';
$fb = new Facebook\Facebook([
  'app_id' => '150494718639750',
  'app_secret' => 'ce13013f03d23d5c354d399a7e7a2138',
  'default_graph_version' => 'v2.5',
  ]);
$helper = $fb->getCanvasHelper();
$permissions = ['email', 'publish_actions']; // optional
try {
	if (isset($_SESSION['facebook_access_token'])) {
	$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }
if (isset($accessToken)) {
	if (isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
		$_SESSION['facebook_access_token'] = (string) $accessToken;
	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
	
	// validating the access token
	try {
		$request = $fb->get('/me');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		if ($e->getCode() == 190) {
			unset($_SESSION['facebook_access_token']);
			$helper = $fb->getRedirectLoginHelper();
			$loginUrl = $helper->getLoginUrl('http://sosyalrozet.anymaa.com/facebook/', $permissions);
			echo "<script>window.top.location.href='".$loginUrl."'</script>";
			exit;
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	try {
		// message must come from the user-end
		//mesaj yerine rozetin açıklaması eklenebilir.
		//veritabanına bağlanıp kullanıcının rozetinin ne olduğu alınır. ona göre yine veri tabanında tutulan ilgili sınıfın mesajı yazdırılır.
		$mesaj='Rozetiniz :Siyasi, Bu rozet Facebook da siyasi olarak nitelendirilen paylaşımlarda bulunduğunuzu gösterir';
		//mesajdeğişkeni içine rozetin veritabından alınan metni yerleştirilir.
		$data = ['source' => $fb->fileToUpload(__DIR__.'/siyasi.png'), 'message' =>$mesaj ];
		$request = $fb->post('/me/photos', $data);
		$response = $request->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	//echo $response['id'];

  	// Now you can redirect to another page and use the
  	// access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper();
	$loginUrl = $helper->getLoginUrl('http://sosyalrozet.anymaa.com/facebook/', $permissions);
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}
