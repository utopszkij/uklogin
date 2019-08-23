<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/oauth2.php';
include_once './models/appregist.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// test Cases
class oauth2ControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new Oauth2Controller();
        $this->request = new Request();
        $REQUEST = $this->request;
        $appregistModel = new AppregistModel(); // ez tartalmazza az apps tábla kreálást
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function pdf_syntax_only() {
        $this->request->set('client_id','nincsilyen');
        $this->controller->pdf($this->request);
        $this->assertEquals(1,1);
    }
    
    public function test_registform_notfound() {
        $this->request->set('client_id','nincsilyen');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
     public function test_registform_ok() {
        $table = new table('apps');
        $rec = new stdClass();
        $rec->client_id = '123';
        $rec->client_secret = '123456';
        $rec->name = 'test123';
        $rec->domain = 'https://test.hu';
        $rec->callback = 'https://test.hu/logged';
        $rec->css = '';
        $rec->falseLoginLimit = '10';
        $rec->admin = 'admin';
        $table->insert($rec);
        $this->request->set('client_id','123');
        $this->controller->registform($this->request);
        $this->expectOutputRegex('/LBL_SIGNEDPDF/');
    }
    
     public function test_registform2_PDFNOTUPLOADED() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->set('abc',1);
        $this->request->set('signed_pdf','test_notuploaded');
        $this->controller->registform2($this->request);
        $this->expectOutputRegex('/ERROR_PDF_NOT_UPLOADED/');
    }
    
    public function test_registform2_OK() {
        $this->request->sessionSet('client_id','503206214');
        $this->request->sessionSet('csrToken','abc');
        $this->request->set('abc',1);
        $this->controller->registform2($this->request);
        $this->expectOutputRegex('/USER/');
    }
    
    public function test_doregist_nickempty() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','');
        $this->request->set('psw1','');
        $this->request->set('psw2','');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_NICK_EMPTY/');
    }

    public function test_doregist_pswempty() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','');
        $this->request->set('psw2','');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_PSW_EMPTY/');
    }
    
    public function test_doregist_pswinvalid() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','12');
        $this->request->set('psw2','');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_PSW_INVALID/');
    }
        
    public function test_doregist_2pswnotequeals() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123457');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_PSW_NOTEQUAL/');
    }
    
    public function test_doregist_nickexists() {
        $table = new Table('users');
        $data = new stdClass();
        $data->client_id = '123';
        $data->signhash = '';
        $data->nick = 'user1';
        $table->insert($data);
        
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->sessionSet('nick','');
        
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','user1');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_NICK_EXISTS/');
    }
    
    public function test_doregist_signexists() {
        $table = new Table('users');
        $data = new stdClass();
        $data->client_id = '123';
        $data->signhash = 'testsign';
        $data->nick = 'user2';
        $table->insert($data);
        
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign');
        $this->request->sessionSet('nick','');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','user3');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/ERROR_SIGN_EXISTS/');
    }
    
    public function test_doregist_ok() {
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('signHash','testSign2');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/USER_SAVED/');
    }
    
    public function test_forgetpsw_ok() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->controller->forgetpsw($this->request);
        $this->expectOutputRegex('/LBL_SIGNEDPDF/');
    }
    
    public function test_forgetpsw_nick_empty() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','');
        $this->controller->forgetpsw($this->request);
        $this->expectOutputRegex('/ERROR_NICK_EMPTY/');
    }
    
    public function test_forgetpsw_app_notfoundy() {
        $this->request->sessionSet('client_id','nincsilyen');
        $this->request->set('nick','testelek');
        $this->controller->forgetpsw($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
    public function test_changepsw_nick_empty() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->changepsw($this->request);
        $this->expectOutputRegex('/ERROR_NICK_EMPTY/');
    }
    
    public function test_changepsw_psw_empty() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','');
        $this->request->set('psw2','123456');
        $this->controller->changepsw($this->request);
        $this->expectOutputRegex('/ERROR_PSW_EMPTY/');
    }
    
    
    public function test_changepsw_psw_error() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','nemjo');
        $this->request->set('psw2','123456');
        $this->controller->changepsw($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_changepsw_ok() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->changepsw($this->request);
        $this->expectOutputRegex('/LBL_NEW_PSW/');
    }
    
    public function test_mydata_ok() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->mydata($this->request);
        $this->expectOutputRegex('/"testelek"/');
    }
    
    public function test_loginform_falsclient_id() {
        $this->request->sessionSet('client_id','nincsilyen');
        $this->controller->loginform($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
    public function test_loginform_ok() {
        $this->request->set('client_id','123');
        $this->controller->loginform($this->request);
        $this->expectOutputRegex('/USER/');
    }
    
    public function test_dologin_invalidNick() {
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('client_id','123');
        $this->request->set('abc',1);
        $this->request->set('nick','nincsilyen');
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_dologin_invalidPsw() {
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('client_id','123');
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw','nemjo');
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_dologin_invalidPsw2() {
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('client_id','123');
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw','');
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_dologin_ok() {
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('client_id','123');
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->controller->dologin($this->request);
        $this->expectOutputRegex('/headers sent/');
    }
    
    public function test_access_token_ok() {
        // set test app (client_id='123', client_secret='t1234'
        $table = new Table('apps');
        $rec = new stdClass();
        $rec->client_id='123';
        $rec->client_secret='t1234';
        $table->where(['client_id','=','123']);
        $table->update($rec);
        
        // create test user (client_id=t123', code='t1235', access_token='t1234567')
        $table = new Table('users');
        $rec = new stdClass();
        $rec->client_id='123';
        $rec->code='t1235';
        $rec->nick='testuser';
        $rec->enabled=1;
        $rec->errorcount=0;
        $rec->codetime=date('Y-m-d H:i:s');
        $rec->access_token='t1234567';
        $table->insert($rec);
        
        $this->request->set('client_id','123');
        $this->request->set('client_secret','t1234');
        $this->request->set('code','t1235');
        $access_token = $this->controller->access_token($this->request);
        $this->expectOutputRegex('/access_token/');
    }
    
    public function test_userinfo_ok() {
        $this->request->set('access_token','t1234567');
        $this->controller->userinfo($this->request);
        $this->expectOutputRegex('/nick/');
    }
    
    public function test_useractival_notfound() {
        $this->request->sessionSet('csrToken','t1234567');
        $this->request->set('t1234567','1');
        $this->request->set('client_id','123');
        $this->request->set('nick','nincsilyen');
        $this->controller->useractival($this->request);
        $this->expectOutputRegex('/NOT_FOUND/');
    }
    
    public function test_useractival_ok() {
        $this->request->sessionSet('csrToken','t1234567');
        $this->request->set('t1234567','1');
        $this->request->set('client_id','123');
        $this->request->set('nick','testelek');
        $this->controller->useractival($this->request);
        $this->expectOutputRegex('/ACTIVATED/');
    }
    
    
    public function test_mydata_ok2() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->mydata($this->request);
        $this->expectOutputRegex('/"testelek"/');
    }
        
    public function test_doRegist_forgetPsw() {
        
        $this->request->sessionSet('csrToken','abc');
        $this->request->sessionSet('client_id','123');
        $this->request->sessionSet('nick','testelek');
        $this->request->sessionSet('signHash','testSign2');
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->controller->doRegist($this->request);
        $this->expectOutputRegex('/SAVED/');
    }
        
    public function test_deleteaccount_ok() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('t1234567','1');
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->deleteaccount($this->request);
        $this->expectOutputRegex('/USER_DELETED/');
    }
    
    public function test_deleteaccount_notfound() {
        $this->request->sessionSet('client_id','123');
        $this->request->set('t1234567','1');
        $this->request->set('nick','nincsilyen');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->deleteaccount($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
    public function test_pdf() {
        $this->request->sessionSet('client_id','123');
        $this->controller->pdf($this->request);
        $this->assertEquals('',''); // csak szintaktikai teszt
    }
    
    public function test_getCallbackUrl() {
        $this->request->sessionSet('client_id','123');
        $app = new stdClass();
        $app->callback='https://test.hu';
        $user = new stdClass();
        $user->code = '12345';
        $this->request->sessionSet('extraParams',["p1" => "v1"]);
        $res = $this->controller->getCallbackUrl($app, $user, $this->request);
        $this->assertEquals('https://test.hu?code=12345&p1=v1',$res); 
    }
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        $db->statement('DELETE FROM apps');
        $db->statement('DELETE FROM users');
        $this->assertEquals('','');
    }
    
}

