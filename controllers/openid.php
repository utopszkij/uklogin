<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/phpmailer/src/Exception.php';
require './vendor/phpmailer/src/PHPMailer.php';
require './vendor/phpmailer/src/SMTP.php';

class Params {
    public $msgs = [];
}

class OpenidUserController extends Controller {
    
    /**
     * task init - logged User és kapott paraméterek a result Params -ba
     * @param Request $request
     * @param array $names elvért paraméter nevek
     * @return Params
     */
    protected function init(Request $request, array $names = []): Params {
        $result = new Params();
        $result->loggedUser = $request->sessionGet('loggedUser', JSON_decode('{"id":0, "nicname":"guest"}'));
        // tényleges érkezett paraméterek
        foreach ($request->params as $fn => $fv) {
            $result->$fn = $fv;
        }
        // az elvárt, de nem érkezett paraméterek '' értékkel
        foreach ($names as $name) {
            if (!isset($result->$name)) {
                $result->$name = '';
            }
        }
        return $result;
    }
    
    /**
     * sikeres login végrehajtása
     * @param UserRecord $userRec
     * @param string $redirect_uri
     * @param string $state
     * @param string $nonce
     * @return void
     */
    protected function successLogin($userRec, string $redirect_uri,
                                    string $state, string $nonce) {
       if ($redirect_uri != '') {
           
           // id token kialakitása 
           $tokenHead = base64_encode('{"typ":"JWT", "alg":"SHA256"}');
           $tokenClam = new StdClass();
           $tokenClam->sub = $userRec->code;
           $tokenClam->iss = $userRec->nickname;
           $tokenClam->aud = 0;
           $tokenClam->nonce = $nonce;
           $tokenClam->auth_time = time();
           $tokenClam->exp = time() + (config('CODE_EXPIRE'));
           $tokenPlan = base64_encode(JSON_encode($tokenClam));
           $tokenHash = hash('sha256',$tokenHead.$tokenPlan);
           $id_token = $tokenHead.'.'.$tokenPlan.'.'.$tokenHash;
           if (strpos($redirect_uri, '?') > 0) {
               $redirect_uri .= '&state='.$state;
           } else {
               $redirect_uri .= '?state='.$state;
           }
           $redirect_uri .= '&nonce='.$nonce.
           '&id_token='.$id_token.
           '&token='.session_id().
           '&access_token='.session_id();
           
           // '&token='.$userRec->code;
           // '&access_token='.$userRec->code;
           redirectTo($redirect_uri);
       } else {
           echo 'success login '.$userRec->nickname;
       }
    }
    
    /**
     * chek doregist' params, result into $p->msgs
     * @param Params $p
     * @return void - $p->msgs 
     */
    protected function doregistCheck(Params &$p) {
        // ellenörzések
        if ($p->nick == '') {
            $p->msgs[] = txt('NICK_REQUIRED');
        }
        if ($p->email == '') {
            $p->msgs[] = txt('EMAIL_REQUIRED');
        }
        if ($p->psw1 != $p->psw2) {
            $p->msgs[] = txt('PASSWORDS_NOTEQUALS');
        }
        if ($p->dataprocessaccept != 1) {
            $p->msgs[] = txt('DATAPROCESS_ACCEPT_REQUIRED');
        }
        if (($p->id == 0) & ($p->psw1 == '')) {
            $p->msgs[] = txt('PSW_REQUIRED');
        }
    }
    
    /**
     * openid technikai paraméterek ellenörzése
     * @param Params $p
     */
    protected function openidCheck(Params &$p) {
        if ($p->response_type == 'id_token token') {
            $p->response_type = 'token id_token';
        }
        if ($p->response_type != 'token id_token') {
            $p->msgs[] = 'only "token id_token" response_type supported';
        }
        $enabledScope1 = ['sub', 'nickname', 'eamil', 'email_verified', 'sysadmin', 'posta_code', 'locality'];
        $enabledScope2 = ['sub', 'openid',
            'nickname', 'email', 'email_verified', 'given_name', 'middle_name', 'family_name',
            'name', 'picture', 'street_addres', 'locality', 'postal_code', 'addres',
            'birth_date', 'phone_number', 'phone_number_verified',
            'updated_at', 'audited', 'auditor', 'audit_time', 'sysadmin'
        ];
        $w = explode(' ',$p->scope);
        $ok = true;
        if (config('OPENID') == '1') {
            $enabledScope = $enabledScope1;
        } else {
            $enabledScope = $enabledScope2;
        }
        foreach ($w as $scopeItem) {
            if (array_search($scopeItem, $enabledScope) === false) {
                $ok = false;
            }
        }
        if (!$ok) {
            $p->msgs[] = txt('NOT_SUPPORTED_SCOPE').':'.$p->scope.
            ' '.txt('ENABLED_SCOPE').':'.JSON_encode($enabledScope);
        }
        if ($p->redirect_uri == '') {
            $p->msgs[] = 'redirect_uri empty';
        }
    }
    
