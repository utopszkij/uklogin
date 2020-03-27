<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
* readme kezelő osztály
*/
class ReadmeController extends Controller {
    
	 /**
	 * readme megjelenités
	 * @param Request $request
	 */
    public function show(Request $request) {
	    $this->docPage($request,  'readme');
	}
}
?>