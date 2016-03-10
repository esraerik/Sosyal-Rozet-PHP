<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';
header('Content-Type: text/html; charset=utf-8');
header("Access-Control-Allow-Origin: http://localhost:8080/turkish-nlp-examples/servletdeneme");
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: *');

$fb = new Facebook\Facebook([
  'app_id' => '150494718639750',
  'app_secret' => 'ce13013f03d23d5c354d399a7e7a2138',
  'default_graph_version' => 'v2.5',
  ]);
$helper = $fb->getRedirectLoginHelper();
$permissions = ['email,user_friends','user_posts','user_likes','publish_actions']; // optional
	
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
		// getting short-lived access token
		$_SESSION['facebook_access_token'] = (string) $accessToken;
	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();
		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);
		$_SESSION['facebook_access_token'] = (string) $longLivedAccessToken;
		// setting default access token to be used in script
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}
	// redirect the user back to the same page if it has "code" GET variable
	if (isset($_GET['code'])) {
		header('Location: ./');
	}
	// getting basic info about user
	try {
		$requestPicture = $fb->get('/me/picture?redirect=false&height=200'); //getting user picture
		$requestProfile = $fb->get('/me'); // getting basic info
		$picture = $requestPicture->getGraphUser();
		$profile = $requestProfile->getGraphUser();
		$res = $fb->get( '/me/feed' );
		//$feed = $res->getGraphObject();
		$posts_request = $fb->get('/me/posts?limit=500');
		$posts = $fb->get('/me/tagged');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		session_destroy();
		// redirecting user back to app login page
		header("Location: ./");
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	// showing picture on the screen
	//echo "<img src='".$picture['url']."'/>";
	$pic=$picture['url'];
	//print_r ($profile['name']);
	$name=$profile['name'];
	//$Degisken=utf8_encode( $name );
	$Degisken=iconv("ISO-8859-9", "UTF-8",$name);
	// saving picture
	$img = __DIR__.'/'.$profile['id'].'.jpg';
	file_put_contents($img, file_get_contents($picture['url']));
	$feed = $res->getGraphEdge()->asArray();
	
	$in_encoding = "ISO-8859-1";
		foreach($feed as $key){
			$yazi.=$key['message'];
		}
	
	$total_posts = array();
	
	$var = "  ";
	file_put_contents('file.php', $var);
		$posts_res = $posts->getGraphEdge()->asArray();
		$in_encoding = "ISO-8859-1";
		foreach($posts_res as $key){
			$yazi.=$key['message'];
			file_put_contents('file.php', utf8_decode($key['message']), FILE_APPEND);
		}
		
	// if have more friends than 100 as we defined the limit above on line no. 68
	
	$posts_response = $posts_request->getGraphEdge();
//$var_str = var_export($posts_response, true);

	if($fb->next($posts_response)) {
		$response_array = $posts_response->asArray();
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
			$yazi.=$key['message'];
			
		//bu super dönüştürüyor ama alırken dönüşüm yaptığım şeyi java kabul etm,yor
			
		}
	}
	//LOGOUT
	//verilen url kullanıcın yönlendirileceği sayfa diğer ama facebooka yönlendiriyor???
		//$logoutUrl = $helper->getLogoutUrl('facebook_access_token', 'http://sosyalrozet.anymaa.com/facebook/');
		
		// redirecting user back to app login page
		//header("Location: ./");
echo '<a href="logout.php">ÇIKIŞ YAP!</a>';
$logoutUrl = $helper->getLogoutUrl('facebook_access_token', 'http://sosyalrozet.anymaa.com/facebook/');
echo'<a href="'.$logoutUrl .'">ÇIKIŞ YAP Facebook </a>';
  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
?>
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/HTML; charset=utf-8" />
<html >
<head>
  <title>Smrating</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
 
</head>
<body id="bdy" background="bg_img2.jpg" width="900px" height="1000px" onload="learner()">

<div class="container">
</br></br>
	<div style="width:1040px;float:left">
		<p><img style="margin-left:120px; margin-right:200px" align="auto" src="sosyalrozetlogo.png"></p>
	</div>
	<div style="width:300px;float:right">	
	</div>
	</br></br>
  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#profile">Profil</a></li>
	 <li><a data-toggle="tab" href="#friends">Arkadaşlar</a></li>
    <li><a data-toggle="tab" href="#likes">Beğeniler</a></li>
	
  </ul>

<div class="tab-content">
  <div id="profile" class="tab-pane fade in active">
    <h3>Profil</h3>
	