    /**
     * UserRecord feltöltése Params és PdfData -ból
     * @param Params $p
     * @param PdfData $pdfData
     * @return UserRecord
     */
    protected function fillUserRecord(Params $p, $pdfData): UserRecord {
        $userRec = new UserRecord();
        $nameItems = explode(' ',$pdfData->xml_viseltNev);
        $nameItems[] = '';
        $nameItems[] = '';
        $nameItems[] = '';
        
        if ($pdfData->txt_tartozkodas != '') {
            $addressItems = explode(' ',$pdfData->txt_tartozkodas,3);
        } else {
            $addressItems = explode(' ',$pdfData->txt_address,3);
        }
        $addressItems[] = '';
        $addressItems[] = '';
        $addressItems[] = '';
        
        $userRec->id = $p->id; // *
        $userRec->sub = hash('sha256',rand(10000,99999));  // * hash
        $userRec->nickname = $p->nick; // * bejelentkezési név
        $userRec->pswhash = hash('sha256', $p->psw1); // * jelszó sha256 hash
        $userRec->locality = $addressItems[1]; // * település
        $userRec->postal_code = $addressItems[0]; // * irányító szám
        $userRec->audited = 1; // * hitelesített vagy nem?
        $userRec->auditor = 'ügyfélkapu'; // * auditor nick name vagy "ugyfélkapu"
        $userRec->audit_time = time(); // * auditálás időpontja
        $userRec->code = hash('sha256',$pdfData->xml_szuletesiNev.
            $pdfData->xml_anyjaNeve.$pdfData->xml_szuletesiDatum); // * hash(origname.mothersname,birth_date)
        $userRec->signdate = $pdfData->xml_alairasKelte; // *
        $userRec->sysadmin = 0; // *
        $userRec->email = $p->email; // * email
        $userRec->email_verified = 0; // * email ellnörzött?
        $userRec->updated_at = time(); // utolsó modosítás timestamp
        $userRec->created_at = time();
        if (config('OPENID') == 2) {
           $userRec->given_name = $nameItems[2]; // első keresztnév
           $userRec->middle_name = $nameItems[1]; // második keresztnév
           $userRec->family_name = $nameItems[0]; // családnév
           $userRec->mothersname = $pdfData->xml_anyjaNeve; // anyja neve
           $userRec->phone_number = $p->phone_number; // telefonszám
           $userRec->phone_number_verified = 0; // telefonszám ellenörzött?
           $userRec->street_address = $addressItems[2]; //  utca, házszám, emelet
           $userRec->birth_date = $pdfData->xml_szuletesiDatum; // születési dátum timestram
           $userRec->gender = $p->gender; // 'man' vagy 'woman'
           $userRec->picture = ''; // avatar kép uri
           $userRec->profile = ''; // profil web uri
           $userRec->origname = $pdfData->xml_szuletesiNev;
        }
        return $userRec;     
    }
    
