<?php
if (isset($_GET['sid'])) {
    session_id(strip_tags($_GET['sid']));
}
if (isset($_POST['sid'])) {
    session_id(strip_tags($_POST['sid']));
}
session_start();
include_once './.config.php';
include_once './core/database.php';
include_once './core/framework.php';

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

$request = new Request();
foreach ($_POST as $name => $value) {
	$request->set($name,$value);
}
foreach ($_GET as $name => $value) {
	$request->set($name,$value);
}
$option = $request->input('option','default');
$task = $request->input('task','default');
$lng = $request->input('lng',$request->sessionGet('lng',DEFLNG));
$request->sessionSet('lng',$lng);
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
	   $controller->$task ($request);
	} else {
	    echo 'Fatal error '.$task.' task not found in '.$option.' controller'; exit();
	}
} else {
    echo 'Fatal error '.$option.' controller not found'; exit();
}
?>