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
	}
	
}
?>

