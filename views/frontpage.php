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
	    $this->echoHtmlHead();
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
			      		<em class="fa fa-plus-circle"></em>&nbsp;<?php echo txt('NEWAPP'); ?></a>&nbsp;
			      	<a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      		<em class="fa fa-info-circle"></em>&nbsp;<?php echo txt('DESC'); ?></a>
			      </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo txt('APPTITLE'); ?></h5>
			   	 	<p><?php echo txt('APPINFO'); ?></p>
				   </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/cover_2.jpg" alt="Second slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo txt('NEWAPP'); ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo txt('DESC'); ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo txt('APPTITLE'); ?></h5>
			   	 	<p><?php echo txt('APPINFO'); ?></p>
				   </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/cover_4.jpg" alt="Third slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo txt('NEWAPP'); ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo txt('DESC'); ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo txt('APPTITLE'); ?></h5>
			   	 	<p><?php echo txt('APPINFO'); ?></p>
				   </div>
			    </div>
			    
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/logo.jpg" alt="Foorth slide">
			        <div class="buttons">
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/appregist/add" target="_self">
			      	  	<em class="fa fa-plus-circle"></em>&nbsp;<?php echo txt('NEWAPP'); ?></a>&nbsp;
			      	  <a href="<?php echo MYDOMAIN; ?>/opt/readme/show" target="_self">
			      	  	<em class="fa fa-info-circle"></em>&nbsp;<?php echo txt('DESC'); ?></a>
			        </div>
			       <div class="carousel-caption d-none d-md-block">
				   	<h5><?php echo txt('APPTITLE'); ?></h5>
			   	 	<p><?php echo txt('APPINFO'); ?></p>
				   </div>
			    </div>
			    
			  </div>
			  
			  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
			    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
			    <span class="sr-only"><?php echo txt('PRIOR'); ?>Elöző</span>
			  </a>
			  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
			    <span class="carousel-control-next-icon" aria-hidden="true"></span>
			    <span class="sr-only"><?php echo txt('NEXT'); ?></span>
			  </a>
			</div><!-- carousel -->   
			<div class="counters">
				<div class="appCount">
					<em class="fa fa-cog"></em><br />
					Regisztrált applikációk száma:
					<var><?php echo $p->appCount; ?></var>
				</div>
				<div class="userCount">
					<em class="fa fa-user"></em><br />
					Regisztrált felhasználói fiókok száma:
					<var><?php echo $p->userCount; ?></var>
				</div>
			</div>  
			<div class="info">
  				<p>
  				Ez egy minden párttól, szervezettől független civil kezdeményezés. Teljes egészében magán emberek
  				adományaiból működik. A rendszert üzemeltető szerver jelenleg 2019.szeptember 31. -ig van kiifizetve.
  				Amennyiben módja van rá, kérjük támogassa a rendszer működését.
  				</p>
  				<p>
  				  <a href="<?php echo MYDOMAIN; ?>/opt/adomany/show" 
  				     style="background-color:blue; color:white; padding:10px; border-radius:5px;">
  					Támogatás
  				  </a>
  				</p>
  			</div>
			   
        	<?php $this->echoHtmlPopup(); ?>
          </div><!-- #scope -->
		  <?php $this->echoFooter(); ?>
          <?php $this->loadJavaScriptAngular('frontpage',$p); ?>
        </body>
        </html>
        <?php 		
	}
}
?>
