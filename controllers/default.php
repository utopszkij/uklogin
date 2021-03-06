<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */


/**
 *  DefaultController  kezdőlap 
 */  
class DefaultController extends Controller {
    
    /** controller neve */
    protected $cName = 'default';
    
    /**
     * adat lekérés távoli url -ről curl POST -al
     * @param string $url
     * @param array $fields
     * @return string
     */
    protected function getFromUrl(string $url, array $fields = []): string {
        $fields_string = '';
        $ch = curl_init();
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string, '&');
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_POST, count($fields));
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        return curl_exec($ch);
    }
    
    /**
     * kezdőlap megejelenítése
     * @param Request $request
     */
	public function defaultform(Request $request) {
      // echo frontpage
	    $request->set('sessionid','0');
		$request->set('lng','hu');
		$view = $this->getView('frontpage');
		$model = $this->getModel('frontpage');
		$data = $this->init($request,[]);
		$data->option = $request->input('option','default');
		$data->appCount = $model->getAppCount();
		$data->userCount = $model->getUserCount();
		$data->adminNick = $request->sessionGet('adminNick','');
		$data->loggedUser = $request->sessionGet('loggedUser','');
		$data->access_token = $request->sessionGet('access_token','');
		$view->display($data);
	}
	
	/**
	 * openid login redirect_uri
	 * @param Request $request - token, state, nonce=nextUrl
	 */
	public function logged(Request $request) {
	    $access_token = $request->input('token');
	    $nonce = $request->input('nonce');
	    // userinfo lekérdezése
	    $url = MYDOMAIN.'/openid/userinfo/';
	    $fields = ["access_token" => $access_token];
	    $result = JSON_decode($this->getFromUrl($url, $fields));
	    if (is_object($result)) {
	       $request->sessionSet('adminNick',$result->nickname);
	       $request->sessionSet('access_token',$access_token);
	    }
	    if ($nonce == '') {
            $url = MYDOMAIN;
        } else {
            $url = urldecode($nonce);
        }
        ?>
        <script type="text/javascript">
        	window.parent.document.location = "<?php echo $url; ?>";
        </script>
        <?php 
	}
	
	/**
	 * after logout redirect_uri
	 * @param Request $request
	 */
	public function logout(Request $request) {
	    $this->getModel('openid'); // userRecord
	    $request->sessionSet('adminNick','');
	    $request->sessionSet('access_token','');
	    $request->sessionSet('loggedUser',new UserRecord());
	    redirectTo(MYDOMAIN);
	}
	
	
}
?>