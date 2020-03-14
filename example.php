<?php
/*
 * Példa program az uklogin/openid használatára
 * Az app regisztrálásakor megadott callback URL: <MYDOMAIN>/example.php?task=code)
 * Az app client_id="000000000" client_secret="0000000000"
 * 
 * Hivásai:
 * 1. paraméter nélkül: képernyő kirajzolás
 * 2. task=code&token=xxxxxx  az uklogin hivta vissza, ilyenkor iframe -ben fut
 */
session_start();

// lokális teszt vagy éles szerver?.
if ($_SERVER['SERVER_NAME'] == 'robitc') {
    // lokális teszt
    define('MYDOMAIN','http://robitc/uklogin');
    define('UKLOGINDOMAIN','http://robitc/uklogin/openid');
} else if ($_SERVER['SERVER_NAME'] == '192.168.0.12') {
    // lokális teszt
    define('MYDOMAIN','http://192.168.0.12/uklogin');
    define('UKLOGINDOMAIN','http://192.168.0.12/uklogin/openid');
} else {
	// éles szerver
	define('MYDOMAIN','https://uklogin.tk');
	define('UKLOGINDOMAIN','https://uklogin.tk/openid');
}
define('CLIENTID','0000000000');
define('CLIENTSECRET','0000000000');

if (isset($_GET['task'])) {
    $task = $_GET['task'];
} else {
    $task = 'home';
}

/**
 * adat lekérés távoli url -ről curl POST -al
 * @param string $url
 * @param array $fields
 * @return string
 */
function getFromUrl(string $url, array $fields = []): string {
    $fields_string = '';
    $ch = curl_init();
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    $fields_string = rtrim($fields_string, '&');
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POST, count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
    return curl_exec($ch);
}

/**
 * uklogin/openid szerver hivta vissza. token, state, nonce paraméter érkezik
 */
if ($task == 'code') {
    $access_token = $_GET['token'];

    $_SESSION['access_token'] = $access_token;
    // userinfo lekérdezése
    $url = UKLOGINDOMAIN.'/userinfo/';
    $fields = ["access_token" => $access_token];
    $result = JSON_decode(getFromUrl($url, $fields));

    // kiirás a képernyőre
    echo '<!doctype html>
        <html>
        <head>
        <title>uklogin example</title>
        </head>
        <body>
        <h1>Sikeres login</h1>
        <p>uklogin szerver visszahivta token='.$access_token.'</p>
        <p>userinfo kérés eredménye:</p>
        '.JSON_encode($result).'
        </body>
        </html>
    ';
    
} // task=code

