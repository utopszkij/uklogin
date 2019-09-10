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
        $this->controller = new AppregistController();
        $this->request = new Request();
        $REQUEST = $this->request;
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_add_adminNotLogged() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','');
        $this->controller->add($this->request);
        $this->expectOutputRegex('/iframe/');
    }
    
    public function test_save_domainEmpty() {
        // a balmix.hu -n van uklogin.html
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        $this->request->sessionSet('csrToken','123');
        $this->request->set('123','1');
        $this->request->set('client_id','');
        $this->request->set('domain','');
        $this->request->set('name','balmix');
        $this->request->set('callback','https://balmix.hu/opt/home/logged');
        $this->request->set('css','https://balmix.hu/uklogin.css');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->controller->save($this->request);
        $this->expectOutputRegex('/ERROR_DOMAIN_INVALID/');
    }
    
     public function test_save_ok() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        // a balmix.hu -n van uklogin.html
        $this->request->sessionSet('csrToken','123');
        $this->request->set('123','1');
        $this->request->set('client_id','');
        $this->request->set('domain','https://balmix.hu');
        $this->request->set('name','balmix');
        $this->request->set('callback','https://balmix.hu/opt/home/logged');
        $this->request->set('css','https://balmix.hu/uklogin.css');
        $this->request->set('dataProcessAccept',1);
        $this->request->set('cookieProcessAccept',1);
        $this->controller->save($this->request);
        $this->expectOutputRegex('/Client_id/');
    }
    
    public function test_adminform_found() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        $this->controller->adminform($this->request);
        $this->expectOutputRegex('/LBL_TITLE/');
    }
    
    public function test_adminform_noneInApps() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        $this->request->set('client_id','11');
        $this->controller->adminform($this->request);
        $this->expectOutputRegex('/FATAL_ERROR/');
    }
        
    public function test_adminform_manyApps() {
        $db = new DB();
        $table = new Table('apps');
        $app = $table->first();
        $client_id = $app->client_id;
        $admin = $app->admin;
        $this->request = new Request();
        $this->request->sessionSet('adminNick',$admin);
        $this->request->set('client_id',$client_id);
        $this->controller->adminform($this->request);
        $this->expectOutputRegex('/LBL_TITLE/');
    }
        
    public function test_adminform_adminNotLogged() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','');
        $this->controller->adminform($this->request);
        $this->expectOutputRegex('/FATAL_ERROR/');
    }
    
     public function test_appremove_OK() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        $this->request->set('123','1');
        $this->request->sessionSet('csrToken','123');
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
        $this->request->sessionSet('adminNick','admin');
        $this->request->set('123','1');
        $this->request->sessionSet('csrToken','123');
        $this->request->set('client_id','nincsilyen');
        $this->controller->appremove($this->request);
        $this->expectOutputRegex('/ERROR_NOTFOUND/');
    }
    
    public function test_adminform_notfound() {
        $this->request = new Request();
        $this->request->sessionSet('adminNick','admin');
        $this->controller->adminform($this->request);
        $this->expectOutputRegex('/ERROR_APP_NOTFOUND/');
    }
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        // $db->statement('DELETE FROM apps');
        $this->assertEquals('','');
    }
}

