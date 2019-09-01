<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/login.php';
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
        $REQUEST = $this->request;
        
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        // teszt app rekord létrehozása
        $db->statement("
        INSERT INTO apps (name,client_id,client_secret,`domain`,callback,css,falseLoginLimit,admin) VALUES
        ('teszt app','123','t1234','http://robitc/uklogin','http://robitc/uklogin/example.php?task=code','',10,'wdgéá')
        ");
        // teszt user létrehozása (jelszó:123456)
        $db->statement("
        INSERT INTO users (client_id,nick,pswhash,signhash,enabled,errorcount,code,access_token,codetime,blocktime) VALUES
        ('123','testelek','8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92','8c566badded9cff1d2a3ee3da7b5525d9f1cc15230a8f9059aead3dc4ac2da05',1,0,'12345','','','')
        ");
        
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
    
}

