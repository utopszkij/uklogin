<?php
class Request {
    protected $sessions;
    function __construct() {
        $this->sessions = new stdClass();
    }
    public function set($name, $value) {
        $this->$name = $value;
    }
    public function input($name, $def='') {
        $result = $def;
        if (isset($this->$name)) $result = $this->$name;
        return $result;
    }
    public function sessionSet($name,$value) {
        $this->sessions->$name = $value;
    }
    public function sessionGet($name, $def = '') {
        $result = $def;
        if (isset($this->sessions->$name)) $result = $this->sessions->$name;
        return $result;
    }
    public function session_count() {
        return 1;
    }
}

function getModel($modelName) {
    include_once './models/'.$modelName.'.php';
    $modelClassName = $modelName.'Model';
    return new $modelClassName ();
}

function getView($viewName) {
    include_once './views/'.$viewName.'.php';
    $viewClassName = $viewName.'View';
    return new $viewClassName ();
}

function loadJavaScript($jsName, $params) {
    return '';
}

function loadJavaScriptAngular($jsName, $params) {
    return '';
}

function txt($s) {
    return $s;
}

function htmlHead() {
    return '';
}
function htmlPopup() {
    return '';
}
?>