<form id="commentForm" name="commentForm" action="http://localhost:8080/turkish-nlp-examples/servletdeneme"  method="POST">
<input type="hidden" name="phpto" id="phpto" value="<?php echo htmlspecialchars($yazi); ?>" />
<img  name="profilepic" id="profilepic"  src="<?php echo $pic; ?>" value="<?php echo htmlspecialchars($pic); ?>"/>
<p><?php echo $Degisken ?></p>
<input type="hidden"name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" />
<button type="submit" name="submit" id="commentSubmit" onclick="myfunc()" value="RozetIstegi">Rozet Istegi</button> 

<div id="data" name="data"></div>

</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.js"></script>
<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<script src="http://code.jquery.com/jquery-1.5.js"></script>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script type="text/javascript">

function myfunc(){

	//alert("hi");
	var json ='';
	status='hi';
	var posst=$("#commentForm").serializeArray();
	
	//var formURL = $("http://localhost:8080/turkish-nlp-examples/servletdeneme/doGet");
	   $.ajax(
        {
            url : 'http://localhost:8080/turkish-nlp-examples/servletdeneme/doGet',
            type: "POST",
            data :  posst,
			contentType: "application/json;charset=utf-8",
			processData: false,
			crossDomain: true,
			async:false,
			cache:false,
			dataType: 'jsonp',
			traditional: true,
			headers:'Content-Type',
			jsonpCallback: "localJsonpCallback",
			complete: function(response){
			$('#data').html(response.responseText);
			},
			success: function(data){
				console.log('response:'+data);
				json=$.parseJSON(data); // create an object with the key of the array
				alert("çalıstı");
			},
            error: function(jqXHR, textStatus, errorThrown) 
            {
				console.log('hata');
                alert("hata");
				
            }
        });
    //alert(json+"slm");

}	
function localJsonpCallback(json) {
		
        if (!json.Error) {
           alert("localjsonpcallback çalıstı");
        }
        else {
          
            alert(json.Message);
        }
    }


       </script>
	   
	 <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
	 <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
     <script type="text/javascript">
     </script>

<form id="commentFormWrap" name="commentForm"  method="POST">   
<div id="yeni">Sosyal Hayatını Rozetlendir</div>
	   <script type="text/javascript">
	
	  $(document).ready(function(){
		   var contentType ="application/x-www-form-urlencoded; charset=utf-8";
			if(window.XDomainRequest) //for IE8,IE9
			contentType = "text/plain";
      $("#ShareItem").click(function(e){
          e.preventDefault();
		  var contentType ="application/x-www-form-urlencoded; charset=utf-8";

 var posst=$("#commentForm").serializeArray();
$.ajax({
     url:'http://localhost:8080/turkish-nlp-examples/servletdeneme',
     data:posst,
     type:"POST",
     dataType:"json", 
	 headers:'Content-Type',    
     success:function(data)
     {
        alert("Data from Server"+JSON.stringify(data));
     },
     error:function(jqXHR,textStatus,errorThrown)
     {
        alert("You can not send Cross Domain AJAX requests: "+errorThrown);
     }
    });
      });
    });
 
       </script>

</form>
	   
	<form action="upload-photo-on-timeline.php" method="get"> 
<button type="submit" onclick="alarm()">Duvarda Paylaş</button> 
</form>
<script type="text/javascript">

function alarm(){

	alert("Rozetinizi Duvarda Paylaşıyorsunuz!");
}
</script>
    <p></p>
  </div>
  <div id="friends" class="tab-pane fade">
    <h3>Arkadaslar</h3>
	<form action="get-list-of-friends-names.php" method="get"> 
<button type="submit">Arkadaşları Gör</button> 
</form>
    <p></p>
  </div>
  <div id="likes" class="tab-pane fade">
    <h3>Beğeniler</h3>
	<form action="get-list-of-liked-pages.php" method="get"> 

<button type="submit">Beğenilen Sayfaları Gör</button> 
</form>
    <p></p>
	
  </div>
  </div>
</div>
<script>
function learner(){
	
//document.location.href='http://localhost:8080/turkish-nlp-examples/LearnerServlet';

	

}	</script>

</body>
</html>
<?php
} else {
	// replace your website URL same as added in the developers.facebook.com/apps e.g. if you used http instead of https and you used non-www version or www version of your website then you must add the same here
	$loginUrl = $helper->getLoginUrl('http://sosyalrozet.anymaa.com/facebook/', $permissions);
	echo '<a href="' . $loginUrl . '">Giriş Yap!</a>';
}
?>
