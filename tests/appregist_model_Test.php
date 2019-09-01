<?php
declare(strict_types=1);
global $REQUEST;
include_once './tests/config.php';
include_once './core/database.php';
include_once './models/appregist.php';
include_once './tests/mock.php';

use PHPUnit\Framework\TestCase;


// test Cases
class appregistModelTest extends TestCase 
{
    protected $model;
    protected $request;
    protected $client_id;
    
    function __construct() {
        global $REQUEST;
        parent::__construct();
        $this->model = new appregistModel();
        $this->request = new Request();
        $REQUEST = $this->request;
        
    }
    
    public function test_start() {
        // create and init test database
        $db = new DB();
        $db->statement('CREATE DATABASE IF NOT EXISTS test');
        $this->assertEquals('',$db->getErrorMsg());
    }
    
    public function test_check_domain_empty() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = '';
        $this->data->name = 'teszt app';
        $this->data->callback = 'https://github.com/utopszkij/logged';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_DOMAIN_EMPTY',$msg);
    }
    
    public function test_check_domain_invalid() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'nem_jo_domain';
        $this->data->name = 'teszt app';
        $this->data->callback = 'https://github.com/utopszkij/logged';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_DOMAIN_INVALID',$msg);
    }

    public function test_check_daomain_exists() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://test.hu';
        $this->data->name = 'teszt app';
        $this->data->callback = 'https://github.com/utopszkij/logged';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_DOMAIN_EXISTS',$msg);
    }
    
    public function test_check_name_empty() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = '';
        $this->data->callback = 'https://github.com/utopszkij/logged';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_NAME_EMPTY',$msg);
    }
    
    public function test_check_callback_empty() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = 'valami';
        $this->data->callback = '';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_CALLBACK_EMPTY',$msg);
    }
    
    public function test_check_callback_invalid() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = 'valami';
        $this->data->callback = 'nemjó';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_CALLBACK_INVALID',$msg);
    }
    
    public function test_check_callback_not_in_domain() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = 'valami';
        $this->data->callback = 'https://mashol.hu/logged.php';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_CALLBACK_NOT_IN_DOMAIN',$msg);
    }
    
    public function test_check_css_invalid() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = 'valami';
        $this->data->callback = 'https://valami.hu/opt/home/logged';
        $this->data->css  = 'nemjo';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_CSS_INVALID',$msg);
    }

    public function test_check_admin_empty() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://valami.hu';
        $this->data->name = 'valami';
        $this->data->callback = 'https://valami.hu/opt/home/logged';
        $this->data->css  = '';
        $this->data->admin = '';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_ADMIN_EMPTY',$msg);
    }
     
    public function test_check_uklogin_html__not_exist() {
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://adatmagus.hu';
        $this->data->name = 'valami';
        $this->data->callback = 'https://adatmagus.hu/opt/home/logged';
        $this->data->css  = '';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_UKLOGIN_HTML_NOT_EXISTS',$msg);
    }
    
    public function test_check_dataProcess_not_accept() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://balmix.hu';
        $this->data->name = 'balmix';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 0;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_DATA_ACCEPT_REQUEST',$msg);
    }
    
    public function test_check_cookieProcess_not_accept() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://balmix.hu';
        $this->data->name = 'balmix';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 0;
        $msg = $this->model->check($this->data);
        $this->assertContains('ERROR_COOKIE_ACCEPT_REQUEST',$msg);
    }
    
    public function test_check_ok() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://balmix.hu';
        $this->data->name = 'balmix';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $msg = $this->model->check($this->data);
        $this->assertEquals(0,count($msg));
    }
    
    public function test_save_new_ok() {
        global $REQUEST;
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'https://balmix.hu';
        $this->data->name = 'balmix';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $this->data->falseLoginLimit = 1;
        $res = $this->model->save($this->data);
        $REQUEST->client_id = $res->client_id;
        $this->assertEquals(true,isset($res->client_id));
    }
    
    public function test_save_insert_notok() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = '';
        $this->data->domain = 'nemjo';
        $this->data->name = 'balmix';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $this->data->falseLoginLimit = 1;
        $res = $this->model->save($this->data);
        $this->assertEquals(true,isset($res->error));
    }
    
    public function test_save_update_ok() {
        global $REQUEST;
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->id = 1;
        $this->data->client_id = $REQUEST->client_id;
        $this->data->domain = 'https://balmix.hu';
        $this->data->name = 'balmix javitva';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->falseLoginLimit = 5;
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $res = $this->model->save($this->data);
        $this->assertEquals(true, isset($res->client_id));
    }
    
    public function test_save_update_notok() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $this->data->client_id = 'nincsilyen';
        $this->data->domain = 'nemjo';
        $this->data->name = 'balmix javitva';
        $this->data->callback = 'https://balmix.hu/opt/home/logged';
        $this->data->css  = 'https://balmix.hu/uklogin.css';
        $this->data->admin = 'admin';
        $this->data->falseLoginLimit = 5;
        $this->data->dataProcessAccept = 1;
        $this->data->cookieProcessAccept = 1;
        $res = $this->model->save($this->data);
        $this->assertEquals(true,isset($res->error));
    }
    
    public function test_remove_notfound() {
        // a balmix.hu -n van uklogin.html
        $this->data = new AppRecord();
        $client_id = 'nincsilyen';
        $res = $this->model->remove($client_id);
        $this->assertEquals('ERROR_NOT_FOUND',$res);
    }
    
    public function test_remove_ok() {
        global $REQUEST;
        $this->data = new AppRecord();
        $client_id = $REQUEST->client_id;
        $res = $this->model->remove($client_id);
        $this->assertEquals('', $res);
    }
        
    public function test_end() {
        $db = new DB();
        // clear test datas
        $db->statement('DELETE FROM apps');
        $this->assertEquals('','');
    }
}

