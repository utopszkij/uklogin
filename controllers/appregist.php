<?php
class AppregistController {
	public function add($request) {
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$data = new stdClass(); 
		createCsrToken($request, $data);
		$view = getView('appregist');
		$data->option = $request->input('option','default');
		// hibás kitöltés utáni visszahivásnál érkezhetnek form adatok is.
		$data->msg = $request->input('msg','');
		$data->client_id = ''; // új app -nél generálva lesz, üreset kell küldeni
		$data->client_secret = ''; // új app -nél generálva lesz, üreset kell küldeni
		$data->id = ''; // új app -nél generálva lesz, üreset kell küldeni
		$data->name = $request->input('name','');
		$data->domain = $request->input('domain','');
		$data->css = $request->input('css','');
		$data->callback = $request->input('callback','');
		$data->falseLoginLimit = $request->input('falseLoginLimit','5');
		$data->hackLimit = $request->input('hackLimit','10');
		$data->psw1 = '';
		$data->psw2 = '';
		$data->oldpsw = '';
		$data->admin = $request->input('admin','');
		$data->adminFalseLoginLimit = $request->input('adminFalseLoginLimit','5');
		$data->adminLoginEnabled = $request->input('adminLoginEnabled','1');
		$view->form($data);
	}
	
	/**
	 * do save into database (insert or update)
	 * @param object $request
	 */
	public function save($request) {
	    // check csrtoken
	    checkCsrToken($request);
	    
	    // csrtoken ok
	    $model = getModel('appregist');
	    $view = getView('appregist');
	    // $data kialakitása a $request -ből
        $data = new stdClass(); 
        $data->id = $request->input('id','');
        $data->name = $request->input('name','');
        $data->client_id = $request->input('client_id','');
        $data->client_secret = $request->input('client_secret','');
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
	            $view->errorMsg($res);
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
	    checkCsrToken($request);
	    
	    // csrtoken ok
	    echo '<p>Remove app</p>'; 
	}
	
	/**
	 * adminisztrátor login képernyő megjelenítése
	 * @return void
	 */ 
	public function adminlogin($request) {
	    $p = new stdClass(); 
	    createCsrToken($request, $p);
	    $view = getView('appregist');
	    $p->msg = $request->input('msg','');
	    $view->adminLoginForm($p); 
	}
	
	protected function echoAdminForm(&$data, $request, $rec, $view) {
    	$data->option = $request->input('option','default');
    	$data->msg = $request->input('msg','');
    	$data->client_id = $rec->client_id;
    	$data->client_secret= $rec->client_secret;
    	$data->id= $rec->id;
    	$data->name = $rec->name;
    	$data->domain = $rec->domain;
    	$data->css = $rec->css;
    	$data->callback = $rec->callback;
    	$data->falseLoginLimit = $rec->falseLoginLimit;
    	$data->hackLimit = 10;
    	$data->psw1 = '';
    	$data->psw2 = '';
    	$data->oldpsw = '';
    	$data->admin = $rec->admin;
    	$data->adminFalseLoginLimit = $rec->adminFalseLoginLimit;
    	$data->adminLoginEnabled = $rec->adminLoginEnabled;
    	$view->form($data);
	}
	
	/**
	 * admin login error kezelése
	 * @param App $rec
	 * @param Request $request
	 * @param Model $model
	 * @return string|string[]
	 */
	protected function adminLoginError($rec, $request, $model) {
    	// hibaszámláló növelése, szükség esetén letiltás
    	if ($rec) {
    	    $adminFalseLoginLimit = $rec->adminFalseLoginLimit;
    	} else {
    	    $adminFalseLoginLimit = 10;
    	}
    	$errorCount = $request->sessionGet('errorCount',0);
    	$errorCount++;
    	$request->sessionSet('errorCount',$errorCount);
    	if (($errorCount > $adminFalseLoginLimit) && ($rec)) {
    	    $rec->adminLoginEnabled = 0;
    	    $model->update($rec);
    	}
    	if ($errorCount > $adminFalseLoginLimit) {
    	    $msg = 'ADMIN_LOGIN_DISABLED';
    	} else {
    	    $msg = ['INVALID_LOGIN',($adminFalseLoginLimit - $errorCount)];
    	}
    	return $msg;
	}
	
