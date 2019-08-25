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
        // teszt app rekord létrehozása
        $db->statement("
        INSERT INTO apps (name,client_id,client_secret,`domain`,callback,css,falseLoginLimit,admin) VALUES
        ('teszt app','123','t1234','http://robitc/uklogin','http://robitc/uklogin/example.php?task=code','',10,'wdgéá')
        ");
        // teszt user létrehozása (jelszó:123456)
        $db->statement("
        INSERT INTO users (client_id,nick,pswhash,signhash,enabled,errorcount,code,access_token,codetime,blocktime) VALUES 
        ('123','testelek','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','8c566badded9cff1d2a3ee3da7b5525d9f1cc15230a8f9059aead3dc4ac2da05',1,0,'','','','')
        ");
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

