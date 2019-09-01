<?php
class ReadmeController extends Controller {
    public function show(RequestObject $request) {
	    $this->docPage($request,  'readme');
	}
}
?>