    /**
     * login képernyő feldolgozása, sikeres login esetén:
     * sessionba tárolja a loggedUser -t
     * ugrik a redirect_uri -ra
     * csrtoken ellenörzés
     * @param Request $request - login form mezői, 
     *    sessionban client_id, scope, policy_uri, redirect_uri, state, nonce, csrToken 
     *    csrToken
     */
    public function dologin(Request $request) {
        $p = $this->init($request,['nickname','psw','dataprocessaccept']);
        $this->checkCsrToken($request);
        $this->createcsrToken($request, $p);
        $p->dataprocessaccept = $request->input('dataprocessaccept');
        $model = $this->getModel('openid');
        $view = $this->getView('openid');
        $redirect_uri = $request->sessionGet('redirect_uri');
        $nonce = $request->sessionGet('nonce');
        $state = $request->sessionGet('state');
        $client = $model->getApp($request->sessionGet('client_id'));
        if ($p->nickname == '' ) {
            $p->msgs[] = txt('NICK_REQUIRED');
        }
        if ($p->psw == '' ) {
            $p->msgs[] = txt('PSW_REQUIRED');
        }
        if ($p->dataprocessaccept != 1) {
            $p->msgs[] = txt('DATAPROCESS_ACCEPT_REQUIRED');
        }
        if (count($p->msgs) == 0) {
            $userRec = $model->getUserByNick($p->nickname);
            if ($userRec->id > 0) {
                if ($userRec->pswhash != hash('sha256', $p->psw)) {
                    $p->msgs[] = txt('INVALID_LOGIN');  // hibás jelszó
                }
            } else {
                $p->msgs[] = txt('INVALID_LOGIN'); // nincs meg a user rekord
            }
        }
        if (count($p->msgs) > 0) {
            // loginform hibaüzenettel
            $p->clientTitle = $client->name;
            $p->scope = $request->sessionget('scope');
            $p->policy_uri = $request->sessionGet('policy_uri');
            $view->loginForm($p);
        } else {
            $request->sessionSet('loggedUser', $userRec);
            // sessionból törli az ott tárol openid paraméterket a scope kivételével
            $request->sessionSet('client_id','');
            $request->sessionSet('redirect_uri','');
            $request->sessionSet('state','');
            $request->sessionSet('nonce','');
            $request->sessionSet('policy_uri','');
            $request->sessionSet('response_type','');
            $this->successLogin($userRec, $redirect_uri, $state, $nonce);
        }
    }
    
    /**
     * registform 2. képernyő (pdf elemzés, elelnörzés, profil képernyő megjelenítés
     * csrtoken ellenörzés
     * @param Request $request - feltöltött pdf fiile, id
     *    client_id, scope, policy_uri, redirect_uri, state, nonce, 
     *    csrToken
     * @return void
     */
    public function registform2(Request $request) {
    	$this->checkCsrToken($request);
    	$p = $this->init($request,["pdffile","id"]);
        $p->pdfName = $request->input("pdffile","alairt_pdf");
        $p->msgs = [];
        $this->createCsrToken($request, $p);
        $model = $this->getModel("openid");
        $pdfParser = $this->getModel("pdfparser");
        $view = $this->getView("openid");
        $client_id = $request->sessionGet("client_id");
        if ($client_id == 'self') {
            $client = $model->getApp($client_id);
            $p->clientTitle = $client->name;
        } else {
            $p->clientTitle = 'self';
        }
        $p->scope = $request->sessionGet("scope","");
        $p->policy_uri = $request->sessionGet("policy_uri","");
        $tmpDir = $pdfParser->createWorkDir();
		$pdfFilePath = $tmpDir.'/'.getUploadedFile($p->pdfName, $tmpDir);
		if (file_exists($pdfFilePath)) {
		  $pdfData = $pdfParser->parser($pdfFilePath);
		} else {
		  $pdfData = new PdfData();
		  $pdfData->error = 'not found '.$pdfFilePath.' pdfName='.$p->pdfName;
		}
		$p->email = $pdfData->xml_ukemail;
		$p->phone_number = '';
		$p->szuletesiNev = $pdfData->xml_szuletesiNev;
		$p->szuletesiDatum = $pdfData->xml_szuletesiDatum;
		$p->anyjaNeve = $pdfData->xml_anyjaNeve;
		$p->address = $pdfData->txt_address;
		$p->id = 0;
		$pdfParser->clearFolder($tmpDir);
		if ($pdfData->error == "") {
		    $request->sessionSet('pdfData',JSON_encode($pdfData));
			$view->registForm2($p);
		} else {
			foreach (explode(", ",$pdfData->error) as $item) {
				$p->msgs[] = txt($item);
			}
			$view = $this->getView('pdfform');
			$p->formTitle = $p->clientTitle.'<br />Regisztráció';
			$p->okURL = config('MYDOMAIN').'/openid/registform2'; 
			$view->pdfForm($p);
		}
     }
    
