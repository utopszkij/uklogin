<?php
class View {
    protected function loadJavaScript($jsName, $params) {
    }
    
    protected function loadJavaScriptAngular($jsName, $params) {
    }
    
    protected function echoHtmlHead() {
    }
    
    protected function echoHtmlPopup() {
    }
    
}

class Controller {
    protected function getModel($modelName) {
        include_once './models/'.$modelName.'.php';
        $modelClassName = $modelName.'Model';
        return new $modelClassName ();
    }
    
    protected function getView($viewName) {
        include_once './views/'.$viewName.'.php';
        $viewClassName = $viewName.'View';
        return new $viewClassName ();
    }
    
    protected function createCsrToken($request, $data) {
        $request->sessionSet('csrToken','testCsrToken');
        $data->csrToken = 'testCsrToken';
    }
    
    protected function checkCsrToken($request) {
        if ($request->input($request->sessionget('csrToken')) != 1) {
            echo '<p>invalid csr token</p>'.JSON_encode($request);
            exit();
        }
    }

    protected function docPage($request, string $viewName) {
        $request->set('sessionid','0');
        $request->set('lng','hu');
        $view = $this->getView($viewName);
        $data = new stdClass();
        $data->option = $request->input('option','default');
        $data->adminNick = $request->sessionGet('adminNick','');
        $view->display($data);
    }
} // class Controller

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

function txt($s) {
    return $s;
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