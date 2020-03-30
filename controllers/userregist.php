<?php
// 2020.01.30 Új aláírás rendszer
// ez az oAth2 rendszer user regisztrációja, az Openid nem ezt használja.

include 'vendor/autoload.php';
include_once 'models/appregist.php';

class UserregistController extends Controller {

    /**
     * user regist első képernyő (pdf letöltési link, aláirt pdf feltöltési form, help)
     * client_id tárolása sessionba
     * app->css -t használja
     * @param Request $request - client_id
     * @return void
     */
    public function registform(RequestObject $request) {
        $appModel = $this->getModel('appregist');
        $view = $this->getView('pdfform');
        $client_id = $request->input('client_id','?');
        $app = $appModel->getData($client_id);
        $request->sessionSet('nick','');
        if (!isset($app->error)) {
            $data = new stdClass();
            // create csr token
            $this->createCsrToken($request, $data);
            // save client_id a sessionba
            $request->sessionSet('client_id', $client_id);
            // képernyő kirajzolás
            $data->okURL = config('MYDOMAIN').'/opt/userregist/registform2';
            $data->formTitle = txt('LBL_REGISTFORM1');
            $view->pdfForm($data);
        } else {
            $view->errorMsg(['ERROR_NOTFOUND']);
        }
	}
	
	/**
	 * login képernyő újra hívása jelszó hiba esetén
	 * hibás bejelentkezés számláló modosítása, szükség esetén fiók letiltása
	 * @param object $app
	 * @param UsersModel $model
	 * @param oauth2View $view
	 * @param object $request
	 * @param object $user
	 */
	protected function pswError(&$app, &$model, &$view, &$request, &$user) {
	    $user->errorcount++;
	    if ($user->errorcount >= $app->falseLoginLimit) {
	        $user->enabled = 0;
	        $user->blocktime = date('Y-m-d H:i:s');
	    }
	    $client_id = $app->client_id;
	    $tryCount = $app->falseLoginLimit - $user->errorcount;
	    $model->updateUser($user);
	    $request->sessionSet('client_id', $client_id);
	    if ($user->enabled == 1) {
	        $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	    } else {
	        $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	    }
	}

	/**
	 * user regist első képernyő (pdf letöltési link, aláirt pdf feltöltési form, help)
	 * client_id sessionbol jön
	 * app->css -t használja
	 * @param Request $request - nick
	 * @return void
	 */
	public function forgetpsw(RequestObject $request) {
	    $request->sessionSet('nick',$request->input('nick',''));
	    $appModel = $this->getModel('appregist');
	    $view = $this->getView('pdfform');
	    $client_id = $request->sessionGet('client_id','?');
	    $app = $appModel->getData($client_id);
	    if (!isset($app->error)) {
	        $data = new stdClass();
	        // create csr token
	        $this->createCsrToken($request, $data);
	        // save client_id a sessionba
	        $request->sessionSet('client_id', $client_id);
	        // képernyő kirajzolás
	        $data->okURL = config('MYDOMAIN').'/opt/userregist/registform2';
	        $data->formTitle = txt('FORGET_PSW');
	        $view->pdfForm($data);
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	}
	
	protected function checkSignHashExist($client_id, $signHash) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = $signHash;
	    $res->nick = '';
	    $model = $this->getModel('users');
	    $user = $model->getUserBySignHash($client_id, $signHash);
	    if (!isset($user->error)) {
	        $res->error = 'ERROR_PDF_SIGN_EXISTS';
	        $res->nick = $user->nick;
	    }
	    return $res;
	}
	
