<?php
class AppregistController {
	public function add($request) {
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$request->sessionSet('csrtoken', random_int(10000,99999));
		$view = getView('appregist');
		$data = new stdClass(); //  $data = $model->getData(....);
		$data->option = $request->input('option','default');
		// hibás kitöltés utáni visszahivásnál érkezhetnek form adatok is.
		$data->msg = $request->input('msg','');
		$data->client_id = ''; // új app -nél generálva lesz, üreset kell küldeni
		$data->name = $request->input('name','');
		$data->domain = $request->input('domain','');
		$data->css = $request->input('css','');
		$data->callback = $request->input('callBack','');
		$data->falseLoginLimit = $request->input('falseLoginLimit','5');
		$data->hackLimit = $request->input('hackLimit','10');
		$data->psw1 = '';
		$data->psw2 = '';
		$data->oldpsw = '';
		$data->admin = $request->input('admin','');
		$data->falseAdminLoginLimit = $request->input('falseAdminLoginLimit','5');
		$data->adminLoginEnabled = $request->input('adminLoginEnabled','1');
		$data->csrtoken = $request->sessionGet('csrtoken',0);
		$view->form($data);
	}
	
	/**
	 * do save into database (insert or update)
	 * @param object $request
	 */
	public function save($request) {
	    // check csrtoken
	    $csrtoken = $request->sessionGet('csrtoken',0);
	    if ($request->input($csrtoken,'0') != 1) {
	        echo '<p>Invalid CSR token</p>'; exit;
	    }
	    // csrtoken ok
	    
	    $model = getModel('appregist');
	    $view = getView('appregist');
	    // $data kialakitása a $request -ből
        $data = new stdClass();
        $data->id = 0;
        $data->name = $request->input('name','');
        $data->client_id = '';
        $data->client_secret = '';
        $data->domain = $request->input('domain','');
        $data->callback = $request->input('callback','');
        $data->css = $request->input('css','');
        $data->falseLoginLimit = $request->input('falseLoginLimit',5);
        $data->admin  = $request->input('admin','');
        $data->psw1  = $request->input('psw1','');
        $data->adminFalseLoginLimit  = $request->input('adminFalseLoginLimit',5);
        $data->adminLoginEnabled = 1;
        $data->dataProcessAccept = $request->input('dataProcessAccept',0);
        $data->cookieProcessAccept = $request->input('cookieProcessAccept',0);
        
	    $msg = $model->check($data);
	    if (count($msg) == 0) {
	        $res = $model->save($data);
	        if (!isset($res->error)) {
	            $view->successMsg($res);
	        } else {
	            $view->errorMsg();
	        }
	    } else {
	        $request->set('msg',$msg);
	        $this->add($request); 
	    }
	}
	
	/**
	 * remove app record from database
	 * @param object $request
	 */
	public function remove($request) {
	    // check csrtoken
	    $csrtoken = $request->sessionGet('csrtoken',0);
	    if ($request->input($csrtoken,'0') != 1) {
	        echo '<p>Invalid CSR token</p>'; exit;
	    }
	    // csrtoken ok
	    echo '<p>Remove app</p>'; 
	}
}
?>