    /**
     * regisztrációs form adatainak tárolása, elfelejtett jelszó form tárolása
     * bejelentkezik és ugrás a redirect_uri -ra
     * csrtoken ellenörzés
     * @param Request $request id, nickname, psw1, psw2, email, phone_number, dataprocessaccept, 
     *    csrToken
     *    sessionban: client_id, scope, policy_uri, redirect_uri, state, nonce,
     *                pdfData, csrToken 
     * @return void
     */
    public function doregist(Request $request) {
        $p = $this->init($request,['id','nick','psw1','psw2',
            'email', 'phone_number', 'dataprocessaccept', 'gender']);
        $this->checkCsrToken($request);
        $this->createCsrToken($request, $p);
        $p->id = $request->input('id',0); // az init ''-et állit be ha nem érkezik)
        
        $view = $this->getView('openid');
        $model = $this->getModel('openid');
        $pdfData = JSON_decode($request->sessionGet('pdfData'));
        $redirect_uri = $request->sessionGet('redirect_uri');
        $client = $model->getApp($request->sessionGet('client_id'));
        
        // ha $p->id > 0 akkor elfelejtett jelszó kezelés, ilyenkor
        // ennek egyeznie kell a loggedUser->id -vel
        if (($p->id > 0) & ($p->id != $p->loggedUser->id)) {
            echo 'Fatal errior id invalid'; exit(); 
        }
        
        $this->doregistCheck($p);
        
        $userRec = $model->getUserByNick($p->nick);
        if ($userRec->id > 0) {
            if ($userRec->id != $p->id) {
                $p->msgs[] = txt('NICK_EXISTS');
            }
        }
        
        
        if (!is_object($pdfData)) {
            $p->msgs[] = txt('ACCESS_VIOLATION').' pdfdata empty';
            $p->name = '';
            $p->address = '';
            $p->szuletesiNev = '';
            $p->szuletesiDatum = '';
            $p->anyjaNeve = '';
            $p->gender = '';
        } else {
            $p->name = $pdfData->xml_nev;
            $p->address = $pdfData->txt_address;
            $p->szuletesiNev = $pdfData->xml_szuletesiNev;
            $p->szuletesiDatum = $pdfData->xml_szuletesiDatum;
            $p->anyjaNeve = $pdfData->xml_anyjaNeve;
            $p->gender = $request->input('gender');
        }
        if (count($p->msgs) == 0) {
            $userRec = $this->fillUserRecord($p, $pdfData);
            $res = $model->getUserByCode($userRec->code);
            if (($res->id == 0) | ($res->id == $userRec->id)) {
                $s = $model->saveUser($userRec);
                if ($s != '') {
                    $p->msgs[] = $s;
                }
            } else {
                $p->msgs[] = txt('SIGN_HASH_EXISTS').' :'.$res->nickname;
            }
        }
        if (count($p->msgs) == 0) {
            if ($p->id == 0)  {
                $url = config('MYDOMAIN').'/opt/openid/emailverify/code/'.$userRec->code;
                $subject = $client->name.' email hitelesités';
                $body = '<p>Az email hitelesítéséhez kattints az alábbi linkre</p>'.
                    '<p><a href="'.$url.'">'.$url.'</a></p>';
                if ($userRec->email != '') {
                    sendEmail($userRec->email, $subject, $body);
                }
            }
            $request->sessionSet('loggedUser', $userRec);
                $this->successLogin($userRec, $redirect_uri,
                    $request->sessionGet('state'), $request->sessionGet('nonce'));
        } else {
                $p->clientTitle = $client->name;
                $p->scope = $request->sessionGet('scope');
                $p->policy_uri = $request->sessionGet('policy_uri');
                $p->nick = '';
                $view->registForm2($p);
        }
    }
    
    /**
     * scopeForm adatainak tárolása
     * ugrás a redirect_uri -ra
     * csrtoken ellenörzés
     * @param Request $request - dataprocessaccept,
     *    csrToken
     *    sessionban: client_id, scope, policy_uri, redirect_uri, state, nonce,
     *                pdfData, csrToken
     * @return void
     */
    public function doscopeform(Request $request) {
        $p = $this->init($request,['dataprocessaccept']);
        $this->checkCsrToken($request);
        $this->createCsrToken($request, $p);
        $view = $this->getView('openid');
        $model = $this->getModel('openid');
        if ($p->loggedUser->id == 0) {
            $p->msgs[] = txt('ACCESS_VIOLATION');
            $view->errorMsg($p->msgs);
            return;
        }
        $p->nickname = $p->loggedUser->nickname;
        $redirect_uri = $request->sessionGet('redirect_uri');
        $client = $model->getApp($request->sessionGet('client_id'));
        if ($p->dataprocessaccept == 1) {
            $this->successLogin($p->loggedUser, $redirect_uri,
                $request->sessionGet('state'), $request->sessionGet('nonce'));
        } else {
            $request->sessionSet('loggedUser', new UserRecord());
            $p->clientTitle = $client->name;
            $p->scope = $request->sessionGet('scope');
            $p->policy_uri = $request->sessionGet('policy_uri');
            $p->nickname = '';
            $p->psw = '';
            $view->loginForm($p);
        }
    }
    