if ($task == 'home') {
    if (!isset($_SESSION['access_token'])) {
        $_SESSION['access_token'] = session_id();
    }
?>
<!doctype html>
<html lang="hu">
  <head>
    <base href="<?php echo MYDOMAIN; ?>" target="_blank">
    <meta charset="utf-8">
    <meta name="title" content="Example Ügyfélkapus login rendszer">
    <meta name="description" content="Example web szolgáltatás e-demokrácia programok számára. Regisztráció ügyfélkapus aláírás segitségével.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>example uklogin</title>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

	 <!-- bootstrap -->
	 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	 <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	 <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
	 <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

	 <!-- awesome font -->
	 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	 <!-- font -->
	 <link href='https://fonts.googleapis.com/css?family=Grand+Hotel' rel='stylesheet' type='text/css'>

	 <style type="text/css">
        /* desctop */
       @media (min-width: 1024px) {
           #scope {padding:5px}
    	   .main {padding:20px;}
    	   #popup {position:absolute; z-index:99; display:none;
    	     top:130px; left:15px; width:910px; max-width:910px; height:800px;
    	     background-color:white; margin:10px; border-style:solid; border-width:2px; border-color:black;}
    	   #popup iframe {border-style:none; width:900px; height:770px}
    	   #popupHeader {text-align:right;}
    	   #events {border-style:none}
    	   #sourceTitle {position:absolute; z-index:98; top:360px; left:100px; color:white;}
    	   #source {position:absolute; z-index:98; top:300px; left:50px; width:80%; background-color:white}
    	   #logo {width:100%}
    	   .demoInfo {position:absolute; z-index:60; top:300px; left:100px; width:600px; height:auto;
    	       background-color:silver; padding:10px;  opacity:0.5; color:black;}
	   }

       /* phone */
       @media (max-width: 1023px) {
           #scope {padding:5px}
    	   .main {padding:10px;}
    	   #popup {position:absolute; z-index:99; display:none;
    	     top:15px; left:15px; width:95%; max-width:550px; height:900px;
    	     background-color:white; margin:5px; border-style:solid; border-width:2px; border-color:black;}
    	   #popup iframe {border-style:none;  width:95%; height:850px}
    	   #popupHeader {text-align:right;}
    	   #events {border-style:none}
    	   #sourceTitle {position:absolute; z-index:98; top:360px; left:50px; color:white;}
    	   #source {position:absolute; z-index:98; top:300px; left:50px; width:80%; background-color:white}
    	   #logo {width:100%}
    	   .demoInfo {position:absolute; z-index:60; top:30px; left:30px;
    	       width:80%; height:auto;
    	       background-color:silver; padding:50px;  opacity:0.5; color:black;}
       }


	 </style>

  </head>
  <body>
  	<div class="main">
  		<h1>
  			Ügyfélkapus OpenId bejelentkezés példa program
  		</h1>
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
			  <div class="collapse navbar-collapse" id="navbarNav">
			    <ul class="navbar-nav">
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo MYDOMAIN; ?>">
			        	<em class="fa fa-home"></em>&nbsp;Kezdőlap<span class="sr-only">(current)</span></a>
			      </li>
			      <!-- li class="nav-item">
			        <a class="nav-link" target="_self" href="" id="linkRegist">
			        	<em class="fa fa-plus"></em>&nbsp;Regisztrálás</a>
			      </li -->
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="" id="linkLogin">
			        	<em class="fa fa-sign-in"></em>&nbsp;Bejelentkezés
			        </a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="ifrm1" 
			            href="<?php echo MYDOMAIN; ?>/openid/profileform" id="linkLogin"
			            onclick="$('#popup').show(); true;">
			        	<em class="fa fa-address-card-o"></em>&nbsp;Profil
			        </a>    
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="ifrm1"
			        	href="<?php echo MYDOMAIN; ?>/openid/logout/?token=<?php echo $_SESSION['access_token']; ?>" 
			        	id="linkLogin"
			        	onclick="$('#popup').show(); true;">
		        		<em class="fa fa-sign-out"></em>&nbsp;Kijelentkezés
			        </a>
			      </li>
			    </ul>
			  </div>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>
			</nav>
  			<div style="text-align:center">
  				<img src="<?php echo MYDOMAIN; ?>/templates/default/logo.jpg" id="logo" />
  			</div>
  			<div id="popup">
  				<div id="popupHeader">
  				 <em class="fa fa-close" style="cursor:pointer" title="close"
  					id="popupClose"></em>&nbsp;
  				</div>
  				<iframe id="ifrm1" name="ifrm1" src=""></iframe>
  			</div>
          	<h4 id="sourceTitle">Forrás program <em class="fa fa-code" style="cursor:pointer"
          		onclick="$('#source').toggle();" title="view"></em>
          	</h4>
          	<div id="source" style="display:none">
  				<div id="popupHeader">
  				 <em class="fa fa-close" style="cursor:pointer" title="close"
  					onclick="$('#source').toggle();"></em>&nbsp;
  				</div>
              	<textarea style="width:100%; height: 550px" readonly="readonly">
              		<?php
              		    echo "\n";
              		    $lines = file('example.php');
              		    foreach ($lines as $line) {
              		        $line = str_replace('<', '&lt;', $line);
              		        $line = str_replace('>', '&gt;', $line);
              		        echo $line;
              		    }
              		?>
              	</textarea>
          	</div>
  	</div>

  	<script type="text/javascript">
		$(function() {
			$('#linkRegist').click(function() {
				$('#ifrm1').attr('src',"<?php echo MYDOMAIN; ?>/opt/userregist/registform/client_id/<?php echo CLIENTID; ?>");
				$('#popup').show();
				return false; // ne hajsa végre a href linket
			});
			$('#linkLogin').click(function() {
				// oauth2 $('#ifrm1').attr('src',"<?php echo MYDOMAIN; ?>/oauth2/loginform/client_id/<?php echo CLIENTID; ?>");
				// gyakran /state/urldecoded(url) formában a sikeres login aután
				// aktivizálandó url -t is meg adunk.
				
				// openid
				var url ="<?php echo MYDOMAIN; ?>"+
						"/openid/authorize/"+
						"?client_id=<?php echo CLIENTID; ?>"+
						"&redirect_uri=<?php echo urlencode(MYDOMAIN.'/example.php?task=code'); ?>"+
						"&state=123"+
						"&nonce=567"+
						"&policy_uri=<?php echo urlencode(MYDOMAIN.'/adatkezeles.html'); ?>"+
						"&scope=<?php echo urlencode('sub nickname email postal_code locality'); ?>";
				$('#ifrm1').attr('src',url);
				$('#popup').show();
				return false; // ne hajsa végre a href linket
			});
			$('#popupClose').click(function() {
					var myFrame = $("#ifrm1").contents().find('body');
	        		myFrame.html('&nbsp;');	
	        		$('#popup').hide();				
		    });
		});
  	</script>

  </body>
</html>
<?php
} // task=home
?>
