<?php
class AdomanyController extends Controller {
    public function show(RequestObject $request) {
        $this->docPage($request,  'adomany');
    }
}
?>