    /**
     * elfelejtett jelszó kezelés pdfform kirajzolás, nickname sessionba mentése
     * csrtoken ellenörzés
     * @param Request $request - nickname, csrToken
     * @return void
     */
    public function forgetpswform(Request $request) {
        $p = $this->init($request, ['nickname']);
        $p->msgs = [];
        $this->createCsrToken($request, $p);
        $view = $this->getView('openid');
        $model = $this->getModel('openid');
        if ($p->nickname != '') {
            $user = $model->getUserByNick($p->nickname); 
            if ($user->id > 0) {
                // új jelszó kreálása
                $newPsw = 'psW'.random_int(10000, 99999);
                $user->pswhash = hash('sha256', $newPsw); // * jelszó sha256 hash
                $model->saveUser($user);
                // jelszó küldése
                $subject = config('MYDOMAIN').' új jelszó';
                $body = '<p>Új jelszó:</p>'.
                    '<p><strong>'.$newPsw.'</strong></p>';
                if ($user->email != '') {
                    sendEmail($user->email, $subject, $body);
                    $p->msgs[] = txt('NEW_PSW_SENDED');
                    $view->successMsg($p->msgs, true);
                } else {
                    $p->msgs[] = txt('EMPTY_EMAIL');
                    $view->errorMsg($p->msgs,'','',true);
                }
            } else {
                $p->msgs[] = txt('NICK_NOT_FOUND');
                $view->errorMsg($p->msgs,'','',true);
            }
        } else {
            $p->msgs[] = txt('NICK_REQUIRED');
            $view->errorMsg($p->msgs,'','',true);
        }
    }
    
    /**
     * email ellenörzö link kattintás feldolgozója
     * @param Request $request - code
     */
    public function emailverify(Request$request) {
        $code = $request->input('code');
        $model = $this->getModel('openid');
        $view = $this->getView('openid');
        $user = $model->getUserByCode($code);
        unset($user->pswhash);
        if ($user->id > 0) {
            $user->email_verified = 1;
            $model->saveUser($user);
            $view->successMsg([txt('EMAIL_VERIFIED')]);
        } else {
            echo 'fatal error';
        }
    }
    
    public function profilesave(Request $request) {
        $p = $this->init($request,['id','email','phone_number',
            'gender','picture','profile','psw1','psw2']);
        $this->checkCsrToken($request);
        $model = $this->getModel('openid');
        $view = $this->getView('openid');
        $loggedUser = $request->sessionGet('loggedUser', new UserRecord());
        if (($loggedUser->id > 0) & ($loggedUser->id == $p->id)) {
            $loggedUser = $model->getUserByNick($loggedUser->nickname);
            if ($p->psw1 != '') {
                $loggedUser->pswhash = hash('sha256',$p->psw1);
            } else {
                unset($loggedUser->pswhash);
            }
            foreach ($loggedUser as $fn => $fv) {
                if (isset($p->$fn)) {
                    $loggedUser->$fn = $p->$fn;
                } else {
                    $loggedUser->$fn = $fv;
                }
            }
            $msg =  $model->saveUser($loggedUser);
            $request->sessionSet('loggedUser', $loggedUser);
            if ($msg == '') {
                $view->successMsg([txt('PROFILE_SAVED')]);
            } else {
                $view->errorMsg([txt($msg)]);
            }
        } else {
            $view->errorMsg([txt('ACCESS_VIOLATION')]);
        }
    }
    