	/**
	 * user egist második képernyő
	 *   (aláirt pdf feltöltés feldolgozása, nick/psw1/psw2 form)
	 * vagy forgetPsw második képernyő
	 *   (aláirt pdf feltöltés feldolgozása, nick/psw1/psw2 form)
	 * sessionban érkezik client_id
	 * ha elfelejtett jelszó miatti regisztráció ismétlés vagy jelszó modosítás akkor
	 * sessionban nick is érkezhet
	 * @param Request $request - signed_pdf, cssrtoken, nick
	 * @return void
	 */
	public function registform2(RequestObject $request) {
	    $appModel = $this->getModel('appregist');
	    $model = $this->getModel('users'); // szükség van rá, ez kreál szükség esetén táblát.
	    $pdfParser = $this->getModel('pdfparser');
	    $view = $this->getView('userregist');
	    $client_id = $request->sessionGet('client_id','');
	    $nick = $request->input('nick','');
	    $request->sessionSet('nick',$nick);
	    $forgetPswNick = $request->sessionGet('nick','');
	    if ($forgetPswNick != '') {
	        $nick = $forgetPswNick;
	    }
	    $app = $appModel->getData($client_id);
	    if (isset($app->error)) {
	        $app = new AppRecord();
	        $app->name = 'testApp';
	        $app->css = '';
	    }
	    // csrttoken ellnörzés
	    $this->checkCsrToken($request);
	    
	    // munkakönyvtár létrehozása a sessionId -t használva -> $tmpDir
	    $tmpDir = $pdfParser->createWorkDir();
	    
	    // uploaded file másolása
	    $fileName = getUploadedFile('alairt_pdf', $tmpDir);
	    if ($fileName == '') {
	        echo 'upload error'; exit();
	    }
	    $filePath = $tmpDir.'/'.$fileName;

	    // uploaded file elemzése
	    $res = $pdfParser->parser($filePath); 
	    
	    // munka könyvtár törlése
	    $pdfParser->clearFolder($tmpDir);
	    
	    if ($res->error != '') {
	        $view->errorMsg(explode(', ',$res->error));
	    } else {
	        // res->postal_code, ->locality, ->street_address, ->signHash képzése
	        $cimElemek = explode(' ',$res->txt_address,3);
	        $cimElemek[] = '';
	        $cimElemek[] = '';
	        $cimElemek[] = '';
	        $res->postal_code = $cimElemek[0];
	        $res->locality = $cimElemek[1];
	        $res->street_address = ''; // ne legyen GDPR kötele, elvileg $cimElemek[2] lenen;
	        
	        $res->signHash = hash('sha512', $res->xml_szuletesiNev.
	                                        $res->xml_szuletesiDatum.
	                                        $res->xml_anyjaNeve, false);                 
	        $request->sessionSet('postal_code',$res->postal_code);
	        $request->sessionSet('locality',$res->locality);
	        $request->sessionSet('street_address',$res->street_address);
	        $request->sessionSet('signHash', $res->signHash);
	    }
	    if (($res->error == '') && ($forgetPswNick == '')) {
	        $res = $this->checkSignHashExist($client_id, $res->signHash);
	        if ($res->error != '') {
	            $view->errorMsg([$res->error, 'nick:'.$res->nick]);
	        }
	    }
	    if (($res->error == '') && ($forgetPswNick != '')) {
	        // most azt kell megnézni azonos signHash keletkezett-e?
	        $user = $model->getUserByNick($client_id, $forgetPswNick);
	        if ($res->signHash != $user->signhash) {
	            $view->errorMsg([txt('ERROR_SIGNNOTEQUAL')]);
	        }
	    }
	    if ($res->error == '') {
	        // echo ouput form
	        $data = new stdClass();
	        $this->createCsrToken($request, $data);
	        
	        $request->sessionSet('client_id', $client_id);
	        $data->msgs = [];
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->nick = $request->input('nick','');
	        if ($forgetPswNick != '') {
	            $data->nick = $forgetPswNick;
	            $data->title = 'FORGET_PSW';
	        } else {
	            $data->title = 'LBL_REGISTFORM2';
	        }
	        $data->psw1 = '';
	        $data->psw2 = '';
	        $data->adminNick = $request->sessionGet('adminNick','');
	        $view->registForm2($data);
	    }
	}
	
	
	/**
	 * get $app és $user or result errorMsg
	 * @param object $app
	 * @param object $user
	 * @param object $userModel 
	 * @param string $client_id
	 * @param string $nick
	 * @param string $psw
	 * @return string
	 */
	protected function getAppUser(AppRecord &$app, 
	    UserRecord &$user, 
	    &$userModel,
	    string $client_id, string $nick, string $psw): string {
	    $result = '';
	    $appModel = $this->getModel('appregist');
	    if ($nick == '') {
	        $result = 'ERROR_NICK_EMPTY';
	    } else if ($psw == '') {
	        $result = 'ERROR_PSW_EMPTY';
	    } else {
	       $app = $appModel->getData($client_id);
	       $user = $userModel->getUserByNick($client_id, $nick);
	    }
	    return $result;
	}
	
