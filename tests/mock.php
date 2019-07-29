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
/**
 * A "tests" könyvtárból másol a terget -be
 * postname -től függetlenül mindig ugyanazt a file-t.
 * @param string $postName
 * @param string $target
 * @return string
 */
function getUploadedFile(string $postName, string $targetDir): string {
    $name = 'avdhA3-18840f38-7adf-4f8a-a8b2-c3e307d63b48.pdf';
    if (file_exists('./tests/'.$name)) {
        if (copy ('./tests/'.$name, $targetDir.'/'.$name)) {
            $result = $name;
        } else {
            $result = '';
        }
    } else {
        $result = '';
    }
    return $result;
}

?>