    public function mydata(Request $request) {
        $this->init($request,[]);
        $this->checkCsrToken($request);
        $model = $this->getModel('openid');
        $view = $this->getView('openid');
        $loggedUser = $request->sessionGet('loggedUser', new UserRecord());
        if ($loggedUser->id > 0) {
            $loggedUser = $model->getUserByNick($loggedUser->nickname);
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo JSON_encode($loggedUser,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
        } else {
            $view->errorMsg([txt('ACCESS_VIOLATION')]);
        }
    }
    
    public function delaccount(Request $request) {
        $this->init($request,[]);
        $this->checkCsrToken($request);
        $model = $this->getModel('openid');
        $view = $this->getView('openid');
        $loggedUser = $request->sessionGet('loggedUser', new UserRecord());
        if ($loggedUser->id > 0) {
            $loggedUser = $model->getUserByNick($loggedUser->nickname);
            
            // user rekord update
            $user = new UserRecord();
            $user->id = $loggedUser->id;
            $user->nickname = 'törölt';
            $user->pswhash = hash('sha256', rand(10000000,99999999));
            $user->audited = $loggedUser->audited;
            $model->saveUser($user);
            
            // kijelentkezés
            $user->id = 0;
            $request->sessionSet('loggedUser',$user);
            $view->successMsg([txt('ACCOUNT_DELETED')]);
        } else {
            $view->errorMsg([txt('ACCESS_VIOLATION')]);
        }
    }
    
    
    
} // OpenidUserController

class OpenidController extends OpenidUserController {

	/**
	 * openid végpont Bejelentkezés
	 * paramétereket sessionba tárolja
	 * ha loggedUser van akkor scopeForm megjelenités
	 * ha nincs loggedUser akkor login képernyőt jelenít meg
	 * Ha client_id=='self' ajkkor magába az openid szolgáltatásba jelentkezik be
	 * ilyenkor nincs scope elfogadtatás.    
	 * @param Request $request
	 *   client_id - regisztrált kliens azonosító vagy redirect_uri vagy "self"
     *   scope - a kliens által kért adatok szóközzel szeparált listája 
     *     GDPR == false verzióba max 'nickname postal_code locality street_address' 
     *     GDPR == true verzióban max
     *       'nickname email email_verified given_name middle_name family_name
     *       name picture street_addres locality postal_code address
     *       birth_date phone_number phone_number_verified 
     *       updated_at audited auditor audit_time sysadmin' közül
     *   redirect_uri - sikeres bejelentkezés után visszahivandó cím
     *   state - opcionális paraméter, a redirect_uri ezt is megkapja
     *   nonce - opcionális paraméter, a redirect_uri ezt is megkapja
     *   response_type - csak 'id_token token' elfogadott
     *   policy_uri - kliens adatkezelési leírása
	 * @return void
	 */
    public function authorize(Request $request) {
        $p = $this->init($request, ['client_id','redirect_uri','scope',
            'state','nonce','policy_uri','response_type']);
        $p->response_type = $request->input('response_type','id_token token');
        $p->scope = $request->input('scope','nickname postal_code locality');
        $p->redirect_uri = $request->input('redirect_uri', config('MYDOMAIN'));
        $p->client_id = $request->input('client_id', config('MYDOMAIN'));
        
        $view = $this->getView('openid');
        $model = $this->getModel('openid');
        $request->sessionSet('client_id',$p->client_id);
        $request->sessionSet('redirect_uri',$p->redirect_uri);
        $request->sessionSet('scope',$p->scope);
        $request->sessionSet('state',$p->state);
        $request->sessionSet('nonce',$p->nonce);
        $request->sessionSet('policy_uri',$p->policy_uri);
        $request->sessionSet('response_type',$p->response_type);

        // ellenörzések
        $this->openidCheck($p);
        if (count($p->msgs) > 0) {
            $view->errorMsg($p->msgs);
            return;
        }
        if ($p->client_id == 'self') {
            $p->clientTitle = 'self';    
        } else {
            $client = $model->getApp($p->client_id);
            $p->clientTitle = $client->name;
        }
        if ($p->loggedUser->id > 0) {
            $p->nickname = $p->loggedUser->nickname;
            $p->msgs = [];
            if ($p->clientTitle != 'self') {
                $this->createCsrToken($request, $p);
                $view->scopeForm($p);
            } else {
                $this->successLogin($p->loggedUser, 
                    $request->sessionGet('redirect_uri'),
                    $request->sessionGet('state'), 
                    $request->sessionGet('nonce'));
            }
        } else {
            $p->nickname = '';
            $p->psw = '';
            $this->createCsrToken($request, $p);
            $view->loginForm($p);
        }
	}

