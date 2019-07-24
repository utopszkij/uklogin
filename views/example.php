<?php
include_once './views/common.php';
class ExampleView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function example($p) {
	    echo htmlHead();
        ?>	
        <body ng-app="app">
         <?php $this->echoNavbar($p); ?>
         <div ng-controller="ctrl" id="scope" style="display:none">
	        <p>option={{option}}</p>
    
			<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
			  <div class="carousel-inner">
			    <div class="carousel-item active">
			      <img class="d-block w-100" src="./templates/default/cover_4.jpg" alt="First slide">
			       <div class="carousel-caption d-none d-md-block">
				   	 <h5>first slide</h5>
			   	 	<p>123</p>
					 </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/entrepreneur-3245868_1920.jpg" alt="Second slide">
			       <div class="carousel-caption d-none d-md-block">
				   	 <h5>second slide</h5>
			   	 	<p>123</p>
					 </div>
			    </div>
			    <div class="carousel-item">
			      <img class="d-block w-100" src="./templates/default/windmill-3322529_1920.jpg" alt="Third slide">
			       <div class="carousel-caption d-none d-md-block">
				   	 <h5>Third slide</h5>
			   	 	<p>123</p>
					 </div>
			    </div>
			  </div>
			  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
			    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
			    <span class="sr-only">Previous</span>
			  </a>
			  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
			    <span class="carousel-control-next-icon" aria-hidden="true"></span>
			    <span class="sr-only">Next</span>
			  </a>
			</div><!-- carousel -->        
    
        	<?php echo htmlPopup(); ?>
        		</div>
        	</div>
  	  
          <?php loadJavaScriptAngular('example',$p); ?>
          <script type="text/javascript">
			   $('.carousel').carousel();
          </script>    
		  <?php $this->echoFooter(); ?>
        </body>
        </html>
        <?php 		
	}
}
?>