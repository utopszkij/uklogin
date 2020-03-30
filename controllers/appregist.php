<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

/** AppregistController osztály applikáció kezelés */
class AppregistController extends Controller {

    /** controller neve */
    protected $cName = 'appregist';

    /**
     * task init
     * {@inheritDoc}
     * @param Request $request
     * @param array $fields
     * @return Params
     */
    protected function init(Request &$request, array $fields = array()): Params {
        $result = parent::init($request, $fields);
        if (!$result->loggedUser) {
            $this->getModel('openid');
            $result->loggedUser = new UserRecord();
        }
        $result->nickName = $result->loggedUser->nickname;
        $result->adminNick = $result->loggedUser->nickname;
        return $result;
    }

	/**
	 * új app felvivő képernyő kirajzolása
	 * sessionban jöhet adminNick
	 * @param object $request
	 */
    public function add(Request $request) {
		$request->set('lng','hu');
		$data = $this->init($request,[]);
		// ha a sessionban nincs adminNick akkor az admin nincs bejelentkezve,
		// bejelentkezési popup kirajzolása
        if ($request->sessionGet('adminNick') == '') {
            $redirect_uri = MYDOMAIN.'/opt/default/logged/';
            $url = MYDOMAIN.'/openid/authorize/'.
                '?client_id='.urlencode($redirect_uri).
                '&redirect_uri='.urlencode($redirect_uri).
                '&response_type='.urlencode('token id_token').
                '&nonce='.urlencode(MYDOMAIN.'/opt/appregist/add').
                '&state=0'.
                '&scope='.urlencode('nickname');
            redirectTo($url);
            return;
		}
		$this->createCsrToken($request, $data);
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
		$data->pubkey = $request->input('pubkey','');
		$data->policy = $request->input('policy','');
		$data->scope = $request->input('scope','');
		$data->jwe = $request->input('jwe',0);
		$data->pubkeyplaceholder = "-----BEGIN PUBLIC KEY-----\n....\n....\n-----END PUBLIC KEY-----";
		if ($data->jwe == 0) {
		    $data->jwe0selected = ' selected="selected"';
		    $data->jwe1selected = '';
		} else {
		    $data->jwe1selected = ' selected="selected"';
		    $data->jwe0selected = '';
		}
		$data->adminFalseLoginLimit = $request->input('adminFalseLoginLimit','5');
		$data->adminLoginEnabled = $request->input('adminLoginEnabled','1');
		$data->adminNick = $request->sessionGet('adminNick','');
		$data->access_token = $request->sessionGet('access_token','');
		$request->sessionSet('adminNick',$data->adminNick);
		$this->view->form($data);
	}

	/**
	 * do save into database (insert or update)
	 * sessinban van az adminNick
	 * @param object $request form fields
	 */
	public function save(Request $request) {
	    // check csrtoken
	    $this->init($request, []);
	    $this->checkCsrToken($request);
	    // $data kialakitása a $request -ből
	    $data = new AppRecord();
	    $data->id = $request->input('id','');
        $data->name = $request->input('name','');
        $data->client_id = $request->input('client_id','');
        $data->client_secret = $request->input('client_secret','');
        $data->domain = $request->input('domain','');
        $data->callback = $request->input('callback','');
        $data->admin  = $request->sessionGet('adminNick','');
        $data->dataProcessAccept = $request->input('dataProcessAccept',0);
        $msg = $this->model->check($data);
	    if (count($msg) == 0) {
	        $res = $this->model->save($data);
	        if (!isset($res->error)) {
	            $msgs = [txt('APPSAVED')];
	            $msgs[] = 'client_id:'.$data->client_id;
	            $msgs[] = txt('ADMININFO');
	            $this->view->successMsg($msgs, '', '', true);
	        } else {
	            $this->view->errorMsg($res->error,'','',true);
	        }
	    } else {
	        $request->set('msg',$msg);
	        $this->add($request);
	    }
	}

	/**
	 * adminisztrátor applikációinak megjelenítése
	 * @param object $data
	 * @param Request $request
	 * @param AppRecord $rec
	 * @param View $view
	 */
	protected function echoAdminForm(&$data, Request $request, $rec, View $view) {
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
    	$data->access_token  = $request->sessionGet('access_token','');
    	$this->view->form($data);
	}

	/**
	 * client_id keresése az appas -ek között
	 * @param array $apps
	 * @param string $client_id
	 * @return AppRecord | false
	 */
	protected function findApp(array $apps, string $client_id) {
	    $result = false;
	    if ($client_id == '') {
	        // az első applikáció adatainak megjelenitése
	        $result = $apps[0];
	    } else {
	        // megkeressük a request-ben érkezett client_id -t az apps -ben és azt jelenitjük meg
	        foreach ($apps as $app) {
	            if ($app->client_id == $client_id) {
	                $result = $app;
	            }
	        } // foreach
	   }
	   return $result;
	}

	/**
	 * sikeres admin login után echo admin form
	 * sessionban érkezik az adminNick, request-ben érkezhet client_id
	 * @param object $request
	 */
	public function adminform(Request $request) {
	    $data = $this->init($request, []);
	    $adminNick = $request->sessionGet('adminNick');
        $request->sessionSet('adminNick',$adminNick);
	    if ($adminNick == '') {
	        $this->view->errorMsg(['FATAL_ERROR'],'','',true);
	    }
	    $request->set('sessionid','0');
	    $request->set('lng','hu');
	    $this->createCsrToken($request, $data);
	    // adminNick összes app rekordjának beolvasása
	    $data->apps = $this->model->getAppsByAdmin($adminNick);
	    $data->adminNick = $request->sessionGet('adminNick','');
	    $data->access_token  = $request->sessionGet('access_token','');

	    if (count($data->apps) == 0) {
	        // nincsen ennek az adminnak applikációi
	        $this->view->errorMsg(['ERROR_APP_NOTFOUND'],'','',true);
	    } else {
	        $client_id = $request->input('client_id','');
	        $app = $this->findApp($data->apps, $client_id);
	        if ($app) {
	            $data->client_id = $app->client_id;
	            $this->echoAdminForm($data, $request, $app, $this->view);
	        } else {
	            $this->view->errorMsg(['FATAL_ERROR'],'','',true);
	        }
	    }
	}

	/**
	 * logout végrehajtása
	 * @param Request $request
	 */
	public function logout(Request $request) {
	    $request->sessionSet('csrtoken',random_int(1000000,9999999));
	    $request->sessionSet('adminNick','');
	    $request->sessionSet('access_token','');
	    ?>
	    <script type="text/javascript">
			window.location = "./index.php";
	    </script>
	    <?php
	}

	/**
	 * applikáció törlés képernyő
	 * @param Request $request
	 */
	public function appremove(Request $request) {
	    // check csrtoken
	    $data = $this->init($request, []);
	    $this->checkCsrToken($request);
	    $msg = '';
	    $rec = $this->model->getData($request->input('client_id',''));
	    if (!isset($rec->error)) {
	        $msg = $this->model->remove($rec->client_id);
	        if ($msg == '') {
	            $rec->adminNick = $request->sessionGet('adminNick');
	            $rec->access_token = $request->sessionGet('access_token');
	            $rec->loggedUser = $data->loggedUser;
	            $this->view->successMsg([txt('APPREMOVED')], '', '', true);
	        } else {
	            $rec->error = $msg;
				$this->view->errorMsg([$rec->error],'','',true);
	        }
	    } else {
	        $rec = new AppRecord();
            $rec->error = 'ERROR_NOTFOUND';
			$this->view->errorMsg([$rec->error],'','',true);
	    }
	}

}
?>
