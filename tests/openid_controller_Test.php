<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './tests/mock.php';
include_once './core/database.php';
include_once './controllers/openid.php';
include_once './models/openid.php';

use PHPUnit\Framework\TestCase;


// test Cases
class openidControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $model = new OpenidModel(); // oi_users tábla generálás
        $this->request = new Request();
        $REQUEST = $this->request;
        $this->controller = new OpenidController();
    }
    
    public function test_start() {
        $db = new DB();
        $db->statement('DELETE FROM oi_users');
        $this->assertEquals('',$db->getErrorMsg());
        // $this->assertContains('ERROR_DOMAIN_EMPTY',$msg);
        // $this->expectOutputRegex('/parent/');
    }
    
    // ================ doregist ====================================
    
    public function test_doregist_nickPswEmailEmpty() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('nickname','');
        $this->request->set('psw1','');
        $this->request->set('psw2','');
        $this->request->set('email','');
        $this->request->set('dataprocessaccept','0');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/NICK_REQUIRED/');
        $this->expectOutputRegex('/PSW_REQUIRED/');
        $this->expectOutputRegex('/EMAIL_REQUIRED/');
        $this->expectOutputRegex('/DATAPROCESS_ACCEPT_REQUIRED/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_doregist_pswsNotEqual() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('nickname','user1');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','abcdefg');
        $this->request->set('email','test@email.hu');
        $this->request->set('dataprocessaccept','1');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/PASSWORDS_NOTEQUALS/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_doregist_sessionError() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('nickname','user1');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('email','test@email.hu');
        $this->request->set('dataprocessaccept','1');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ACCESS_VIOLATION/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_doregist_ok() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('nick','user1');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('email','test@email.hu');
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('pdfData','{
            "error":"",
            "txt_name":"", 
            "txt_mothersname":"",
            "txt_birth_date":"",
            "txt_address":"",
            "txt_tartozkodas":"",
            "info_creator":"",
            "info_producer":"",
            "info_pdfVersion":"",
            "xml_nev":"",
            "xml_ukemail":"",
            "xml_szuletesiNev":"",
            "xml_anyjaNeve":"",
            "xml_szuletesiDatum":"",
            "xml_alairasKelte":""
        }');
        $this->request->sessionSet('redirect_uri','testRedirectUri');
        $this->controller->doregist($this->request);  // felvisz egy "user1" rekordot a test adatbázisba
        $this->assertNotEquals('',$redirectURL);
    }
    public function test_doregist_nickExists() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('nick','user1');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('email','test@email.hu');
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('pdfData','{
            "error":"",
            "txt_name":"", 
            "txt_mothersname":"",
            "txt_birth_date":"",
            "txt_address":"",
            "txt_tartozkodas":"",
            "info_creator":"",
            "info_producer":"",
            "info_pdfVersion":"",
            "xml_nev":"",
            "xml_ukemail":"",
            "xml_szuletesiNev":"",
            "xml_anyjaNeve":"",
            "xml_szuletesiDatum":"",
            "xml_alairasKelte":""
}');
        $this->request->sessionSet('redirect_uri','redirect_uri');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/NICK_EXISTS/');
        $this->assertEquals('',$redirectURL);
    }
    
    // =================== dologin ========================================
     
    public function test_dologin_nickPswEmpty() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('nickname','');
        $this->request->set('psw','');
        $this->request->set('dataprocessaccept',0);
        // chkbox not checked; not send $this->request->dataprocessaccept = 0;
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/NICK_REQUIRED/');
        $this->expectOutputRegex('/PSW_REQUIRED/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_dologin_wrong_notfound() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('nickname','wrong');
        $this->request->set('psw','wrong');
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->dataprocessaccept = '1';
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_dologin_wrong_psw() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('nickname','user1');
        $this->request->set('psw','wrong');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('dataprocessaccept','1');
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_dologin_ok() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('nickname','user1');
        $this->request->set('psw','123456');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('redirect_uri','testRedirectUri');
        $this->controller->dologin($this->request);
        $this->assertNotEquals('',$redirectURL);
    }
    
    // ================= doscopefrom ===================
    
    public function test_doscopeform_ok() {
        global $redirectURL;
        $redirectURL = '';
        $user = new UserRecord();
        $user->id = 1;
        $this->request->sessionSet('loggedUser', $user);
        $this->request->set('nickname','user1');
        $this->request->set('psw','123456');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('redirect_uri','testRedirectUri');
        $this->controller->doscopeform($this->request);
        $this->assertNotEquals('',$redirectURL);
    }
    public function test_doscopeform_notAccept() {
        global $redirectURL;
        $redirectURL = '';
        $user = new UserRecord();
        $user->id = 1;
        $this->request->sessionSet('loggedUser', $user);
        $this->request->set('nickname','user1');
        $this->request->set('psw','123456');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('dataprocessaccept','0');
        $this->request->sessionSet('redirect_uri','testRedirectUri');
        $this->controller->doscopeform($this->request);
        $this->expectOutputRegex('/LOGIN/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_doscopeform_notlogged() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('nickname','user1');
        $this->request->set('psw','123456');
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken',1);
        $this->request->set('dataprocessaccept','1');
        $this->request->sessionSet('redirect_uri','testRedirectUri');
        $this->controller->doscopeform($this->request);
        $this->expectOutputRegex('/ACCESS_VIOLATION/');
        $this->assertEquals('',$redirectURL);
    }
    
    // =================== AUTHORIZE =================================
    
    public function test_authorize_wrongResponseType() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'wrong');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->authorize($this->request);
        $this->expectOutputRegex('/only "id_token token"/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_authorize_wrongScope() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code wrong');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->authorize($this->request);
        $this->expectOutputRegex('/NOT_SUPPORTED_SCOPE/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_authorize_redirect_uriEmpty() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', '');
        $this->request->set('scope', 'nickname postal_code wrong');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->authorize($this->request);
        $this->expectOutputRegex('/redirect_uri empty/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_authorize_ok1() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->authorize($this->request);
        $this->expectOutputRegex('/LOGIN/');
        $this->assertEquals('',$redirectURL);
    }
    public function test_authorize_ok2() {
        global $redirectURL;
        $redirectURL = '';
        $user = new UserRecord();
        $user->id = 1;
        $this->request->sessionSet('loggedUser', $user);
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->authorize($this->request);
        $this->expectOutputRegex('/SCOPE/');
        $this->assertEquals('',$redirectURL);
    }
    
    // ================== registform ======================================
    
    
    public function test_registform_redirect_uriEmpty() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', '');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/redirect_uri empty/');
    }
    
    public function test_registform_scopeWorng() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code wrong');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/NOT_SUPPORTED_SCOPE/');
    }
    
    public function test_registform_response_typeWrong() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'wrong');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/only "id_token token"/');
    }
    public function test_registform_OK() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/REGIST_FORM/');
    }
    
    
    // ================= registform2 ============================
    
    public function test_registform2_notUpladedFile() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->request->set('upladName', 'nincsIlyen_pdf');
        $this->controller->registform2($this->request);
        $this->expectOutputRegex('/alert-danger/');
    }
    public function test_registform2_wrongUploadedFile() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->request->set('upladName', 'rossz_pdf');
        $this->controller->registform2($this->request);
        $this->expectOutputRegex('/alert-danger/');
    }
    public function test_registform2_ok() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->sessionSet('loggedUser', new UserRecord());
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('client_id', 'http://test.hu');
        $this->request->set('redirect_uri', 'http://test.hu');
        $this->request->set('scope', 'nickname postal_code');
        $this->request->set('policy_uri', 'http://test.hu/policy');
        $this->request->set('response_type', 'id_token token');
        $this->request->set('state', 'teststate');
        $this->request->set('nonce', 'testnonce');
        $this->request->set('pdffile', 'jo_pdf');
        $this->controller->registform2($this->request);
        $this->expectOutputRegex('/registForm2/');
    }
    
    // ================ forgetpswform =======================
    
    public function test_forgetpswform_nickEmpty() {
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('nickname','');
        $this->controller->forgetpswform($this->request);
        $this->expectOutputRegex('/NICK_REQUIRED/');
    }
    
    public function test_forgetpswform_nickInvalid() {
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('nickname','nemjo');
        $this->controller->forgetpswform($this->request);
        $this->expectOutputRegex('/NOT_FOUND/');
    }
    
    public function test_forgetpswform_ok() {
        $this->request->sessionSet('csrToken','testcsrtoken');
        $this->request->set('testcsrtoken', '1');
        $this->request->set('nickname','user1');
        $this->controller->forgetpswform($this->request);
        $this->expectOutputRegex('/NEW_PSW_SENDED/');
    }
    
    /*
    
    public function test_userinfo() {
    }
    
    public function test_logout() {
    }
    
    public function test_refresh() {
    }
    
    public function test_revoke() {
    }
    
    public function test_emailverify() {
    }
    */
    
    public function test_end() {
        $db = new DB();
        $db->statement('DELETE FROM oi_users');
        $this->assertEquals('',$db->getErrorMsg());
    }
}

