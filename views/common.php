<?php
class CommonView {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function echoNavbar($p) {
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
			        	<i class="fa fa-home"></i>&nbsp;<?php echo txt('HOME'); ?><span class="sr-only">(current)</span></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/readme/show">
			        	<i class="fa fa-info"></i>&nbsp;<?php echo txt('READMY'); ?></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/appregist/add">
			        	<i class="fa fa-plus"></i>&nbsp;<?php echo txt('NEWAPP'); ?></a>
			      </li>
			      <li class="nav-item">
			        <a class="nav-link" target="_self" href="<?php echo txt('MYDOMAIN'); ?>/opt/appregist/adminlogin">
			        	<i class="fa fa-key"></i>&nbsp;<?php echo txt('ADMINLOGIN'); ?></a>
			      </li>
			    </ul>
			  </div>
			  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			    <span class="navbar-toggler-icon"></span>
			  </button>
			</nav>   
			<p style="background-color:red; color:white">Ez a rendszer jelenleg fejlesztés alatt áll. Még nem használható! Készültség:1%</p>     
		<?php       
     } // echoNavbar
        
     function echoFooter() {
        ?>
      	<div id="footer">  
      	<p>
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/impresszum/show">
				<i class="fa fa-pencil"></i>&nbsp;<?php echo txt('IMPRESSUM'); ?></a>&nbsp;&nbsp;&nbsp;      
			<a href="<?php echo txt('MYDOMAIN'); ?>/opt/adatkezeles/show">
				<i class="fa fa-lock"></i>&nbsp;<?php echo txt('DATAPROCESS'); ?></a>&nbsp;&nbsp;&nbsp;      
			<a href="http://gnu.hu/gplv3.html">
				<i class="fa fa-copyright"></i>&nbsp;<?php echo txt('LICENCE'); ?>: GNU/GPL</a>&nbsp;&nbsp;&nbsp;      
			<a href="https://github.com/utopszkij/uklogin">
				<i class="fa fa-github"></i>&nbsp;<?php echo txt('SOURCE'); ?></a>&nbsp;&nbsp;&nbsp;   
			<a href="mailto:tibor.fogler@gmail.com">
				<i class="fa fa-bug"></i>&nbsp;<?php echo txt('BUGMSG'); ?></a>&nbsp;&nbsp;&nbsp;   
      	</p>   
		<p><?php echo txt('SWRESOURCE'); ?>:			
				<a href="https://www.php.net/manual/en/index.php">php</a>&nbsp;
				<a href="https://fontawesome.com/icons?d=gallery">fontAwesome</a>&nbsp;
				<a href="https://www.w3schools.com/css/">css</a>&nbsp;
				<a href="https://getbootstrap.com/">bootstrap</a>&nbsp;
				<a href="https://jquery.com/">Jquery</a>&nbsp;
				<a href="https://angularjs.org/">AngularJs</a>&nbsp;
		</p>
		<p><?php echo txt('SWFORKINFO'); ?>&nbsp;
	    	<a href="https://gitlab.com/mark.szabo-simon/elovalaszto-app?fbclid=IwAR2X4RlNDA4vHw5-4ABkDCzzuifNpE5-u9T7j1X-wuubag4ZY0fSvnifvMA">lásd itt</a></p>
		</div>
        <?php 		
	}
}
?>