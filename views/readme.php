
<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

include_once './views/common.php';
include_once './views/mdView.php';
/** ReadmeView readme.md file megjelenítése */
class ReadmeView  extends CommonView  {
	/**
	* echo readme html page
	* @param Params $p
	* @return void
	*/
	public function display(Params $p) {
	    $view = new MdView();
	    $view->mdShow($p, './readme.md');
	}
	
}
?>