	/**
	 * openid végpont user információk kérése
	 * json formában a sessionban lévő scope -ban kért adatokat adja vissza
	 * @param Request $request - access_token
	 * @return void
	 */
	public function userinfo(Request $request) {
	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    $model = $this->getModel('openid');
	    $access_token = $request->input('access_token');
	    
	    // access_token paraméterben a session_id érkezett, 
	    // sessionhoz kapcsolódás
	    // a loggedUser a sessionban van.

	    if ($access_token != session_id()) {
	       session_abort();
	       session_id($access_token);
	       $request = new Request();
	    }
	    $user = $request->sessionGet('loggedUser', new UserRecord());
	    $scope = $request->sessionGet('scope');
	  
	    
	    if ($user->id > 0) {
    	    $w = explode(' ',
    	        str_replace('openid',
    	            'sub nickname address email email_verified name '.
    	            'picture birth_date phone_number phone_number_verified updated_at',
    	            $scope));
    	    
    	    echo '{';
    	    foreach ($w as $item) {
    	        if ($item == 'sub') {
    	            echo '"sub":"'.$user->id.'",';
    	        } else if ($item == 'name') {
    	            if ($user->middle_name == '') {
    	                echo '"name":"'.$user->family_name.' '.$user->given_name.'",';
    	            } else {
    	                echo '"name":"'.$user->family_name.' '.$user->middle_name.' '.$user->given_name.'",';
    	            }
    	        } else if ($item == 'address') {
    	            echo '"address":"'.$user->postal_code.' '.$user->locality.' '.$user->street_address.'",';
    	        } else if (isset($user->$item)) {
    	            echo '"'.$item.'":"'.$user->$item.'",';
    	        }
    	    }
    	    echo '"time":"'.date('Y.m.d h:i:s').'"';
    	    echo '}';
	    } else {
	        echo '{"error":"not found"}';
	    }
	}

	/**
	 * openid végpont - kijelentkezés
	 * @param Request $request
	 *    token_type_hint = 'access_token'
	 *    token
	 *    redirect_uri
	 * @return void
	 */
	public function logout(Request $request) {
	    $access_token = $request->input('token');
	    $redirect_uri = $request->input('redirect_uri');
	    if ($access_token != session_id()) {
    	   session_abort();
	       session_id($access_token);
	       $request = new Request();
	    }
	    $model = $this->getModel('openid');
	    $request->sessionSet('loggedUser', new UserRecord());
	    if ($redirect_uri != '') {
	        redirectTo($redirect_uri);
	    } else {
	        echo '{"info":"success logout"}';
	    }
	}
	
	/**
	 * openid végpont - token frissitése
	 * @param Request $request
	 *    token_type_hint = 'access_token'
	 *    token
	 *    redirect_uri
	 * @return void
	 */
	public function refresh(Request $request) {
	    $access_token = $request->input('token');
	    $redirect_uri = $request->input('redirect_uri');
	    if ($access_token != session_id()) {
    	    session_abort();
	        session_id($access_token);
	        $request = new Request();
	    }
	    $model = $this->getModel('openid');
	    $request->sessionSet('loggedUser', $request->sessionGet('loggedUser', new UserRecord()));
	    if ($redirect_uri != '') {
	        redirectTo($redirect_uri);
	    } else {
	        echo '{"info":"success refresh. access_token= '.$access_token.'"}';
	    }
	}

	/**
	 * openid végpont - kijelentkezés
	 * @param Request $request
	 *    token_type_hint = 'access_token'
	 *    token
	 *    redirect_uri
	 * @return void
	 */
	public function revoke(Request $request) {
	    $this->logout($request);
	}
	
	/**
	 * bejelentkezett user profil adatait jeleníti meg editálható formban
	 * @param Request $request - none
	 */
	public function profileform(Request $request) {
	    $p = $this->init($request, []);
	    $this->createCsrToken($request, $p);
	    $model = $this->getModel('openid');
	    $view = $this->getView('openid');
	    $user = $request->sessionGet('loggedUser', new UserRecord());
	    if ($user->id > 0) {
	        $user = $model->getUserByNick($user->nickname);
	        foreach ($user as $fn => $fv) {
	            $p->$fn = $fv;
	        }
	        $view->profileform($p);
	    } else {
	        $view->errorMsg([txt('ACCESS_VIOLATION')]);
	    }
	}
	
