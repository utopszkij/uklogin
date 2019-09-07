<?php
include_once './controllers/login.php';
class AppregistController extends Controller {
	/**
	 * új app felvivő képernyő kirajzolása
	 * sessionban jöhet adminNick
	 * @param object $request
	 */
    public function add(RequestObject $request) {
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
		$this->createCsrToken($request, $data);
		$view = $this->getView('appregist');
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
	public function save(RequestObject $request) {
	    // check csrtoken
	    $this->checkCsrToken($request);
	    
	    // csrtoken ok
	    $model = $this->getModel('appregist');
	    $view = $this->getView('appregist');
	    // $data kialakitása a $request -ből
	    // $data = new stdClass();
	    $data = new AppRecord();
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
	            $view->AppsuccessMsg($res);
	        } else {
	            $res->adminNick = $request->sessionGet('adminNick');
	            $view->errorMsg($res->error,'','',true);
	        }
	    } else {
	        $request->set('msg',$msg);
	        $this->add($request); 
	    }
	}
	
	protected function echoAdminForm(&$data, RequestObject $request, $rec, ViewObject $view) {
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
	public function adminform(RequestObject $request) {
	    $model = $this->getModel('appregist');
	    $view = $this->getView('appregist');
	    $adminNick = $request->sessionGet('adminNick');
        $request->sessionSet('adminNick',$adminNick);
	    if ($adminNick == '') {
	        $rec = new stdClass();
	        $rec->error = 'FATAL_ERROR';
	        $rec->adminNick = $request->sessionGet('adminNick');
	        $view->errorMsg([$rec->error],'','',true);
	    }
	    $request->set('sessionid','0');
	    $request->set('lng','hu');
	    $data = new stdClass();
	    $this->createCsrToken($request, $data);
	    
	    // adminNick összes app rekordjának beolvasása
	    $data->apps = $model->getAppsByAdmin($adminNick);
	    $data->adminNick = $request->sessionGet('adminNick','');
	    
	    if (count($data->apps) == 0) {
	        // nincsen ennek az adminnak applikációi
	        $rec = new stdClass();
	        $rec->error = 'ERROR_APP_NOTFOUND';
	        $rec->adminNick = $data->adminNick;
	        $view->errorMsg([$rec->error],'','',true);
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
	               $view->errorMsg([$rec->error],'','',true);
	           }
	        } // jött client_id a request -ben?
	    }
	}
	
	public function logout(RequestObject $request) {
	    $request->sessionSet('csrtoken',random_int(1000000,9999999));
	    $request->sessionSet('adminNick','');
	    ?>
	    <script type="text/javascript">
			window.location = "./index.php";
	    </script>
	    <?php
	}
	
	public function appremove(RequestObject $request) {
	    // check csrtoken
	    $this->checkCsrToken($request);
	    
	    $hackModel = $this->getModel('hack');
	    if (!$hackModel->checkEnabled($_SERVER['REMOTE_ADDR'])) {
	        echo '<p>Disabled IP ';
	        exit;
	    }
	    
	    // csrtoken ok, ip enabled
	    // hacker hibaszámláló nullázása
	    $hackModel->ipEnable($_SERVER['REMOTE_ADDR']);
	    
	    $msg = '';
	    $model = $this->getModel('appregist');
	    $view = $this->getView('appregist');
	    $rec = $model->getData($request->input('client_id',''));
	    if (!isset($rec->error)) {
	        $msg = $model->remove($rec->client_id);
	        if ($msg == '') {
	            $rec->adminNick = $request->sessionGet('adminNick');
	            $view->removedMsg($rec);
	        } else {
	            $rec->adminNick = $request->sessionGet('adminNick');
				$rec->error = $msg;	            
				$view->errorMsg([$rec->error],'','',true);
	        }
	    } else {
	        $rec = new AppRecord();
            $rec->adminNick = $request->sessionget('adminNick');
			$rec->error = 'ERROR_NOTFOUND';	            
			$view->errorMsg([$rec->error],'','',true);
	    }
	}
	
}
?>