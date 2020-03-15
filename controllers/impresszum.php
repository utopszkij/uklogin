<?php
class ImpresszumController extends Controller {
	public function show(RequestObject $request) {
	    $this->docPage($request,  'impresszum');
	}
}
?>