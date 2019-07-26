<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/appregist.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;

$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// test Cases
class appregistControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new appregistController();
        $this->request = new Request();
        $REQUEST = $this->request;
        
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_save_domainEmpty() {
        // a balmix.hu -n van uklogin.html
        $this->request = new Request();
        $this->request->sessionSet('csrtoken','123');
        $this->request->set('123','1');
        $this->request->set('client_id','');
        $this->request->set('domain','');
        $this->request->set('name','balmix');
        $this->request->set('callback','https://balmix.hu/opt/home/logged');
        $this->request->set('css','https://balmix.hu/uklogin.css');
        $this->request->set('admin','admin');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $res = $this->controller->save($this->request);
        $this->expectOutputRegex('/ERROR_DOMAIN_INVALID/');
    }
    
    public function test_save_ok() {
        // a balmix.hu -n van uklogin.html
        $this->request = new Request();
        $this->request->sessionSet('csrtoken','123');
        $this->request->set('123','1');
        $this->request->set('client_id','');
        $this->request->set('domain','https://balmix.hu');
        $this->request->set('name','balmix');
        $this->request->set('callback','https://balmix.hu/opt/home/logged');
        $this->request->set('css','https://balmix.hu/uklogin.css');
        $this->request->set('admin','admin');
        $this->request->set('psw1','123456');
        $this->request->set('psw2','123456');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $res = $this->controller->save($this->request);
        $this->expectOutputRegex('/Client_id/');
    }
    
    public function test_adminlogin() {
        $this->request = new Request();
        $res = $this->controller->adminlogin($this->request);
        $this->expectOutputRegex('/ADMIN_NICK/');
        
    }
    
    public function test_doadminlogin_OK() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $table = new Table('apps');
        $table->where(['domain','=','https://balmix.hu']);
        $rec = $table->first();
        if ($rec) {
            $this->request->set('client_id',$rec->client_id);
            $this->request->set('nick','admin');
            $this->request->set('psw','123456');
        }
        $res = $this->controller->doadminlogin($this->request);
        $this->expectOutputRegex('/formApp/');
    }
    
    public function test_doadminlogin_INVALID_PSW() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $table = new Table('apps');
        $table->where(['domain','=','https://balmix.hu']);
        $rec = $table->first();
        if ($rec) {
            $this->request->set('client_id',$rec->client_id);
            $this->request->set('nick','admin');
            $this->request->set('psw','abcd123456');
        }
        $res = $this->controller->doadminlogin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_doadminlogin_INVALID_NICK() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $table = new Table('apps');
        $table->where(['domain','=','https://balmix.hu']);
        $rec = $table->first();
        if ($rec) {
            $this->request->set('client_id',$rec->client_id);
            $this->request->set('nick','nemjo');
            $this->request->set('psw','123456');
        }
        $res = $this->controller->doadminlogin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_doadminlogin_INVALID_CLINET_ID() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $table = new Table('apps');
        $table->where(['domain','=','https://balmix.hu']);
        $rec = $table->first();
        if ($rec) {
            $this->request->set('client_id','nemjo');
            $this->request->set('nick','admin');
            $this->request->set('psw','abcd123456');
        }
        $res = $this->controller->doadminlogin($this->request);
        $this->expectOutputRegex('/INVALID_LOGIN/');
    }
    
    public function test_appremove_OK() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $table = new Table('apps');
        $table->where(['domain','=','https://balmix.hu']);
        $rec = $table->first();
        if ($rec) {
            $this->request->set('client_id',$rec->client_id);
        }
        $this->controller->appremove($this->request);
        $this->expectOutputRegex('/APPREMOVED/');
    }
    
    public function test_appremove_NOTFOUND() {
        $this->request = new Request();
        $this->request->set('123','1');
        $this->request->sessionSet('csrtoken','123');
        $this->request->set('client_id','nincsilyen');
        $this->controller->appremove($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        $db->statement('DELETE FROM apps');
        $this->assertEquals('','');
    }
}

