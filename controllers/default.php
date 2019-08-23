<?php
class DefaultController {
	public function default($request) {
      // echo frontpage
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$view = getView('frontpage');
		$model = getModel('frontpage');
		$data = new stdClass(); //  $data = $model->getData(....);
		$data->option = $request->input('option','default');
		$data->appCount = $model->getAppCount();
		$data->userCount = $model->getUserCount();
		$data->adminNick = $request->sessionget('adminNick','');
		if ($request->sessionGet('user','') != '') {
		    $data->user = $request->sessionGet('user','');
		}
		$view->display($data);
	}
}
?>