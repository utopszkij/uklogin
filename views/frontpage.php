<?php
include_once './views/common.php';
class FrontpageView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function display($p) {
	    if (!isset($p->user)) {
	        $p->user = new stdClass();
	        $p->user->id = 0;
	        $p->user->nick = 'guest';
	        $p->user->avatar = 'https://www.gravatar.com/avatar';
	    }
	    echo htmlHead();
        ?>	
        <body ng-app="app">
         <?php $this->echoNavbar($p); ?>
         <div ng-controller="ctrl" id="scope" style="display:none">
			<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
			  <div class="carousel-inner">
			    <div class="carousel-item active">
			      <img class="d-block w-100" src="./templates/default/cover_1.jpg" alt="First slide">
			      <div class="buttons">
			      	<a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      		<em class="fa fa-plus-circle"></em>&nbsp;<?php echo NEWAPP; ?></a>&nbsp;
			      	<a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      		<em class="fa fa-info-circle"></em>&nbsp;<?php echo DESC; ?></a>
			      </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo APPTITLE; ?></h5>
			   	 	<p><?php echo APPINFO; ?></p>
				   </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/cover_2.jpg" alt="Second slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo NEWAPP; ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo DESC; ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo APPTITLE; ?></h5>
			   	 	<p><?php echo APPINFO; ?></p>
				   </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/cover_4.jpg" alt="Third slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo NEWAPP; ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo DESC; ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo APPTITLE; ?></h5>
			   	 	<p><?php echo APPINFO; ?></p>
				   </div>
			    </div>
			    
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/logo.jpg" alt="Foorth slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo NEWAPP; ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo DESC; ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo APPTITLE; ?></h5>
			   	 	<p><?php echo APPINFO; ?></p>
				   </div>
			    </div>
			    
			  </div>
			  
			  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
			    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
			    <span class="sr-only"><?php echo PRIOR; ?>Elöző</span>
			  </a>
			  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
			    <span class="carousel-control-next-icon" aria-hidden="true"></span>
			    <span class="sr-only"><?php echo NEXT; ?></span>
			  </a>
			</div><!-- carousel -->        
        	<?php echo htmlPopup(); ?>
          </div><!-- #scope -->
		  <?php $this->echoFooter(); ?>
          <?php loadJavaScriptAngular('frontpage',$p); ?>
          
          <?php // echo JSON_encode($_SERVER); ?>
        </body>
        </html>
        <?php 		
	}
}
?>
