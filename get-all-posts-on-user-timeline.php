<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
$fb = new Facebook\Facebook([
  'app_id' => '150494718639750',
  'app_secret' => 'ce13013f03d23d5c354d399a7e7a2138',
  'default_graph_version' => 'v2.4',
]);
$helper = $fb->getCanvasHelper();

$permissions = ['user_posts','publish_actions','email','user_likes']; // optionnal
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
			exit;
		}
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	// getting all posts published by user
	try {
		$res = $fb->get( '/me/feed' );
		//$feed = $res->getGraphObject();
		$posts_request = $fb->get('/me/posts?limit=500');
		$posts = $fb->get('/me/tagged');
	//	$post = $post->getGraphNode()->asArray();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
	$feed = $res->getGraphEdge()->asArray();
		$in_encoding = "ISO-8859-1";
		foreach($feed as $key){
		echo utf8_decode($key['message']);
		//file_put_contents('file.php', utf8_decode($key['message']), FILE_APPEND);
		}
	$total_posts = array();
	
	$var = "  ";
file_put_contents('file.php', $var);
		$posts_res = $posts->getGraphEdge()->asArray();
		$in_encoding = "ISO-8859-1";
		foreach($posts_res as $key){
		echo utf8_decode($key['message']);
		file_put_contents('file.php', utf8_decode($key['message']), FILE_APPEND);
		}
	//foreach($posts as $key){
//	echo utf8_decode($key['message']);}
	// if have more friends than 100 as we defined the limit above on line no. 68
	
	$posts_response = $posts_request->getGraphEdge();
//$var_str = var_export($posts_response, true);

	if($fb->next($posts_response)) {
		$response_array = $posts_response->asArray();
		//print_r($response_array['message']);
		$total_posts = array_merge($total_posts, $response_array);
		while ($posts_response = $fb->next($posts_response)) {	
		
			$response_array = $posts_response->asArray();
			$total_posts = array_merge($total_posts, $response_array);	
		}
		print_r($total_posts);
	} else {
		$posts_response = $posts_request->getGraphEdge()->asArray();
		$in_encoding = "ISO-8859-1";

		foreach($posts_response as $key){
			echo utf8_decode($key['message']);
			file_put_contents('file.php', utf8_decode($key['message']), FILE_APPEND);
			echo '<script type="text/javascript">run();</script>';
		}
	}
	echo '<script type="text/javascript">run();</script>';//fonksiyon çağrısı

	//utf8_encode_deep($structure); birde bunu dene
  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper();
	$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/smratingheroku/', $permissions);
	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}
?>
<html>
    <head>
       <title></title>
	   
<form id="commentForm" name="commentForm" action="http://localhost:8080/turkish-nlp-examples/servletdeneme" method="POST">
<div id="data">deneme</div>

<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script src="http://code.jquery.com/jquery-1.5.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js"></script>
<script type="text/javascript">

function run(){
	
	
var postlar='esra=biesey';
var formURL = $(http://localhost:8080/turkish-nlp-examples/servletdeneme");
	JSON.stringify(postlar);
	        $.ajax(
        {
            url : formURL,
            type: "POST",
            data :  postlar,
           success:function(data, textStatus, jqXHR) 
            {
                $("#commentFormWrap").html("<p>Success</p>");
            },
            error: function(jqXHR, textStatus, errorThrown) 
            {
                $("#commentFormWrap").html("<p>error: "+errorThrown+"</p>");
            }
        });

    $.post('http://localhost:8080/turkish-nlp-examples/servletdeneme', function(data) {
        alert(data);
		$('#data').text(data);
    });
}
			
           <?php
               echo "run();";
           ?>
       </script>
	  </form>
  </head>
    <body>
    </body>
</html>
