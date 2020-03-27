<?php
if (isset($_GET['sid'])) {
    session_id(strip_tags($_GET['sid']));
}
if (isset($_POST['sid'])) {
    session_id(strip_tags($_POST['sid']));
}

include_once './.config.php';
include_once './core/database.php';
include_once './core/framework.php';
include_once './models/uklogin.php';
ini_set('session.gc_maxlifetime', config('CODE_EXPIRE'));
session_set_cookie_params(config('CODE_EXPIRE'));
session_start();

// opt/optionName/taskName/pName/pvalue.... REQUEST_URI értelmezés
$w = explode('/',$_SERVER['REQUEST_URI']);
$i = 0;
while ($i < count($w)) {
    if (($w[$i] == 'opt') && (($i + 2) < count($w))) {
        $_GET['option'] = $w[$i+1];
        $_GET['task'] = $w[$i+2];
        $i = $i+3;
        while (($i+1) < count($w)) {
            $_GET[$w[$i]] = $w[$i+1];
            $i = $i + 2;
        }
        $i = count($w);
    } else {
        $i++;
    }
}

// oauth2/taskName/pName/pvalue.... REQUEST_URI értelmezés
$w = explode('/',$_SERVER['REQUEST_URI']);
$i = 0;
while ($i < count($w)) {
    if (($w[$i] == 'oauth2') && (($i + 1) < count($w))) {
        $_GET['option'] = 'oauth2';
        $_GET['task'] = $w[$i+1];
        $i = $i+2;
        while (($i+1) < count($w)) {
            $_GET[$w[$i]] = $w[$i+1];
            $i = $i + 2;
        }
        $i = count($w);
    } else {
        $i++;
    }
}

// openid/taskName/pName/pvalue.... REQUEST_URI értelmezés
$w = explode('/',$_SERVER['REQUEST_URI']);
$i = 0;
while ($i < count($w)) {
    if ($w[$i] == 'openid') {
        $_GET['option'] = 'openid';
        if (($i+1) < count($w)) {
            $_GET['task'] = $w[$i+1];
        } else {
            $_GET['task'] = 'configuration';
        }
        $i = $i+2;
        while (($i+1) < count($w)) {
            $_GET[$w[$i]] = $w[$i+1];
            $i = $i + 2;
        }
        $i = count($w);
    } else {
        $i++;
    }
}

$request = new Request();
foreach ($_POST as $name => $value) {
	$request->set($name,$value);
}
foreach ($_GET as $name => $value) {
	$request->set($name,$value);
}

$option = $request->input('option','default');
$task = $request->input('task','defaultform');
$lng = $request->input('lng',$request->sessionGet('lng','hu'));
$request->sessionSet('lng',$lng);
$request->sessionSet('option',$option);

if (!defined('LNGDEF')) {
    include './langs/'.$lng.'.php';
}
if (file_exists('./langs/'.$option.'_'.$lng.'.php')) {
    include './langs/'.$option.'_'.$lng.'.php';
}

if (file_exists('./controllers/'.$option.'.php')) {
    
    include_once './controllers/'.$option.'.php';
	$controllerName = ucfirst($option).'Controller';
	
	$controller = new $controllerName ();
	$methods = get_class_methods($controller);
	
	if (in_array($task, $methods)) {
	    $ukloginModel = new UkloginModel();
	    $ukloginModel->init('v1.1');
	    $controller->$task ($request);
	} else {
	    echo 'Fatal error '.$task.' task not found in '.$option.' controller'; exit();
	}
} else {
    echo 'Fatal error '.$option.' controller not found'; exit();
}

?>