	/**
	 * Adminisztrátor login végrehajtása
	 * @param object $request
	 * @return void
	 */
	public function doadminlogin(&$request) {
	    // check csrtoken
	    checkCsrToken($request);
	    
	    $hackModel = getModel('hack');
	    if (!$hackModel->checkEnabled($_SERVER['REMOTE_ADDR'])) {
	        echo '<p>Disabled IP </p>';
	        exit;
	    }
	    
	    // csrtoken ok, ip enabled
	    $error = false;
	    $msg = '';
	    $model = getModel('appregist');
	    $view = getView('appregist');
	    $rec = $model->getData($request->input('client_id',''));
	    if ($rec) {
	        if (($rec->pswhash == hash('sha256', $request->input('psw',''), false)) &&
	            ($rec->admin == $request->input('nick')) &&
	            ($rec->adminLoginEnabled == 1)
	            ) {
	                // hacker hibaszámláló nullázása
	                $hackModel->ipEnable($_SERVER['REMOTE_ADDR']);
	                
	                // login hibaszámláló nullázása
	                $request->sessionSet('errorCount', 0);
	                
	                // app adat képernyő megjelenítése
	                $data = new stdClass(); 
	                createCsrToken($request, $data);
	                $this->echoAdminForm($data, $request, $rec, $view);
	        } else {
                $error = true;
	        }
	    } else {
	        $error = true;
	    }
	    if ($error) {
	        // hacker számláló növelése, szükség esetén letiltás
	        $hackModel->ipAddError($_SERVER['REMOTE_ADDR']);
	        $msg = $this->adminLoginError($rec, $request, $model);
	        $request->set('msg',$msg);
            $this->adminlogin($request);	        
	    } // van error
	} // doadminlogin
	
	public function logout($request) {
	    $request->sessionSet('csrtoken',random_int(1000000,9999999));
	    ?>
	    <script type="text/javascript">
			window.location = "./index.php";
	    </script>
	    <?php
	}
	
	public function appremove($request) {
	    // check csrtoken
	    checkCsrToken($request);
	    
	    $hackModel = getModel('hack');
	    if (!$hackModel->checkEnabled($_SERVER['REMOTE_ADDR'])) {
	        echo '<p>Disabled IP ';
	        exit;
	    }
	    
	    // csrtoken ok, ip enabled
	    // hacker hibaszámláló nullázása
	    $hackModel->ipEnable($_SERVER['REMOTE_ADDR']);
	    
	    $msg = '';
	    $model = getModel('appregist');
	    $view = getView('appregist');
	    $rec = $model->getData($request->input('client_id',''));
	    if ($rec) {
	        $msg = $model->remove($rec->client_id);
	        if ($msg == '') {
	            $view->removedMsg($rec);
	        } else {
	            ?>
	        	$view->notFoundMsg($msg);
	        	<?php
	        }
	    } else {
	        ?>
	        $view->notFoundMsg('ERROR_NOTFOUND');
	        <?php
	    }
	}
	
	/**
	 * user jelszó vátoztsatás
	 * sessionban érkezik a client_id
	 * @param Request $request - csrToken, nick, psw1
	 */
	public function changepsw($request) {
	    echo 'nincs kész';
	    
	}
	
	/**
	 * user tárolt adatainak lekérdezése
	 * sessionban érkezik a client_id
	 * @param Request $request - csrToken, nick, psw1
	 */
	public function mydata($request) {
	    echo 'nincs kész';
	    
	}
	
	/**
	 * user fiók törlése
	 * sessionban érkezik a client_id
	 * @param Request $request - csrToken, nick, psw1
	 */
	public function deleteaccount($request) {
	    echo 'nincs kész';
	    
	}

	/**
	 * user fiók elfelejtett jelszó
	 * sessionban érkezik a client_id
	 * @param Request $request - csrToken, nick
	 */
	public function forgetpsw($request) {
	    echo 'nincs kész';
	    
	}
	
}
?>