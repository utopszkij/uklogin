<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './controllers/txt.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;


// test Cases
class txtControllerTest extends TestCase 
{
    protected $controller;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->controller = new TxtController();
        $this->request = new Request();
        $REQUEST = $this->request;
        
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
   
    public function test_add() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('token','TEST');
        $this->controller->add($this->request);
        $this->assertEquals('',''); // csak szintaktikai test
    }
    
    public function test_doadd() {
        global $redirectURL;
        $redirectURL = '';
        $this->request->set('token','TEST');
        $this->request->set('lngname','test');
        $this->request->set('value','value');
        $this->controller->doadd($this->request);
        $this->assertEquals(file_exists('./langs/test_hu.php'),true);
    }
    
    public function test_end() {
        unlink(MYPATH.'./langs/test_hu.php');
        $this->assertEquals('',''); 
    }
    
        
    
}
?>
