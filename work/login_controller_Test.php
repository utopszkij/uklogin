<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/login.php';
include_once './models/users.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;


// test Cases
class loginControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new LoginController();
        $this->request = new Request();
        $userModel = new UsersModel(); // tábla létrehozása miatt kell
        $REQUEST = $this->request;
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');

        // teszt app rekord létrehozása
        $table = $db->table('apps');
        if (!$table->insert(JSON_decode('{
            "name":"teszt app",
            "client_id":"123",
            "client_secret":"t1234",
            "domain":"http://robitc/uklogin",
            "calback":"http://robitc/uklogin/example.php?task=code",
            "css":"",
            "falseLoginLimit":10,
            "admin":"wdgéá"
        }'))) {
            echo $db->getErrorMsg(); exit();
        }
        
        // teszt user létrehozása (jelszó:123456)
        $table = $db->table('users');
        if (!$table->insert(JSON_decode('{
            "client_id":"123",
            "nick":"testelek",
            "pswhash":"8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92",
            "signhash":"8c566badded9cff1d2a3ee3da7b5525d9f1cc15230a8f9059aead3dc4ac2da05",
            "enabled":1,
            "errorcount":0,
            "code":"12345",
            "access_token":"",
            "codetime":"",
            "blocktime":""
        }'))) {
            echo $db->getErrorMsg(); exit();
        }
        
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_form() {
        $this->request->set('client_id','123');
        $this->controller->form($this->request);
        $this->expectOutputRegex('/iframe/');
        
    }
    
    public function test_code() {
        $this->request->set('code','12345');
        $this->controller->code($this->request);
        $this->expectOutputRegex('/parent/');
    }
    
    public function test_loogout() {
        $this->request->set('code','12345');
        $this->controller->logout($this->request);
        $this->assertEquals(true,true); // csak szintaxis ellenörzés
    }
    
    public function test_end() {
        $db = new DB();
        // clear test datas
        $db->statement('DELETE FROM apps');
        $db->statement('DELETE FROM users');
        $this->assertEquals('','');
    }
    
    
}

