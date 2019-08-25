<?php
include_once './views/common.php';
class LoginView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function loginForm($p) {
	    $this->echoHtmlHead();
        ?>	
        <body ng-app="app">
          <?php $this->echoNavbar($p); ?>
          <div ng-controller="ctrl" id="scope" 
            style="display:block; padding:20px; background-image:url('./templates/default/cover_1.jpg')">
		    <div style="margin:20px; padding:5px; width:500px; height:820px; background-color:white; border-style:solid; border-width:1px;">
		    	<p style="text-align:right">
		    		<a title="<?php echo txt('CLOSE') ?>" href=""
		    		  onclick="location='<?php echo MYDOMAIN; ?>';">
					  <em class="fa fa-close"></em>			    		  
		    		</a>
		    	</p> 
	            <iframe title="login" src="<?php echo MYDOMAIN; ?>/oauth2/loginform/client_id/12/?state=<?php echo urlencode($p->state); ?>" 
    	        style="width:490px; height:800px;" ></iframe> 
			</div>div>
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

