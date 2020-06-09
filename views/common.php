<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * CommonView osztály
 * @author utopszkij
 */
class CommonView extends View {

    /**
     * sikeres akció üzenet
     * @param array $msgs nyelvi konstansokat tartalmazó tömb
	 * @param string $nextLink tovább link URL (opcionális)
	 * @param string $nextLinkText  tovább link szöverge (opcionális)
	 * @param bool $navbar     navbar+footer legyen vagy ne
     * @return void;}
     */
    public function successMsg(array $msgs,
        string $nextLink = '',
        string $nextLinkText = '',
        bool $navbar = false) {
        global $REQUEST, $PARAMS;
        $this->echoHtmlHead($PARAMS);
        ?>
        <body ng-app="app">
        <?php if ($navbar) {
            if (is_object($PARAMS)) {
                $p = $PARAMS;
            } else {
                $p = new Params();
            }
            $p->adminNick = $REQUEST->sessionGet('adminNick');
        	$p->access_token = $REQUEST->sessionGet('access_token');
        	$this->echoNavbar($p);
        }
        ?>
	    <div ng-controller="ctrl" id="scope" style="display:block" class="successMsg">
	    <h2 class="alert alert-success">
			<?php
			foreach ($msgs as $msg) {
			    echo txt($msg).'<br />';
			}
			?>
	    </h2>
	    </div>
	    <?php if ($nextLink != '') : ?>
	    <p><a href="<?php echo $nextLink; ?>" target="_self"><?php echo txt($nextLinkText); ?></a>
	    <?php endif; ?>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('oauth2',new stdClass()); ?>
	    </p>
        <?php if ($navbar) { $this->echoFooter(); } ?>
	    </body>
        </html>
        <?php
	}

	/**
	 * echo fatal error
	 * @param array $msgs nyelvi konstansokat tartalmazó tömb
	 * @param string $backLink
	 * @param string $backStr
	 * @param bool $navbar főmenü és lábléc  megejelenítés kell?
	 * @return void
	 */
	public function errorMsg(array $msgs,
	       string $backLink='',
	       string $backStr='',
	       bool $navbar = false) {
	    global $REQUEST, $PARAMS;
	    $this->echohtmlHead($PARAMS);
	    ?>
        <body ng-app="app">
        <?php if ($navbar) {
            if (is_object($PARAMS)) {
                $p = $PARAMS;
            } else {
                $p = new Params();
            }
   	       $p->adminNick = $REQUEST->sessionGet('adminNick');
           $p->access_token = $REQUEST->sessionGet('access_token');
           $this->echoNavbar($p);
        }


        ?>
	    <div ng-controller="ctrl" id="scope" style="display:block" class="errorMsg">
	    <h2 class="alert alert-danger">
			<?php
			foreach ($msgs as $msg) {
			    echo txt($msg).'<br />';
			}
			?>
	    </h2>
	    </div>
	    <?php if ($backLink != '') : ?>
	    <p><a href="<?php echo $backLink; ?>" target="_self"><?php echo txt($backStr); ?></a>
	    <?php endif; ?>
        <?php $this->echohtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('oauth2', new stdClass()); ?>
        <?php if ($navbar) { $this->echoFooter(); } ?>
        </body>
        </html>
        <?php
	}