	/**
	 * jelszó változtatás, mydata, delAccount akciók 
	 * sessionban érkezik a client_id
	 * @param RequestObject $request - nick, psw1, csrtoken
	 * @param string $action - "changepsw" | "mydata" | "delaccount"
	 */
	protected function userAction(RequestObject $request, $action) {
	    $model = $this->getModel('users');
	    $view = $this->getView('userregist');
	    $loginView = $this->getView('oauth2');
	    $client_id = $request->sessionGet('client_id','?');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    $app = new AppRecord();
	    $user = new UserRecord();
	    
	    $msg = $this->getAppUser($app, $user, $model, $client_id, $nick, $psw);
	    if ($msg != '') {
	        $view->errorMsg([$msg]);
	        return;
	    }
	    if ($action == 'changepsw') {
	        if ((!isset($app->error)) && (!isset($user->error)) && ($user->enabled == 1)) {
	            if ($user->pswhash == hash('sha256', $psw, false)) {
	                $data = new stdClass();
	                // create csrr token
	                $this->createCsrToken($request, $data);
	                // save client_id, nick, sighHash a sessionba
	                $request->sessionSet('client_id', $client_id);
	                $request->sessionSet('nick', $nick);
	                $request->sessionSet('signHash', $user->signhash);
	                
	                // képernyő kirajzolás
	                $data->client_id = $client_id;
	                $data->appName = $app->name;
	                $data->extraCss = $app->css;
	                $data->nick = $nick;
	                $data->title = 'CHANGE_PSW';
	                $data->msgs = [];
	                $data->psw1 = '';
	                $data->psw2 = '';
	                $data->adminNick = $request->sessionget('adminNick','');
	                $view->registForm2($data);
	            } else {
	                $this->pswError($app, $model, $loginView, $request, $user);
	            }
	        } else {
	            $view->errorMsg(['ERROR_NOTFOUND']);
	        }
	    } // changePsw
	    if ($action == 'mydata') {
	        if ((!isset($app->error)) && (!isset($user->error))) {
	            if ($user->pswhash == hash('sha256', $psw, false)) {
	                echo '<code><pre>'.JSON_encode($user,JSON_PRETTY_PRINT).'</pre></code>';
	            } else {
	                $this->pswError($app, $model, $loginView, $request, $user);
	            }
	        } else {
	            $view->errorMsg(['ERROR_NOTFOUND']);
	        }
	    } // mydata
	    if ($action == 'delaccount') {
	        if ((!isset($app->error)) && (!isset($user->error))) {
	            if ($user->pswhash == hash('sha256', $psw, false)) {
	                $request->sessionSet('client_id', $client_id);
	                $model->deleteUser($user);
	                $view->successMsg(['USER_DELETED']);
	            } else {
	                // jelszó hiba
	                $this->pswError($app, $model, $loginView, $request, $user);
	            }
	        } else {
	            $view->errorMsg(['ERROR_NOTFOUND']);
	        }
	    }
	}
	
	/**
	 * jelszó változtatás
	 * sessionban érkezik a client_id
	 * @param RequestObject $request - nick, psw1, csrtoken
	 */
	public function changepsw(RequestObject $request) {
	    $this->userAction($request, 'changepsw');
	}
	
	/**
	 * tárolt adataim
	 * sessionban érkezik a client_id
	 * @param object $request - nick, psw1, csrtoken
	 */
	public function mydata(RequestObject $request) {
	    $this->userAction($request, 'mydata');
	}

	/**
	 * fiók törlése
	 * sessionban érkezik a client_id
	 * @param object $request - nick, psw1, csrtoken
	 */
	public function deleteaccount(RequestObject $request) {
	    $this->userAction($request, 'delaccount');
	}

	/**
	 * registForm2 visszahívbása hibaüzenetekkel
	 * @param RequestObject $request
	 * @param ViewObject $view
	 * @param UserRecord $data
	 * @param Apprecord $app
	 * @param string $forgetPswNick
	 * @param array $msgs
	 */
	protected function recallRegistForm2(RequestObject &$request, 
	    ViewObject &$view, UserRecord &$data, AppRecord $app, string $forgetPswNick, array $msgs) {
    	$this->createCsrToken($request, $data);
    	if (isset($data->signHash)) {
    	    $request->sessionSet('signHash', $data->signHash);
    	}
    	$request->sessionSet('client_id', $app->client_id);
    	$data->appName = $app->name;
    	$data->extraCss = $app->css;
    	$data->msgs = $msgs;
    	$data->forgetPswNick = $forgetPswNick;
    	$data->psw1 = '';
    	$data->psw2 = '';
    	if ($forgetPswNick == '') {
    	    $data->title = 'LBL_REGISTFORM2';
    	} else {
    	    $data->nick = $forgetPswNick;
    	    $data->title = 'FORGET_PSW';
    	}
    	$data->adminNick = $request->sessionget('adminNick','');
    	$view->registForm2($data);
	}
	
