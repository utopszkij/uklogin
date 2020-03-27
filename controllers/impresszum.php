<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/**
 * ImpresszumController osztály
 * @author utopszkij
 */
class ImpresszumController extends Controller {
    
    /**
     * impresszum képernyő megjelenítése
     * @param Request $request
     */
	public function show(Request $request) {
	    $this->docPage($request,  'impresszum');
	}
}
?>