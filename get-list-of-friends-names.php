<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
//require 'index.php';
header('Content-Type: text/html; charset=ISO-8859-9');

$fb = new Facebook\Facebook([
  'app_id' => '150494718639750',
  'app_secret' => 'ce13013f03d23d5c354d399a7e7a2138',
  'default_graph_version' => 'v2.4',
]);

$helper = $fb->getCanvasHelper();

$permissions = ['user_friends']; // optionnal

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
			$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/smratingheroku/', $permissions);
			echo "<script>window.top.location.href='".$loginUrl."'</script>";
		}
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	// get list of friends' names
	//id,name,first_name,picture.width(120).height(120)' DENE???
	//friends?fields=id,name,picture.width(120)&limit=100
	try {
		//taggable_friends olarak değiştirilecek şimdilik friends
		$requestFriends = $fb->get('/me/friends?fields=id,name,picture.width(100)');
		$friends = $requestFriends->getGraphEdge();
	    
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
//print_r($friends);

	// if have more friends than 100 as we defined the limit above on line no. 68
	if ($fb->next($friends)) {
		$allFriends = array();
		$friendsArray = $friends->asArray();
		$allFriends = array_merge($friendsArray, $allFriends);
		while ($friends = $fb->next($friends)) {
			$friendsArray = $friends->asArray();
			$allFriends = array_merge($friendsArray, $allFriends);
		}
		

		foreach ($allFriends as $key) {
			
			$resim=$key['picture'];
			echo "<img src='".$resim['url']."'/>";
			echo utf8_decode($key['name']) . "<br>";
		
		}
		
	} else {
		$allFriends = $friends->asArray();
		$totalFriends = count($allFriends);
		foreach ($allFriends as $key) {
			
			$resim=$key['picture'];
			echo "<img src='".$resim['url']."'/>";
			print_r(utf8_decode($key['name'])) ;
			echo "</br></br>";
			
			file_put_contents('filem.php', " <p>'".$key['name']."</p>");
			//echo utf8_decode($key['name']) . "<br>";
		}
	}
echo'<body background="bg_img2.jpg" width="900px" height="1000px">';
echo '<a href="index.php">Geri</a>';
  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper();
	$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/smratingheroku/', $permissions);
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}