	/**
	 * user Regist 2.képernyő feldolgozás)
	 * sessionban érkezik client_id, signHash,forgetPsw, cím adatok, changePsw esetén nick
	 * @param Request $request - nick, psw1, psw2, csrToken
    * @return void
	 */
	public function doregist(RequestObject $request) {
	    $appModel = $this->getModel('appregist');
	    $model = $this->getModel('users'); // szükség van rá, ez kreál táblát.
	    $view = $this->getView('userregist');

	    // csrttoken ellnörzés
	    $this->checkCsrToken($request);

	    // client_id, signHash, forgetPswNick és cím adatok sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $signHash = $request->sessionGet('signHash','');
		$postal_code = $request->sessionGet('postal_code','');
		$street_address = $request->sessionGet('street_address','');
		$locality = $request->sessionGet('locality',''); 	    
	    
	    $forgetPswNick = $request->sessionGet('nick','');
	    $app = $appModel->getData($client_id);
	    if ($signHash == '') {
	        echo '<p>invalid signHash</p>';
	        exit();
	    }
	    if ($forgetPswNick == '') {
	        $data = new UserRecord();
	    } else {
	        $data = $model->getUserByNick($client_id, $forgetPswNick);
	        if (isset($data->error)) {
	            $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick,
	                ['NOT_FOUND']);
	            return;
	        }
	    }
	    
	    // adat és cookie kezelés elfogadva?
	    if (($request->input('dataProcessAccept',0) != 1) ||
	        ($request->input('cookieProcessAccept',0) != 1)) {
	            $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick,
	                ['ERROR_DATA_ACCEP_REQUEST','ERROR_COOKIE_ACCEP_REQUEST']);
	            return;
	        }

	    // kitöltés ellenörzések
	    $data->client_id = $client_id;
	    $data->signhash = $signHash;
	    $data->nick = $request->input('nick','');
	    $data->psw1 = $request->input('psw1','');
	    $data->psw2 = $request->input('psw2','');
	    if ($forgetPswNick != '') {
	       $data->forgetPswNick = $forgetPswNick;
	       $data->nick = $forgetPswNick;
	    }
	    if (isset($data->forgetPswNick)) {
	       $request->sessionSet('nick',$data->forgetPswNick);
	    }
	    $msgs = $model->check($data);
	    if (count($msgs) > 0) {
	        $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick, $msgs);
	    } else {
	       $data->enabled = 1;
	       $data->errorcount = 0;
	       $data->code = '';
	       $data->access_token = '';
	       $data->codetime = '';
	       $data->postal_code = $postal_code;
	       $data->locality = $locality;
	       $data->street_address = $street_address;
	       
	       $data->pswhash = hash('sha256', $data->psw1, false);
	       unset($data->psw1);
	       unset($data->psw2);
	       unset($data->msgs);
	       
	       if ((!isset($data->forgetPswNick)) || ($data->forgetPswNick == '')) {
	           unset($data->forgetPswNick);
	           $data->id = 0;
	           $msgs = $model->addUser($data);
	       } else {
	           unset($data->forgetPswNick);
	           $msgs = $model->updateUser($data);
	       }
	       if (count($msgs) == 0) {
	           // sikeresen tárolva
	           $view->successMsg(['USER_SAVED']);
	 	 	   $request->sessionSet('postal_code','');		 	
		       $request->sessionSet('locality','');
		       $request->sessionSet('street_address','');
		       $request->sessionSet('signHash','');
	       } else {
	           $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick, $msgs);
	       }
	    }
	}

	/**
	 * user blokkolás feloldása
	 * @param object $request   - csrtoken, nick, client_id
	 */
	public function useractival(RequestObject $request) {
	    $this->checkCsrToken($request);
	    $client_id = $request->input('client_id','');
	    $nick = $request->input('nick','');
	    $model = $this->getModel('users');
	    $view = $this->getView('userregist');
	    $user = $model->getUserByNick($client_id, $nick);
	    if (!isset($user->error)) {
	        $user->enabled = 1;
	        $user->blocktime = '';
	        $user->errorcount = 0;
	        $model->updateUser($user);
	        $view->successMsg(['USER_ACTIVATED']);
	    } else {
	        $view->errorMsg(['NOT_FOUND']);
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
	    ViewObject &$view, AppRecord &$app, $msgs) {
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
}
?>
