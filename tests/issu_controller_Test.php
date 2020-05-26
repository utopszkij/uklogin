<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/mock.php';
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/issu.php';

use PHPUnit\Framework\TestCase;


// test Cases
class IssuControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new IssuController();
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
        $msg = $this->controller->form($this->request);
        $this->expectOutputRegex('/LBL_ISSU/');
    }
    
    public function test_send_error() {
        $this->request->sessionset('csrToken','123');
        $this->request->set('123','1');
        $this->request->set('body','');
        $this->request->set('title','');
        $msg = $this->controller->send($this->request);
        $this->expectOutputRegex('/ERROR_ISSU_TITLE_EMPTY/');
    }
    
    public function test_send_ok() {
        $this->request->sessionset('csrToken','123');
        $this->request->set('123','1');
        $this->request->set('body','issu_title');
        $this->request->set('title','issu_body');
        $msg = $this->controller->send($this->request);
        $this->expectOutputRegex('/ISSU_SAVED/');
    }
    
}

