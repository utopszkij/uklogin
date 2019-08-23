<?php
include_once './controllers/login.php';
class AppregistController {
	/**
	 * új app felvivő képernyő kirajzolása
	 * sessionban jöhet adminNick
	 * @param object $request
	 */
    public function add($request) {
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		
		// ha a sessionban nincs adminNick akkor az admin nincs bejelentkezve,
		// bejelentkezési popup kirajzolása
		if ($request->sessionGet('adminNick') == '') {
            $request->set('state', MYDOMAIN.'/opt/appregist/add');
            $loginController = new LoginController();
            $loginController->form($request);
            return;
		}
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
		$data->admin = $request->sessionGet('adminNick','');
		$data->adminFalseLoginLimit = $request->input('adminFalseLoginLimit','5');
		$data->adminLoginEnabled = $request->input('adminLoginEnabled','1');
		$data->adminNick = $request->sessionGet('adminNick','');
		$request->sessionSet('adminNick',$data->adminNick);
		$view->form($data);
	}
	
	/**
	 * do save into database (insert or update)
	 * sessinban van az adminNick
	 * @param object $request form fields
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
        $data->admin  = $request->sessionGet('adminNick','');
        $data->dataProcessAccept = $request->input('dataProcessAccept',0);
        $data->cookieProcessAccept = $request->input('cookieProcessAccept',0);
	    $msg = $model->check($data);
	    if (count($msg) == 0) {
	        $res = $model->save($data);
	        if (!isset($res->error)) {
	            $res->adminNick = $request->sessionGet('adminNick');
	            $view->successMsg($res);
	        } else {
	            $res->adminNick = $request->sessionGet('adminNick');
	            $view->errorMsg($res);
	        }
	    } else {
	        $request->set('msg',$msg);
	        $this->add($request); 
	    }
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
	    $p->state = MYDOMAIN.'/opt/appregist/adminform';
	    $p->adminNick = $request->sessionGet('adminNick','');
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
    	$data->admin = $rec->admin;
    	$data->adminNick = $request->sessionGet('adminNick','');
    	$view->form($data);
	}
	
	/**
	 * sikeres admin login után echo admin form
	 * sessionban érkezik az adminNick, request-ben érkezhet client_id
	 * @param object $request
	 */
	public function adminform($request) {
	    $model = getModel('appregist');
	    $view = getView('appregist');
	    $adminNick = $request->sessionGet('adminNick');
        $request->sessionSet('adminNick',$adminNick);
	    if ($adminNick == '') {
	        $rec = new stdClass();
	        $rec->error = 'FATAL_ERROR';
	        $rec->adminNick = $request->sessionGet('adminNick');
	        $view->errorMsg($rec);
	    }
	    $request->set('sessionid','0');
	    $request->set('lng','hu');
	    $data = new stdClass();
	    createCsrToken($request, $data);
	    
	    // adminNick összes app rekordjának beolvasása
	    $data->apps = $model->getAppsByAdmin($adminNick);
	    $data->adminNick = $request->sessionGet('adminNick','');
	    
	    if (count($data->apps) == 0) {
	        // nincsen ennek az adminnak applikációi
	        $rec = new stdClass();
	        $rec->error = 'ERROR_APP_NOTFOUND';
	        $rec->adminNick = $data->adminNick;
	        $view->errorMsg($rec);
	    } else {
	        $client_id = $request->input('client_id','');
	        if ($client_id == '') {
	            // az első applikáció adatainak megjelenitése
	            $data->client_id = $data->apps[0]->client_id;
	            $this->echoAdminForm($data, $request, $data->apps[0], $view);
	        } else {
	            // megkeressük a request-ben érkezett client_id -t az apps -ben és azt jelenitjük meg
	           $ok = false; 
	           foreach ($data->apps as $app) {
	               if ($app->client_id == $client_id) {
	                   $data->client_id = $app->client_id;
	                   $this->echoAdminForm($data, $request, $app, $view);
	                   $ok = true;
	               }
	           } // foreach
	           if (!$ok) {
	               $rec = new stdClass();
	               $rec->error = 'FATAL_ERROR';
	               $rec->adminNick = $data->adminNick;
	               $view->errorMsg($rec);
	           }
	        } // jött client_id a request -ben?
	    }
	}
	
	/**
	 * admin login error kezelése
	 * @param App $rec
	 * @param object $request
	 * @param object $model
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
	                $data->adminNick = $request->sessionget('adminNick','');
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
	    $request->sessionSet('adminNick','');
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
	            $rec->adminNick = $request->sessionGet('adminNick');
	            $view->removedMsg($rec);
	        } else {
	            $rec->adminNick = $request->sessionGet('adminNick');
				$rec->error = $msg;	            
	        	$view->errorMsg($rec);
	        }
	    } else {
	        $rec = new stdClass();
            $rec->adminNick = $request->sessionget('adminNick');
			$rec->error = 'ERROR_NOTFOUND';	            
	        $view->errorMsg($rec);
	    }
	}
	
}
?>