<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

include_once './views/common.php';
include_once './views/mdView.php';

/**
 * AdatkezelesView
 * @author utopszkij
 */
class AdatkezelesView  extends CommonView  {
	/**
	* echo html page
	* @param Params $p
	* @return void
	*/
	public function display(Params $p) {
	    $view = new MdView();
	    $view->mdShow($p, './adatkezeles.md');
	}
}
?>

