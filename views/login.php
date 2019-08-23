<?php
include_once './views/common.php';
class LoginView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function loginForm($p) {
	    echo htmlHead();
        ?>	
        <body ng-app="app">
          <?php $this->echoNavbar($p); ?>
          <div ng-controller="ctrl" id="scope" 
            style="display:block; padding:20px; background-image:url('./templates/default/cover_1.jpg')">
            <iframe title="login" src="<?php echo MYDOMAIN; ?>/oauth2/loginform/client_id/12/?state=<?php echo urlencode($p->state); ?>" 
            style="margin:20px; padding:10px; width:500px; height:800px; border-style:solid; border-width:1px;" ></iframe> 
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
	      </div><!-- #scope -->
		  <?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>

