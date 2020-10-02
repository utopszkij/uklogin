<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */


//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

//require './vendor/phpmailer/src/Exception.php';
//require './vendor/phpmailer/src/PHPMailer.php';
//require './vendor/phpmailer/src/SMTP.php';
require './core/jwe.php';
require './models/appregist.php';

/** openid user kezelő osztály */
class OpenidUserController extends Controller {
    /** osztály név */
    protected $cName = 'openid';
    /** string konstans */
    protected $LOGGEDUSER = 'loggedUser';

    /** konstruktor */
    function __construct() {
        $this->getModel($this->cName); // adattábla kreálás
    }

    /**
     * task init - logged User és kapott paraméterek a result Params -ba
     * model és view létrehozása
     * @param Request $request
     * @param array $names elvért paraméter nevek
     * @return Params
     */
    protected function init(Request &$request, array $names = []): Params {
        $result = parent::init($request, $names);
        if (!$result->loggedUser) {
            $result->loggedUser = new UserRecord();
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
       global $REQUEST;
       $response_type = $REQUEST->sessionGet('response_type');
       if ($redirect_uri != '') {
           $jwt = new JwtModel();
           $id_token = $jwt->createIdToken($userRec, $nonce);
           if ($response_type == 'code') {
               if (strpos($redirect_uri, '?') > 0) {
                   $redirect_uri .= '&code='.session_id();
               } else {
                   $redirect_uri .= '?code='.session_id();
               }
           } else {
               if (strpos($redirect_uri, '?') > 0) {
                   $redirect_uri .= '&state='.$state;
               } else {
                   $redirect_uri .= '?state='.$state;
               }
               $redirect_uri .= '&nonce='.$nonce.
               '&id_token='.$id_token.
               '&token='.session_id().
               '&access_token='.session_id();
           }
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
     * @param AppRecord $client
     */
    protected function openidCheck(Params &$p, AppRecord $client) {
        // ha van valós client_id és nincs redirect_uri, policy, scope megadva,
        // akkor client->callback -ot kell visszahívni ill az clientben lévő adatokat kell használni
        if (($client->id > 0) & ($p->redirect_uri == '')) {
            $p->redirect_uri = $client->callback;
        }
        if (($client->id > 0) & ($p->policy_uri == '')) {
            $p->policy_uri = $client->policy;
        }
        if (($client->id > 0) & ($p->scope == '')) {
            $p->scope = $client->scope;
        }

        // ha van valós client_id akkor annak domainjében kell leniie a callback_uri -nak
        if ($client->id > 0) {
            if (($client->domain != '') &
                (strpos(' '.$p->redirect_uri, $client->domain) != 1)) {
                $p->msgs[] = 'invalid redirect_uri';
            }
        }

        // respose type ellenörzés
        if ($p->response_type == 'id_token token') {
            $p->response_type = 'token id_token';
        }
        if (($p->response_type != 'token id_token') & ($p->response_type != 'code')) {
            $p->msgs[] = 'only "token id_token" és "code" response_type supported';
        }

        // scope ellenörzés
        $enabledScope1 = ['sub', 'nickname', 'sysadmin', 'audited', 'postal_code', 'locality'];
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
    protected function fillUserRecord(Params $p, $pdfData, $userRec = false): UserRecord {
        if (!$userRec) {
            $userRec = new UserRecord();
        }
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
        $userRec->nickname = $p->nick; // * bejelentkezési név
        if (isset($p->psw1)) {
            $userRec->pswhash = myHash('sha256', $p->psw1); // * jelszó sha256 hash
        } else {
            unset($userRec->pswhash);
        }
        $userRec->locality = $addressItems[1]; // * település
        $userRec->postal_code = $addressItems[0]; // * irányító szám
        $userRec->audited = 1; // * hitelesített vagy nem?
        $userRec->auditor = 'ügyfélkapu'; // * auditor nick name vagy "ugyfélkapu"
        $userRec->audit_time = time(); // * auditálás időpontja
        $userRec->code = myHash('sha256',$pdfData->xml_szuletesiNev.
            $pdfData->xml_anyjaNeve.$pdfData->xml_szuletesiDatum); // * myHash(origname.mothersname,birth_date)
        $userRec->sub = $userRec->code;
        $userRec->signdate = $pdfData->xml_alairasKelte; // *
        $userRec->sysadmin = 0; // *
        $userRec->updated_at = time(); // utolsó modosítás timestamp
        $userRec->created_at = time();
        // OPENID2 unittestben van használva
        if ((config('OPENID') == 2) | (defined('OPENID2'))) {
           $userRec->email = $p->email; // * email
           $userRec->email_verified = 0; // * email ellnörzött?
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
        $this->getModel('appregist'); // class AppRecord
        $this->checkCsrToken($request);
        $this->createcsrToken($request, $p);
        $p->dataprocessaccept = $request->input('dataprocessaccept');
        $redirect_uri = $request->sessionGet('redirect_uri');
        $nonce = $request->sessionGet('nonce');
        $state = $request->sessionGet('state');
        $client = $this->model->getApp($request->sessionGet('client_id'));
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
            $userRec = $this->model->getUserByNick($p->nickname);
            if ($userRec->id > 0) {
                if ($userRec->pswhash != myHash('sha256', $p->psw)) {
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
            $this->view->loginForm($p);
        } else {
            $request->sessionSet($this->LOGGEDUSER, $userRec);
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
     * registform 2. képernyő (pdf elemzés, elelnörzés, tárolás, login, profil képernyő megjelenítés
     * csrtoken ellenörzés
     * @param Request $request - feltöltött pdf fiile, id
     *    client_id, scope, policy_uri, redirect_uri, state, nonce,
     *    csrToken
     * @return void
     */
    public function registform2(Request $request) {
        $p = $this->init($request,["pdffile","id"]);
        $this->checkCsrToken($request);
        $p->pdfName = $request->input("pdffile","alairt_pdf");
        $p->msgs = [];
        $this->createCsrToken($request, $p);
        $pdfParser = $this->getModel("pdfparser");
        $client_id = $request->sessionGet("client_id");
        if ($client_id == 'self') {
            $client = $this->model->getApp($client_id);
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
		    // pdfsign hash exists?
		    $code = myHash('sha256',$pdfData->xml_szuletesiNev.
		        $pdfData->xml_anyjaNeve.$pdfData->xml_szuletesiDatum); // * myHash(origname.mothersname,birth_date)
		    $res = $this->model->getUserByCode($code);
		    if ($res->id > 0) {
		            // adatok frissitése
		            foreach ($res as $fn => $fv) {
		              $p->$fn = $fv;
		            }
		            $res = $this->fillUserRecord($p, $pdfData, $res);
                    $this->model->saveUser($res);
		            // login
		            $request->sessionSet('loggedUser', $res);
		            // goto profile
		            redirectTo(config('MYDOMAIN').'/opt/openid/profileform');
		    } else {
			         $this->view->registForm2($p);
		    }
		} else {
			foreach (explode(", ",$pdfData->error) as $item) {
				$p->msgs[] = txt($item);
			}
			$this->view = $this->getView('pdfform');
			$p->formTitle = $p->clientTitle.'<br />Regisztráció';
			$p->okURL = config('MYDOMAIN').'/openid/registform2';
			$this->view->pdfForm($p);
		}
     }

     /**
      * sikeres regisztráció utáni teendők
      * @param UserRecord $userRec
      * @param Params $p
      * @param Request $request
      * @param AppRecord $client
      * @param string $redirect_uri
      */
     protected function successRegist(UserRecord $userRec,
                                      Params $p,
                                      Request $request,
                                      AppRecord $client,
                                      string $redirect_uri) {
         if ($p->id == 0)  {
             $url = config('MYDOMAIN').'/opt/openid/emailverify/code/'.$userRec->code;
             $subject = $client->name.' email hitelesités';
             $body = '<p>Az email hitelesítéséhez kattints az alábbi linkre</p>'.
                 '<p><a href="'.$url.'">'.$url.'</a></p>';
             if ($userRec->email != '') {
                 sendEmail($userRec->email, $subject, $body);
             }
         }
         $request->sessionSet($this->LOGGEDUSER, $userRec);
         $this->successLogin($userRec, $redirect_uri,
             $request->sessionGet('state'),
             $request->sessionGet('nonce'));
     }

     /**
     * ha $p->id = 0 regisztrációs form adatainak tárolása,
     * ha $p->id > 0 utolagos ukaudit form tárolása - ez még csak tervezet
     * bejelentkezik és ugrás a redirect_uri -ra
     * csrtoken ellenörzés
     * @param Request $request id, nick, psw1, psw2, email, phone_number, dataprocessaccept,
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
        $pdfData = JSON_decode($request->sessionGet('pdfData'));
        $redirect_uri = $request->sessionGet('redirect_uri');
        $this->getModel('appregist'); // AppRecord class
        $client = $this->model->getApp($request->sessionGet('client_id'));
        $this->doregistCheck($p);
        $userRec = $this->model->getUserByNick($p->nick);
        if (($userRec->id > 0) & ($userRec->id != $p->id)) {
                $p->msgs[] = txt('NICK_EXISTS');
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
            $res = $this->model->getUserByCode($userRec->code);
            if (($res->id == 0) | ($res->id == $userRec->id)) {
                $s = $this->model->saveUser($userRec);
                if ($s != '') {
                    $p->msgs[] = $s;
                }
            } else {
                // login
                $request->sessionSet('loggedUser', $res);
                // goto profile
                redirectTo(config('MYDOMAIN').'/opt/openid/profileform');
                exit();
            }
        }
        if (count($p->msgs) == 0) {
            $this->successRegist($userRec, $p, $request, $client, $redirect_uri);
        } else {
            $p->clientTitle = $client->name;
            $p->scope = $request->sessionGet('scope');
            $p->policy_uri = $request->sessionGet('policy_uri');
            $p->nick = '';
            $this->view->registForm2($p);
        }
    }

    /**
     * scopeForm adatainak tárolása, sessionba mentet 'acceptScopeUser'
     * bejelentkeztetése, ugrás a redirect_uri -ra
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
        $loggedUser = $request->sessionGet('acceptScopeUser', new UserRecord());
        if ($loggedUser->id == 0) {
            $p->msgs[] = txt('ACCESS_VIOLATION');
            $this->view->errorMsg($p->msgs);
            return;
        }
        $p->nickname = $loggedUser->nickname;
        $redirect_uri = $request->sessionGet('redirect_uri');
        $client = $this->getClient($request->sessionGet('client_id'), $p, $this->model);
        if ($p->dataprocessaccept == 1) {
            $p->loggedUser = $loggedUser;
            $request->sessionSet('loggedUser', $p->loggedUser);
            $this->successLogin($p->loggedUser, $redirect_uri,
            $request->sessionGet('state'), $request->sessionGet('nonce'));
        } else {
            $request->sessionSet('loggedUser', new UserRecord());
            $p->clientTitle = $client->name;
            $p->scope = $request->sessionGet('scope');
            $p->policy_uri = $request->sessionGet('policy_uri');
            $p->nickname = '';
            $p->psw = '';
            $this->view->loginForm($p);
        }
    }

    /**
     * elfelejtett jelszó kezelés pdfform kirajzolás, nickname sessionba mentése
     * csrtoken ellenörzés
     * @param Request $request - nickname, csrToken
     * @return void
     */
    public function forgetpswform(Request $request) {
        $p = $this->init($request, []);
        $this->createCsrToken($request, $p);
        $p->okURL = config('MYDOMAIN').'/opt/openid/registform2"}';
        $p->formTitle = txt('FORGETPSW_FORM');
        $view = $this->getView('pdfform');
        $view->pdfForm($p);
    }

    /**
     * email ellenörzö link kattintás feldolgozója
     * @param Request $request - code
     */
    public function emailverify(Request $request) {
        $this->init($request,['code']);
        $code = $request->input('code');
        $user = $this->model->getUserByCode($code);
        unset($user->pswhash);
        if ($user->id > 0) {
            $user->email_verified = 1;
            $this->model->saveUser($user);
            $this->view->successMsg([txt('EMAIL_VERIFIED')],'','',false);
        } else {
            echo 'fatal error';
        }
    }

    /**
     * profil képernyő tárolása
     * @param Request $request
     */
    public function profilesave(Request $request) {
        $p = $this->init($request,['id','email','phone_number',
            'gender','picture','profile','psw1','psw2','name','address']);
        $this->checkCsrToken($request);
        $this->createCsrToken($request, $p);
        $loggedUser = $request->sessionGet($this->LOGGEDUSER, new UserRecord());
        if (($loggedUser->id > 0) & ($loggedUser->id == $p->id)) {
            $loggedUser = $this->model->getUserByNick($loggedUser->nickname);
            if ($p->psw1 != '') {
                $loggedUser->pswhash = myHash('sha256',$p->psw1);
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
            if ($p->name != '') {
                $w = explode(' ',$p->name,3);
                if (count($w) >= 3) {
                    $loggedUser->family_name = $w[0];
                    $loggedUser->middle_name = $w[1];
                    $loggedUser->given_name = $w[2];
                } else if (count($w) == 2) {
                    $loggedUser->family_name = $w[0];
                    $loggedUser->middle_name = '';
                    $loggedUser->given_name = $w[1];
                } else {
                    $loggedUser->family_name = $w[0];
                    $loggedUser->middle_name = '';
                    $loggedUser->given_name = '';
                }
            }
            if ($p->address != '') {
                $w = explode(' ',$p->address,3);
                if (count($w) >= 3) {
                    $loggedUser->postal_code = $w[0];
                    $loggedUser->locality = $w[1];
                    $loggedUser->street_address = $w[2];
                }
            }
            // a nickname egyedi ?
            $rec1 = $this->model->getUserByNick($loggedUser->nickname);
            if (($rec1->id > 0) & ($rec1->id != $loggedUser->id)) {
                $p->msgs[] = txt('NICK_EXISTS');
                foreach ($loggedUser as $fn => $fv) {
                    $p->$fn = $fv;
                }
                $this->view->profileForm($p);
                return;
            }
            if ($loggedUser->nickname == '') {
                $p->msgs[] = txt('NICK_REQUIRED');
                foreach ($loggedUser as $fn => $fv) {
                    $p->$fn = $fv;
                }
                $this->view->profileForm($p);
                return;
            }
            $loggedUser->updated_at = time();
            $msg =  $this->model->saveUser($loggedUser);
            $request->sessionSet($this->LOGGEDUSER, $loggedUser);

            if ($msg == '') {
                $this->view->successMsg([txt('PROFILE_SAVED')],'','',true);
            } else {
                $this->view->errorMsg([txt($msg)]);
            }
        } else {
            $this->view->errorMsg([txt('ACCESS_VIOLATION')],'','',true);
        }
    }

    /**
     * Fiók adatain json formában
     * @param Request $request - sessionban loggedUser
     */
    public function mydata(Request $request) {
        $this->init($request,[]);
        $this->checkCsrToken($request);
        $loggedUser = $request->sessionGet($this->LOGGEDUSER, new UserRecord());
        if ($loggedUser->id > 0) {
            $loggedUser = $this->model->getUserByNick($loggedUser->nickname);
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            echo JSON_encode($loggedUser,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE);
        } else {
            $this->view->errorMsg([txt('ACCESS_VIOLATION')]);
        }
    }

    /**
     * fiók törlése
     * @param Request $request - sessionban loggedUser
     */
    public function delaccount(Request $request) {
        $this->init($request,[]);
        $this->checkCsrToken($request);
        $loggedUser = $request->sessionGet($this->LOGGEDUSER, new UserRecord());
        if ($loggedUser->id > 0) {
            $loggedUser = $this->model->getUserByNick($loggedUser->nickname);

            // user rekord törlése
            $msg = $this->model->delUser($loggedUser->id);

            // kijelentkezés
            $user = new UserRecord();
            $request->sessionSet($this->LOGGEDUSER,$user);

            if ($msg == '') {
                $this->view->successMsg([txt('ACCOUNT_DELETED')],'','',true);
            } else {
                $this->view->errorMsg([$msg]);
            }
        } else {
            $this->view->errorMsg([txt('ACCESS_VIOLATION')]);
        }
    }

    /**
     * set $p->clientTitle by $client_id
     * @param string $client_id
     * @param Params $p
     * @param OpenidModel $model
     */
    protected function getClient(string $client_id, Params &$p, OpenidModel $model): AppRecord {
        $this->getModel('appregist'); // AppRecord class
        $result = new AppRecord();
        if ($client_id == 'self') {
            $result->name = 'self';
            $p->clientTitle = 'self';
        } else {
            $result = $model->getApp($client_id);
            $p->clientTitle = $result->name;
        }
        return $result;
    }


} // OpenidUserController

/** OpenidController osztály */
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
     *   response_type - csak 'id_token token' és 'code' elfogadott
     *   policy_uri - kliens adatkezelési leírása
	 * @return void
	 */
    public function authorize(Request $request) {
        $p = $this->init($request, ['client_id','redirect_uri','scope',
            'state','nonce','policy_uri','response_type']);
        $p->client_id = $request->input('client_id', config('MYDOMAIN'));
        $client = $this->getClient($p->client_id, $p, $this->model);
        if ($client->id <= 0) {
            $client->scope = 'sub nickname postal_code locality audited';
            $client->redirect_uri = '';
        }
        $p->response_type = $request->input('response_type','id_token token');
        $p->scope = urldecode($request->input('scope',$client->scope));
        $p->redirect_uri = urldecode($request->input('redirect_uri', $client->redirect_uri));
        $p->policy = urldecode($request->input('policy', $client->policy));
        $p->nonce = urldecode($request->input('nonce', ''));
        if ($client->name != '') {
            $p->clientTitle = $client->name;
        } else {
            $p->clientTitle = parse_url($p->redirect_uri)['host'];
        }
        $this->setParamToSession($request, $p);
        $this->getModel('appregist'); // AppRecord class
        // ellenörzések
        $this->openidCheck($p, $client);
        if (count($p->msgs) > 0) {
            $this->view->errorMsg($p->msgs);
            return;
        }

        if ($p->loggedUser->id > 0) {
            $p->nickname = $p->loggedUser->nickname;
            $p->msgs = [];
            if ($p->clientTitle != 'self') {
                $this->createCsrToken($request, $p);
                // menti a loggedUser -t sessonba 'acceptScopeUser' néven
                $request->sessionSet('acceptScopeUser', $p->loggedUser);
                // kijelentkezik (a doscopeform fogja ujra bejelentkeztetni)
                $request->sessionSet('loggedUser', new UserRecord());
                $p->nickname = $p->loggedUser->nickname;
                $this->view->scopeForm($p);
            } else {
                $this->successLogin($p->loggedUser,
                    $request->sessionGet('redirect_uri'),
                    $request->sessionGet('state'),
                    $request->sessionGet('nonce'));
                    redirectTo($redirect_uri);
            }
        } else {
            $p->nickname = '';
            $p->psw = '';
            $this->createCsrToken($request, $p);
            $this->view->loginForm($p);
        }
	}

    /**
     * authorize és registform használja
     * @param Request $request
     * @param Params $p
     */
    private function setParamToSession($request, Params $p) {
        $request->sessionSet('client_id',$p->client_id);
        $request->sessionSet('redirect_uri',$p->redirect_uri);
        $request->sessionSet('scope',$p->scope);
        $request->sessionSet('state',$p->state);
        $request->sessionSet('nonce',$p->nonce);
        $request->sessionSet('policy_uri',$p->policy_uri);
        $request->sessionSet('response_type',$p->response_type);
    }

	/**
	 * openid végpont user információk kérése az érkező access_token paraméter valójában session_id ide kell átváltani.
	 * ebben a sessinban van scope, client_id, loggedUser ennek alapján vagy
	 * json formában vagy JWE formában adja vissza a scope -ban kért adatokat.
	 * @param Request $request - access_token,   sessionban logged_user, client_id, scope
	 * @return void
	 */
	public function userinfo(Request $request) {
	    $this->init($request, []);
	    $access_token = $request->input('access_token');
	    $this->sessionChange($access_token, $request);
	    $client_id = $request->sessionGet('client_id');
	    $client = $this->model->getApp($client_id);
	    $user = $request->sessionGet($this->LOGGEDUSER, new UserRecord());
	    $scope = $request->sessionGet('scope');
	    $userInfo = '';
	    if ($user->id > 0) {
	        $user = $this->model->getUserByNick($user->nickname);
	        $w = explode(' ',
	            str_replace('openid',
	                'sub nickname address email email_verified name '.
	                'picture birth_date phone_number phone_number_verified updated_at',
	                $scope));

	        $userInfo = '{';
	        foreach ($w as $item) {
	            if ($item == 'name') {
	                if ($user->middle_name == '') {
	                    $userInfo .= '"name":"'.$user->family_name.' '.$user->given_name.'",';
	                } else {
	                    $userInfo .= '"name":"'.$user->family_name.' '.$user->middle_name.' '.$user->given_name.'",';
	                }
	            } else if ($item == 'address') {
	                $userInfo .= '"address":"'.$user->postal_code.' '.$user->locality.' '.$user->street_address.'",';
	            } else if (isset($user->$item)) {
	                $userInfo .= '"'.$item.'":"'.$user->$item.'",';
	            }
	        }
	        $userInfo .= '"time":"'.date('Y.m.d h:i:s').'"}';
	    } else {
	        $userInfo = '{"error":"not found"}';
	    }
	    if ($client->jwe == 1) {
	        $jwe = new JweModel();
	        if (!headers_sent()) {
	            header('Content-Type: text/plain');
	        }
	        echo $jwe->encrypt($userInfo, $client->pubkey, 'A256CBC');
	    } else {
	        if (!headers_sent()) {
	            header('Content-Type: application/json');
	        }
	        echo $userInfo;
	    }
	}

	/**
	 * access_token kérése code alapján
	 * @param Request $request - code
	 */
	public function token(Request $request) {
	    $this->init($request, ['code']);
	    $this->sessionChange($code, $request);
	    $userRec = $request->sessionGet('loggedUser', new UserRecord());
	    $nonce = $request->sessionGet('nonce', '');
	    $jwt = new JwtModel();
	    $id_token = $jwt->createIdToken($userRec, $nonce);
	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    if ($userRec->id > 0) {
	        echo '{"access_token":"'.session_id().'", "id_token":"'.$id_token.'"}';
	    } else {
	        echo '{"error":"not_found", "access_token":""}';
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
	    $this->init($request, []);
	    $access_token = $request->input('token');
	    $redirect_uri = $request->input('redirect_uri');
	    $this->sessionChange($access_token, $request);
	    $request->sessionSet($this->LOGGEDUSER, new UserRecord());
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
	    $this->init($request, []);
	    $access_token = $request->input('token');
	    $redirect_uri = $request->input('redirect_uri');
	    $this->sessionChange($access_token, $request);
	    $request->sessionSet($this->LOGGEDUSER, $request->sessionGet($this->LOGGEDUSER, new UserRecord()));
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
	    $user = $request->sessionGet($this->LOGGEDUSER, new UserRecord());
	    if ($user->id > 0) {
	        $user = $this->model->getUserByNick($user->nickname);
	        foreach ($user as $fn => $fv) {
	            $p->$fn = $fv;
	        }
	        $this->view->profileform($p);
	    } else {
	        $this->view->errorMsg([txt('ACCESS_VIOLATION')]);
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
	    $this->setParamToSession($request, $p);
	    $client = $this->getClient($p->client_id, $p, $this->model);

	    // ellenörzések
	    $this->openidCheck($p, $client);
	    if (count($p->msgs) > 0) {
	        $this->view->errorMsg($p->msgs);
	        return;
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

	/**
	 * openid konfiguráció kiirása json formában
	 * @param Request $request
	 */
	public function configuration(Request $request) {
	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    ?>
        {
            "authorization_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/authorize",
            "token_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/token",
            "userinfo_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/userinfo",
            "logout_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/logout",
            "refresh_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/refresh",
            "revoke_endpoint": "<?php echo config('MYDOMAIN'); ?>/openid/revoke",
            "authorization_endpoint_params_requided: [
            	"client_id", "redirect_uri", "response_type", "scope", "state", "nonce", "policy_uri"
            ],
            "response_types_supported": [
				"token id_token", "code"
            ],
            "id_token_signing_alg_values_supported": [
            	"SHA256"
            ],
            "unregistered_client_supported": true,
            "token_endpoint_params_requided": [
            	"code"
            ],
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
                "postal_code",
                "locality",
                "sysadmin",
                "audited"
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
                "sysadmin",
                "audited"
<?php endif; ?>
            ]
        }
        <?php
	}

}
?>
