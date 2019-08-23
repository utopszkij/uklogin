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
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_form() {
        $this->request->set('client_id',12);
        $this->controller->form($this->request);
        $this->expectOutputRegex('/iframe/');
        
    }
    
    public function test_code() {
        $this->request->set('code',12345);
        $this->controller->code($this->request);
        $this->expectOutputRegex('/parent/');
    }
    
    public function test_loogout() {
        $this->request->set('code',12345);
        $this->controller->logout($this->request);
        $this->assertEquals(true,true); // csak szintaxis ellenörzés
    }
    
}

