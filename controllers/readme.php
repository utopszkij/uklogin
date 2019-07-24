<?php
class ReadmeController {
	public function show($request) {
      // echo frontpage
	   $request->set('sessionid','0');
		$request->set('lng','hu');
		//$model = getModel('default');
		$view = getView('readme');
		$data = new stdClass(); //  $data = $model->getData(....);
		$data->option = $request->input('option','default');
		if ($request->sessionGet('user','') != '') {
		    $data->user = $request->sessionGet('user','');
		}
		$view->display($data);
		
	}
}
?>