	/**
	* echo html page
	* @param Params $p - adminNick
	* @return void
	*/
	public function echoNavbar(Params $p) {
        if (is_object($p->loggedUser)) {
            if ($p->loggedUser->id > 0) {
                $p->adminNick = $p->loggedUser->nickname;
            } else {
                $p->adminNick = '';
            }
        } else {
            $p->adminNick = '';
        }
        $login_redirect_uri = urlencode(MYDOMAIN.'/opt/default/logged/');
		$logout_redirect_uri = urlencode(MYDOMAIN.'/opt/default/logout/');
		$logout_uri = MYDOMAIN.'/openid/logout/'.
		  		'?token='.$p->access_token.
		  		'&token_type_hint=access_token'.
		  		'&redirect_uri='.$logout_redirect_uri;
		?>
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
			  <?php echo txt('MAINMENU'); ?>&nbsp;
			  <div class="collapse navbar-collapse" id="navbarNav">
			    <ul class="navbar-nav mr-auto">
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>">
			        	<em class="fa fa-home"></em>&nbsp;<?php echo txt('HOME'); ?><span class="sr-only">(current)</span></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/readme/show">
			        	<em class="fa fa-info"></em>&nbsp;<?php echo txt('READMY'); ?></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/appregist/add">
			        	<em class="fa fa-plus"></em>&nbsp;<?php echo txt('NEWAPP'); ?></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/example.php">
			        	<em class="fa fa-compass"></em>&nbsp;<?php echo txt('EXAMPLE'); ?></a>
			      </li>
			      <?php if ($p->adminNick != '') :?>
    			      <li class="nav-item">
    			        <a class="nav-link" target="_self"
    			            href="<?php echo txt('MYDOMAIN'); ?>/opt/appregist/adminform">
    			        	<em class="fa fa-cog"></em>&nbsp;<?php echo txt('MYAPPS'); ?></a>
    			      </li>
			      <?php endif; ?>
			    </ul>
			    <ul class="navbar-nav">
			      <?php if ($p->adminNick == '') :?>
    			      <li class="nav-item">
    			        <a class="nav-link" target="_self"
    			            href="<?php echo txt('MYDOMAIN'); ?>/openid/authorize/?redirect_uri=<?php echo $login_redirect_uri; ?>">
    			        	<em class="fa fa-sign-in"></em>&nbsp;<?php echo txt('LOGIN'); ?></a>
    			      </li>
			      <?php else : ?>
    			      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
       			            <?php if ($p->loggedUser->picture == '') : ?>
       			        		<em class="fa fa-user"></em>&nbsp;<strong><?php echo $p->adminNick; ?></strong>
       			        	<?php  else : ?>
       			        		<img src="<?php echo $p->loggedUser->picture; ?>" alt="avatar" height="25px" /><strong><?php echo $p->adminNick; ?></strong>
       			        	<?php endif; ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
       			          <a class="dropdown-item" target="_self"
        			           href="<?php echo MYDOMAIN; ?>/opt/openid/profileform">
        			           <em class="fa fa-edit"></em>&nbsp;Profil
        			      </a>
	                      <?php if ($p->loggedUser->sysadmin == 1) : ?>
       			          <a class="dropdown-item" target="_self"
        			            href="<?php echo config('MYDOMAIN'); ?>/opt/auditor/form">
        			        	<em class="fa fa-check"></em>&nbsp;<?php echo txt('AUDITOR'); ?>
        			      </a>
        	              <?php endif; ?>
       			          <a class="dropdown-item" target="_self"
        			            href="<?php echo $logout_uri; ?>">
        			        	<em class="fa fa-sign-out"></em>&nbsp;<?php echo txt('LOGOUT'); ?>
        			      </a>
                        </div>
                      </li>
			      <?php endif; ?>
			    </ul>
			  </div>
			  <div class="clr"></div>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>
			</nav>
			<p style="background-color:red; color:white">Ez a rendszer jelenleg ß teszt állapotban használható.</p>
		<?php
     } // echoNavbar

     /**
      * html lábléc kiirása
      */
     function echoFooter() {
        ?>
      	<div id="footer">
      	<p>
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/impresszum/show" target="_self">
				<em class="fa fa-pencil"></em>&nbsp;<?php echo txt('IMPRESSUM'); ?></a>&nbsp;&nbsp;&nbsp;
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_self">
				<em class="fa fa-lock"></em>&nbsp;<?php echo txt('DATAPROCESS'); ?></a>&nbsp;&nbsp;&nbsp;
			<a href="http://www.gnu.hu/gpl.html" target="_new">
				<em class="fa fa-copyright"></em>&nbsp;<?php echo txt('LICENCE'); ?>: GNU/GPL</a>&nbsp;&nbsp;&nbsp;
			<a href="https://github.com/utopszkij/uklogin" target="_self">
				<em class="fa fa-github"></em>&nbsp;<?php echo txt('SOURCE'); ?></a>&nbsp;&nbsp;&nbsp;
			<a href="<?php echo MYDOMAIN; ?>/opt/issu/form" target="_self">
				<em class="fa fa-bug"></em>&nbsp;<?php echo txt('BUGMSG'); ?></a>&nbsp;&nbsp;&nbsp;
      	</p>
		<p><?php echo txt('SWRESOURCE'); ?>:
				<a href="https://www.php.net/manual/en/index.php" target="_self">php</a>&nbsp;
				<a href="https://fontawesome.com/icons?d=gallery" target="_self">fontAwesome</a>&nbsp;
				<a href="https://www.w3schools.com/css/" target="_self">css</a>&nbsp;
				<a href="https://getbootstrap.com/" target="_self">bootstrap</a>&nbsp;
				<a href="https://jquery.com/" target="_self">Jquery</a>&nbsp;
				<a href="https://angularjs.org/" target="_self">AngularJs</a>&nbsp;
				<a href="https://www.fpdf.org" target="_self">fpdf</a>&nbsp;
				<a href="https://github.com/smalot/pdfparser" target="_self">smalot</a>&nbsp;
				<a href="https://github.com/tan-tan-kanarek/github-php-client">
					tan-tan-kanarek_github_kliens
				</a>
		</p>
		<p>Teszteléshez: phpunit&nbsp;mocha&nbsp;sonar-cloud&nbsp;</p>
		<p><?php echo txt('SWFORKINFO'); ?>&nbsp;
	    	<a href="https://gitlab.com/mark.szabo-simon/elovalaszto-app?fbclid=IwAR2X4RlNDA4vHw5-4ABkDCzzuifNpE5-u9T7j1X-wuubag4ZY0fSvnifvMA">lásd itt</a></p>
		</div>
        <?php
	}
}
?>
