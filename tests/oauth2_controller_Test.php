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
        $rec->pswhash = '12345678';
        $rec->adminfalseLoginLimit = '10';
        $rec->adminLoginEnabled = 1;
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
        $this->request->set('abc',1);
        $this->request->set('nick','testelek');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->controller->doregist($this->request);
        $this->expectOutputRegex('/USER_SAVED/');
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
    
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        $db->statement('DELETE FROM apps');
        $db->statement('DELETE FROM users');
        $this->assertEquals('','');
    }
    
}

