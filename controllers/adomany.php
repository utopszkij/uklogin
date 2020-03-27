<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/** AdomanyController  adomány kérés, elszámolás */
class AdomanyController extends Controller {
    
    /**
     * adomány kérés, elszámolás képernyő 
     * @param Request $request
     */
    public function show(Request $request) {
        $this->docPage($request,  'adomany');
    }
}
?>