<?php
include_once './views/common.php';
class ReadmeView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function display($p) {
	    $this->echoHtmlHead();
	    ?>
	    <body ng-app="app">
	    	<?php $this->echoNavbar($p); ?>
			<?php $this->echoLngHtml('readme',$p); ?>
			<?php $this->echoFooter(); ?>
	    </body>
	    </html>	
	    <?php 
	}
	
}
?>

