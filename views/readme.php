<?php
include_once './views/common.php';
include_once './views/mdView.php';
class ReadmeView  extends CommonView  {
	/**
	* echo html page
	* @param object $p
	* @return void
	*/
	public function display($p) {
	    $view = new MdView();
	    $view->mdShow($p, './readme.md');
	    /* $this->echoHtmlHead();
	    ? >
	    <body ng-app="app">
	    	<? php $this->echoNavbar($p); ? >
			<? php $this->echoLngHtml('readme',$p); ? >
			<? php $this->echoFooter(); ? >
	    </body>
	    </html>	
	    <? php
	    */ 
	}
	
}
?>

