<?php
global $redirectURL;

interface ModelObject {
    
}

interface ViewObject {
}

interface ControllerObject {
}

interface  RequestObject {
    public function input(string $name, $default = '');
    public function set(string $name, $value);
    public function sessionGet(string $name, $default='');
    public function sessionSet(string $name, $value);
    public function session_init(string $sessionId);
    public function session_save(string $sessionId);
    public function session_count(): int;
}


class View implements ViewObject {
    public function loadJavaScript(string $jsName, object $params) {
    }
    
    public function loadJavaScriptAngular(string $jsName, object $params) {
    }
    
    public function echoHtmlHead() {
    }
    
    public function echoHtmlPopup() {
    }
    
    public function echoJsLngDefs(array $items) {
        
    }
    
    public function echoLngHtml(string $htmlName, $p) {
        global $REQUEST;
        $lng = $REQUEST->sessionget('lng','hu');
        if (file_exists('langs/'.$htmlName.'_'.$lng.'.html')) {
            include 'langs/'.$htmlName.'_'.$lng.'.html';
        } else if (file_exists('langs/'.$htmlName.'.html')) {
            include 'langs/'.$htmlName.'.html';
        } else {
            echo '<p>'.$htmlName.' html file not found.</p>';
        }
    }

}

class Model implements ModelObject {
    
}

class Controller implements ControllerObject {
    protected function getModel(string $modelName)  {
        include_once './models/'.$modelName.'.php';
        $modelClassName = $modelName.'Model';
        return new $modelClassName ();
    }
    
    protected function getView(string $viewName) {
        $viewClassName = $viewName.'View';
        include_once './views/'.$viewName.'.php';
        return new $viewClassName ();
    }
    
    protected function createCsrToken(RequestObject $request, object $data) {
        $request->sessionSet('csrToken','testCsrToken');
        $data->csrToken = 'testCsrToken';
    }
    
    protected function checkCsrToken(RequestObject $request) {
        if ($request->input($request->sessionget('csrToken')) != 1) {
            echo '<p>invalid csr token</p>'.JSON_encode($request);
            exit();
        }
    }

    protected function docPage(RequestObject $request, string $viewName) {
        $request->set('sessionid','0');
        $request->set('lng','hu');
        $view = $this->getView($viewName);
        $data = new stdClass();
        $data->option = $request->input('option','default');
        $data->adminNick = $request->sessionGet('adminNick','');
        $view->display($data);
    }
} // class Controller

class Request implements RequestObject {
    public $params = [];
    protected $sessions;
    function __construct() {
        $this->sessions = new stdClass();
    }
    public function set(string $name, $value) {
        $this->params[$name] = $value;
    }
    public function input(string $name, $def='') {
        $result = $def;
        if (isset($this->params[$name])) $result = $this->params[$name];
        return $result;
    }
    public function sessionSet(string $name, $value) {
        $this->sessions->$name = $value;
    }
    public function sessionGet(string $name, $def = '') {
        $result = $def;
        if (isset($this->sessions->$name)) $result = $this->sessions->$name;
        return $result;
    }
    public function session_count(): int {
        return 1;
    }
    public function session_init(string $sessionId): int {
        $this->sessions = new stdClass();
    }
    public function session_save(string $sessionId) {
    }
}

function txt(string $s): string {
    return $s;
}

function config(string $s): string {
    if (defined($s)) {
       return constant($s);   
    } else {
        return $s;
    }
}

/**
 * A "tests" könyvtárból másol a terget -be
 * postname -t másol target ben a str_replace('_','.',$postName)
 * @param string $postName
 * @param string $target
 * @return string
 */
function getUploadedFile(string $postName, string $targetDir): string {
    $name = $postName;
    $outName = str_replace('_', '.',$name);
    if (file_exists('./tests/'.$outName)) {
        if (copy ('./tests/'.$outName, $targetDir.'/'.$outName)) {
            $result = $outName;
        } else {
            $result = 'copyError';
        }
    } else {
        $result = 'not found ./tests/'.$outName;
    }
    return $result;
}

function redirectTo($url) {
    global $redirectURL;
    $redirectURL = $url;
}

function sendEmail(string $to, string $subject, string $body): bool {
    $result = true;
    return $result;
}
?>