	/**
	 * openid végpont - regisztráció első képernyő (pdf feltöltés lésd pdfform)
	 * @param Request $request 
	 *      client_id, scope, policy_uri, redirect_uri, state, nonce, 
	 * @return void
	 */
	public function registform(Request $request) {
	    $p = $this->init($request, ['client_id','redirect_uri','scope',
	        'state','nonce','policy_uri','response_type']);
	    $p->response_type = $request->input('response_type','id_token token');
	    
	    $p->scope = $request->input('scope',
	        $request->sessionGet('scope','nickname postal_code locality'));
	    $p->redirect_uri = $request->input('redirect_uri', 
	        $request->sessionGet('redirect_uri',config('MYDOMAIN')));
	    $p->client_id = $request->input('client_id', 
	        $request->sessionGet('client_id',config('MYDOMAIN')));
	    
	    $this->createCsrToken($request, $p);
	    $view = $this->getView('openid');
	    $model = $this->getModel('openid');
	    $request->sessionSet('client_id',$p->client_id);
	    $request->sessionSet('redirect_uri',$p->redirect_uri);
	    $request->sessionSet('scope',$p->scope);
	    $request->sessionSet('state',$p->state);
	    $request->sessionSet('nonce',$p->nonce);
	    $request->sessionSet('policy_uri',$p->policy_uri);
	    $request->sessionSet('response_type',$p->response_type);
	    
	    // ellenörzések
	    $this->openidCheck($p);
	    if (count($p->msgs) > 0) {
	        $view->errorMsg($p->msgs);
	        return;
	    }
	    if ($p->client_id == 'self') {
	        $p->clientTitle = 'self';
	    } else {
	       $client = $model->getApp($p->client_id);
	       $p->clientTitle = $client->name;
	    }
	    $request->sessionSet('token','0');
	    $request->sessionSet('userinfo',urldecode($request->input('userinfo','')));
	    $p->okURL = config('MYDOMAIN').'/opt/openid/registform2"}';
	    if ($p->client_id == 'self') {
	        $p->formTitle = txt('REGIST_FORM');
	    } else {
	        $p->formTitle = $p->clientTitle.'<br />'.txt('REGIST_FORM');
	    }
	    $this->createCsrToken($request, $p);
	    $view = $this->getView('pdfform');
	    $view->pdfForm($p);
	}
	
	public function configuration(Request $request) {
	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    ?>
        {
            "authorization_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/authorize",
            "userinfo_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/userinfo",
            "logout_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/logout",
            "refresh_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/refresh",
            "revoke_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/revoke",
            "authorization_endpoint_params_requided: [
            	"client_id", "redirect_uri", "response_type", "scope", "state", "nonce", "policy_uri"
            ],
            "response_types_supported": [
				"token id_token",
            ],
            "id_token_signing_alg_values_supported": [
            	"SHA256"
            ],
            "unregistered_client_supported": true,
            "userinfo_endpoint_params_requided": [
            	"access_token"
            ],
            "userinfo_endpoint_format": "json";
            "userinfo_endpoint_out_encrypted": false;
            "logout_endpoint_params_supported": [
            	"token_type_hint", "token", "redirect_uri"
            ],
            "token_types_hint_supported": [
				"access_token"
            ],
            "refresh_endpoint_params_supported": [
            	"token_type_hint", "token", "redirect_uri"
            ],
            "revoke_endpoint_params_supported": [
            	"token_type_hint", "token", "redirect_uri"
            ],
            "scopes_supported": [
<?php if (config('OPENID') != 2) : ?>
				"sub",
                "nickname",
                "email",
                "email_verified",
                "postal_code",
                "locality",
                "sysadmin"
<?php endif; ?>
<?php if (config('OPENID') == 2) : ?>
				"openid",
				"sub",
                "nickname",
                "email", 
                "email_verified", 
                "given_name", 
                "middle_name", 
                "family_name",
                "name", 
                "picture", 
                "street_address", 
                "locality", 
                "postal_code", 
                "address",
                "birth_date", 
                "phone_number", 
                "phone_number_verified",
                "updated_at", 
                "sysadmin"
<?php endif; ?>
            ]
        }
        <?php
	}
	
}
?>