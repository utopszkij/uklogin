<?php
declare(strict_types=1);
global $REQUEST;
session_start();
include_once './tests/config.php';
include_once './tests/mock.php';
include_once './core/database.php';
include_once './example.php';

use PHPUnit\Framework\TestCase;


// test Cases
class ExampleTest extends TestCase 
{
    
    public function test_code() {
        $_GET['task'] = 'code';
        $_GET['token'] = '1234';
        codeTask();
        $this->expectOutputRegex('/userinfo kérés eredménye/');
    }
    
    public function test_logoute() {
        $_GET['task'] = 'logout';
        $_GET['token'] = '1234';
        logoutTask();
        $this->expectOutputRegex('/Ügyfélkapus OpenId bejelentkezés példa program/');
    }
    
    public function test_home() {
        $_GET['task'] = 'home';
        homeTask();
        $this->expectOutputRegex('/Ügyfélkapus OpenId bejelentkezés példa program/');
    }
    
 }
?>
