<?php
class DefaultController {
	public function default($request) {
      // echo frontpage
	   $request->set('sessionid','0');
		$request->set('lng','hu');
		//$model = getModel('default');
		$view = getView('frontpage');
		$data = new stdClass(); //  $data = $model->getData(....);
		$data->option = $request->input('option','default');
		if ($request->sessionGet('user','') != '') {
		    $data->user = $request->sessionGet('user','');
		}
		$view->display($data);
		
	}
}
?>