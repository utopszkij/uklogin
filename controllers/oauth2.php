<?php

class Oauth2Controller extends Controller {

	/**
	 * echo loginform
	 * @param Request $request - client_id és más paraméterek is jöhetnek, ezeket sessionba tárolja
	 * @return void
	 */
	public function loginform(RequestObject $request) {
	    $request->sessionSet('nick','');
	    $appModel = $this->getModel('appregist');
	    $view = $this->getView('oauth2');
	    $client_id = $request->input('client_id','');
	    $app = $appModel->getData($client_id);
	    if (!isset($app->error)) {
	        $data = new stdClass();
	        $data->adminNick = $request->sessionget('adminNick','');
	        $this->createCsrToken($request, $data);
	        $request->sessionSet('client_id', $client_id);

	        // egyébb érkező paraméterek tárolása
	        $extraParams = [];
	        foreach ($_GET as $fn => $fv) {
	            if ($fn != 'client_id') {
	                $extraParams[$fn] = $fv;
	            }
	        }
	        foreach ($_POST as $fn => $fv) {
	            if ($fn != 'client_id') {
	                $extraParams[$fn] = $fv;
	            }
	        }
	        $request->sessionSet('extraParams',$extraParams);
	        
	        $data->appName = $app->name;
	        $data->client_id = $app->client_id;
	        $data->extraCss = $app->css;
	        $data->nick = '';
	        $data->psw1 = '';
	        $data->msgs = [];
	        $data->adminNick = $request->sessionget('adminNick','');
	        $view->loginform($data);
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	}

	/**
	 * Login képernyő ujboli kirajzolás hiba esetén
	 * @param Request $request
	 * @param ViewObject $view
	 * @param AppRecord $app
	 * @param array $msgs
	 * @return void
	 */
	protected function recallLoginForm(RequestObject &$request, 
	    ViewObject &$view, &$app, array $msgs) {
	    $data = new stdClass();
	    $this->createCsrToken($request, $data);
	    $data->appName = $app->name;
	    $data->extraCss = $app->css;
	    $data->nick = $request->input('','');
	    $data->psw1 = '';
	    $data->client_id = $app->client_id;
	    $data->msgs = $msgs;
	    $data->adminNick = $request->sessionget('adminNick','');
	    $view->loginform($data);
	}

	/**
	 * callback url kialakitása
	 * @param AppRecord $app
	 * @param UserRecord $user
	 * @param Request $request
	 * @return string
	 */
	public function getCallbackUrl(AppRecord $app, 
	    UserRecord $user, 
	    RequestObject $request): string {
    	$url = $app->callback;
    	if (strpos($url, '?') > 0) {
    	    $url .= '&';
    	} else {
    	    $url .= '?';
    	}
    	$url .= 'code='.$user->code;
    	// extra paraméterek feldolgozása
    	$extraParams = $request->sessionGet('extraParams',[]);
    	if (count($extraParams) > 0) {
    	    foreach ($extraParams as $fn => $fv) {
    	        if (($fn != 'task') && ($fn != 'client_id') &&
    	            ($fn != 'css') && ($fn != 'path') && ($fn != 'option')) {
    	           if (strpos($url, '?') > 0) {
    	                $url .= '&';
    	           } else {
    	                $url .= '?';
    	           }
    	           $url .= $fn.'='.urlencode($fv);
    	        }
    	    }
    	}
	    return $url;
	}

	/**
	 * login képernyő feldolgozása
	 * sessionban érkezik a client_id
	 * @param Request $request csrToken, nick, psw1
	 * @return string -- for unittest: return code
	 */
	public function dologin(RequestObject $request): string {

	    $this->checkCsrToken($request);
	    
	    $appModel = $this->getModel('appregist');
	    $model = $this->getModel('users'); // szükség van rá, ez kreál táblát.
	    $view = $this->getView('oauth2');
	    $client_id = $request->sessionGet('client_id','');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    $user = $model->getUserByNick($client_id, $nick);
	    $app = $appModel->getData($client_id);
	    if (isset($app->error)) {
	        // nem jó client_id van a sessionban!
	        echo '<p class="alert alert-danger">Invalid client_id</p>'; 
	        exit();
	    }

	    if (!isset($user->error)) {
	        if ($user->enabled == 0) {
	            // letiltott user, login képernyő visszahívása
	            $request->sessionSet('client_id', $client_id);
	            $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	        } else if ($user->pswhash == hash('sha256', $psw, false)) {
    	        // sikeres login, code és accessToken generálás, callback visszahívás
    	        $user->code = md5(random_int(1000000, 5999999)).$user->id;
    	        $user->access_token = md5(random_int(6000000, 9999999)).$user->id;
    	        $user->codetime = date('Y-m-d H:i:s');
    	        $user->errorcount = 0;
    	        $user->blocktime = '';
    	        $model->updateUser($user);

    	        $url = $this->getCallbackUrl($app, $user, $request);
    	        if (!headers_sent()) {
    	           header('Location: '.$url, true, 301);
    	        } else {
    	            echo 'headers sent. Not redirect '.$url;
    	            return $user->code;
    	        }
	        } else {
	            // jelszó hiba
	            $user->errorcount++;
	            if ($user->errorcount >= $app->falseLoginLimit) {
	                $user->enabled = 0;
	                $user->blocktime = date('Y-m-d H:i:s');
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            $request->sessionSet('client_id', $client_id);
	            if ($user->enabled == 1) {
	               $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	            } else {
	               $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	            }
	        }
	    } else {
	        // nick név hiba
	        $tryCount = $app->falseLoginLimit;
	        // login képernyő visszahívása
	        $request->sessionSet('client_id', $client_id);
	        $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	    }
	    return '';
	}

	/**
	 * oAuth2 backend function
	 * echo  {"access_token":"xxxxx"} vagy {"error":"xxxxxx"}
	 * @param Request $request code, client_id, client_secret
	 * @return string access_token  -- only for unittest
	 */
	public function access_token(RequestObject $request) {
	    $code = $request->input('code');
	    $client_id = $request->input('client_id');
	    $client_secret = $request->input('client_secret');
	    $appModel = $this->getModel('appregist');
	    $model = $this->getModel('users');
	    $user = $model->getUserByCode($code);

	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    if (isset($user->error)) {
	        echo '{"error":"user not found code='.$code.'"}'; exit();
	    }

	    $app = $appModel->getData($client_id);

	    if (isset($app->error)) {
	        echo '{"error":"app not found client_id='.$client_id.'"}'; exit();
	    }

	    $access_token = '';
	    if ((!isset($app->error)) && (!isset($user->error))) {
	        if (($app->client_secret == $client_secret) &&
	            ($user->enabled == 1) &&
	            ($user->client_id == $app->client_id)
	           ) {
	            $access_token = $user->access_token;
                echo '{"access_token":"'.$user->access_token.'"}';
	        } else {
	            echo '{"error":"client_secret invalid"}';
	        }
	    } else {
	        echo '{"error":"client_id or code invalid"}';
	    }
	    return $access_token;
	}

	/**
	 * oAuth2 backend function
	 * echo {"nickname":"...". "postal_code":"...", "locality":"....", street_address":"...."} vagy 
	 *      {"error":"not found"}
	 * @param Request $request   access_token
	 * @return void
	 */
	public function userinfo(RequestObject $request) {
        $access_token = $request->input('access_token');
        $model = $this->getModel('users');
        $view = $this->getView('oauth2');
        $rec = $model->getUserByAccess_token($access_token);
 
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        if (!isset($rec->error)) {
            echo '{"nick":"'.$rec->nick.'", '.
            '"postal_code":"'.$rec->postal_code.'", '.
            '"locality":"'.$rec->locality.'", '.
            '"street_address":"'.$rec->street_address.'"}';
        } else {
            $view->errorMsg(['NOT_FOUND']);
        }
	}
}
?>
