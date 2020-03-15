<?php
class CommonView extends View {
   
    /**
     * echo succes message after add new app
     * @param object $res {client_id, client_secret
     * @return void;}
     */
    public function successMsg(array $msgs, bool $navbar = false) {
        global $REQUEST;
        $this->echoHtmlHead();
        ?>
        <body ng-app="app">
        <?php if ($navbar) {
        	       $p = new stdClass();
        	       $p->adminNick = $REQUEST->sessionGet('adminNick');
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
        </body>
        <?php $this->echoHtmlPopup(); ?>
        <?php $this->loadJavaScriptAngular('oauth2',new stdClass()); ?>
        </html>
        <?php 
	}
    
	/**
	 * echo fatal error in app save
	 * @param array of string messages
	 * @param string backLink
	 * @param string backLinkText
	 * @param bool show navbar
	 * @return void
	 */
	public function errorMsg(array $msgs, string $backLink='', string $backStr='', bool $navbar = false) {
	    global $REQUEST;
	    $this->echohtmlHead();
	    ?>
        <body ng-app="app">
        <?php if ($navbar) {
        	       $p = new stdClass();
        	       $p->adminNick = $REQUEST->sessionGet('adminNick');
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
        </body>
        </html>
        <?php 
	}
    
	/**
	* echo html page
	* @param object $p - adminNick
	* @return void
	*/
	public function echoNavbar($p) {
	    if (!isset($p->adminNick)) {
	        $p->adminNick = '';
	    }
        if (!isset($p->user)) {
					$p->user = JSON_decode('{"id":0, "nick":"user1", "avatar":"http://www.gravatar.com/avatar"}');
		}         
         ?>
			<nav class="navbar navbar-expand-lg navbar-light bg-light">
			  <?php echo txt('MAINMENU'); ?>&nbsp;
			  <div class="collapse navbar-collapse" id="navbarNav">
			    <ul class="navbar-nav">
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
			      <?php if ($p->adminNick == '') :?>
    			      <li class="nav-item">
    			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/login/form">
    			        	<em class="fa fa-key"></em>&nbsp;<?php echo txt('ADMINLOGIN'); ?></a>
    			      </li>
			      <?php else : ?>
    			      <li class="nav-item">
    			        <a class="nav-link alert-success" target="_self">
    			        	<em class="fa fa-user"></em>&nbsp;<strong><?php echo $p->adminNick; ?></strong></a>
    			      </li>
    			      <li class="nav-item">
    			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/login/form">
    			        	<em class="fa fa-cog"></em>&nbsp;<?php echo txt('MYAPPS'); ?></a>
    			      </li>
    			      <li class="nav-item">
    			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/login/logout">
    			        	<em class="fa fa-sign-out"></em>&nbsp;<?php echo txt('LOGOUT'); ?></a>
    			      </li>
			      <?php endif; ?>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/example.php">
			        	<em class="fa fa-compass"></em>&nbsp;<?php echo txt('EXAMPLE'); ?></a>
			      </li>
			    </ul>
			  </div>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>
			</nav>   
			<p id="loggedUser"><?php echo $p->adminNick; ?></p>
			<p style="background-color:red; color:white">Ez a rendszer jelenleg ß teszt állapotban használható.</p>     
		<?php       
     } // echoNavbar
        
     function echoFooter() {
        ?>
      	<div id="footer">  
      	<p>
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/impresszum/show" target="_self">
				<em class="fa fa-pencil"></em>&nbsp;<?php echo txt('IMPRESSUM'); ?></a>&nbsp;&nbsp;&nbsp;      
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show" target="_self">
				<em class="fa fa-lock"></em>&nbsp;<?php echo txt('DATAPROCESS'); ?></a>&nbsp;&nbsp;&nbsp;      
			<a href="https://gnu.hu/gplv3.html" target="_self">
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