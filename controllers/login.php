<?php
include 'vendor/autoload.php';


class LoginController {
    /**
     * szolgáltatás hívása ; login form kirajzolása iframe -be
     * @param object $request 
     * @return void
     */
    public function form($request) {
        if ($request->sessionGet('adminNick') == '') {
            $view = getView('login');
            $p = new stdClass();
            $p->client_id = 12;
            $p->state = $request->input('state', MYDOMAIN.'/opt/appregist/adminform');
            $p->adminNick = $request->sessionget('adminNick','');
            $view->loginForm($p);
        } else {
            echo '<html>
                <body>
                <script type="text/javascript">
                    document.location="'.MYDOMAIN.'/opt/appregist/adminform";
                </script>
                </body>
                </html>
                ';
        }
    }
    
    /**
     * uklogin szolgáltatás callback function
     * sikeres login után: sessionba teszi a nicknevet, redirect adminform
     * @param object $request - code
     * @retrun void
     */
    public function code($request) {
        $code = $request->input('code');
        $state = urldecode($request->input('state',MYDOMAIN));
        $url = MYDOMAIN.'/oauth2/access_token/client_id/12/client_secret/13/code/'.$code;
        if (MYDOMAIN != '') {
            $result = JSON_decode(implode("\n", file($url)));
        } else {
            $result = new stdClass();
            $result->access_token = '0';
        }
        if ((!isset($result->error)) && (isset($result->access_token))) {
            // access_token sikeresen lekérve. Userinfo kérése
            $access_token = $result->access_token;
            $url = MYDOMAIN.'/oauth2/userinfo/access_token/'.$access_token;
            if (MYDOMAIN != '') {
                $result = JSON_decode(implode("\n", file($url)));
            } else {
                $result = new stdClass();
                $result->nick = 'unittest';
            }
            if ($result->nick != 'error') {
                $request->sessionSet('adminNick',$result->nick);
                echo '<html>
                <body>
                <script type="text/javascript">
                    parent.document.location="'.$state.'";
                </script>
                </body>
                </html>
                ';
            }
        } else {
            echo 'fatal error in login';
        }
    }
    
    public function logout($request) {
        $request->sessionSet('adminNick','');
        $request->sessionSet('csrToken','');
        if (!headers_sent()) {
            header('Location: '.MYDOMAIN);
        }
    }
}
?>