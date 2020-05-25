<?php
/**
* google login
*
* FIGYELEM NEM LEHET IFRAME -ben!
* ===============================
*
* szükséges config:GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET
* sikeres login után sessionban lévő redirect_uri  cimre ugrik
*
* lásd:
*  https://developers.google.com/identity/protocols/oauth2/web-server
*
* kliens regisztrálása https://console.developers.google.com/
* 1. domain tulajdon igazolása (DNS szerkesztést igényel!)
* 2. credentals ClienId létrehozás
*     kéri a Oauuth conetent screen beállítást .... (nem baj nem publikált és ellenörzött)
* 3. újra credentals létrehozás .... most már végig megy.
*
*/
include_once 'langs/openid_hu.php';

/** google kontroller osztály */
class GoogleloginController extends Controller {

    /** konstruktor */
    function __construct() {
    }

    /**
     * távoli URL hívás
     * @param string $url
     * @param array $post ["név" => "érték", ...]
     * @param array $headers
     * @return string
     */
    protected function callCurl(string $url, array $post=array(), array $headers=array()):string {
        $return = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if(count($post)>0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $return = curl_exec($ch);
        return $return;
    }

    /**
	 * távol API hívás
	 * @param string $url
	 * @param array $post
	 * @param array $headers
	 * @return mixed
	 */
	protected function apiRequest(string $url, array $post=array(), array $headers=array()) {
	        $headers[] = 'Accept: application/json';
	        if (isset($post['access_token'])) {
	            $headers[] = 'Authorization: Bearer ' . $post['access_token'];
                   $post = [];
	        }
	        $response = $this->callCurl($url, $post, $headers);
	        return JSON_decode($response);
	}

	/**
	 * eljárás miután facebookal sikeresen bejelentkezett
	 * ha a user nem létezik létrehozza, ha már létezik akkor beolvassa
	 * @param string $fbId
	 * @param string $fbName
	 * @param string $fbPicture
	 * @return UserRecord
	 */
	protected function readOrCreateUser(string $sub, string $name, string $picture, string $email): UserRecord {
	    $this->getModel('openid'); // user rekord definició
		$user = new UserRecord();
		$w = explode(' ',$name);
		$table = new table('oi_users');
		$table->where(['sub','=','g_'.$sub]);
		$rec = $table->first();
		if ($rec) {
			// megvan a user rekord
			foreach ($rec as $fn => $fv) {
				$user->$fn = $fv;
			}
		} else {
			// még nincs ilyen user rekord, most létrehozzuk
    		$user = new UserRecord();
			$user->sub = 'g_'.$sub;
			$user->picture = $picture;
			$user->family_name = $name;
			$user->nickname = $name;
			$user->pswhash = time();
			$user->code = $user->sub;
			$user->email = $email;
			if (count($w) >= 3) {
				$user->family_name = $w[2];
				$user->middle_name = $w[1];
				$user->given_name = $w[0];
			}
			if (count($w) == 2) {
				$user->family_name = $w[1];
				$user->middle_name = '';
				$user->given_name = $w[0];
			}
            $given_name = $user->given_name;
			if (config('OPENID') != 2) {
			    $user->family_name = '';
			    $user->middle_name = '';
			    $user->given_name = 'g';
			    $user->picture = '';
			    $user->picture = '';
			    $user->email = '';
			}
			$table->insert($user);
			$user->id = $table->getInsertedId();
			$user->nickname = $given_name.'-'.$user->id;
			$table->where(['id','=',$user->id]);
			$table->update($user);
		}
		return $user;
	}

	/**
	 * Átirányit a google loginra  state -ban a session_id() -t küldi
	 * @param Request $request
	 */
	public function authorize(Request &$request) {
		?>
		<html>
		<body>
		wait please ...
		<div style="display:none">
		<form action="https://accounts.google.com/o/oauth2/auth" method="post" name="form1" target="_self">
		<input type="text" name="client_id" value="<?php echo config('GOOGLE_CLIENT_ID'); ?>" />
		<input type="text" name="state" value="<?php echo session_id(); ?>" />
		<input type="text" name="redirect_uri" value="<?php echo config('MYDOMAIN')?>/opt/googlelogin/code/" />
		<input type="text" name="scope" value="email profile openid" />
		<input type="text" name="policy_uri" value="<?php echo $request->sessionGet('policy_uri'); ?>" />
		<input type="text" name="response_type" value="code" />
		<button type="submit">OK</button>
		</form>
		</div>
		<script type="text/javascript">
			document.forms.form1.submit();
		</script>
		</body>
		</html>
		<?php
	}

	/**
	 * google oauth hivta vissza
	 * 1. lekérdezi a google - ból az acces_token-t
	 * 2. lekérdezi a google - ból a userinfokat
	 * 3. szükség esetén létrehozza a user rekordot
	 * 4. uklogin bejelentkezést csinál
	 * 5. ezután a sessionban lévő redirect_uri -ra ugrik, ez pedig a scope elfogadtató
	 * képernyőt jeleníti majd meg.
	 * sessionban érkezik: client_id, policy_url, scope, redirect_uri, state, nonce
	 * @param Request $request - code, state state-ban a session_id érkezik
	 */
	public function code(Request &$request) {

	    $code = $request->input('code');
	    $state = $request->input('state');
	    $this->sessionChange($state, $request);
   	    $token = $this->apiRequest(
   	      'https://oauth2.googleapis.com/token',
   		   ['client_id' => config('GOOGLE_CLIENT_ID'),
             'client_secret' => config('GOOGLE_CLIENT_SECRET'),
   		     'grant_type' => 'authorization_code',
             'redirect_uri' => config('MYDOMAIN').'/opt/googlelogin/code/',
             'state' => $state,
             'code' => $code
   		   ]
	    );
		if (isset($token->access_token)) {
            $url="https://www.googleapis.com/oauth2/v1/userinfo?alt=json";
            $request->sessionSet('access_token', $token->access_token);
   			$guser = $this->apiRequest(
   				$url,
				['access_token' => $token->access_token]
			);
	    	if (!isset($guser->error)) {
	    	    // $fbuser alapján bejelentkezik (ha még nincs user rekord létrehozza)
	    	    // $fbuser->name, ->id ->picture->data->url
	    	    $user = $this->readOrCreateUser($guser->id, $guser->name, $guser->picture, $guser->email);
	    	    $p = new Params();
	    	    $model = $this->getModel('openid');
	    	    $view = $this->getView('openid');
	    	    $p->client_id = $request->sessionGet('client_id');
	    	    $p->scope = $request->sessionGet('scope');
	    	    // $client = $model->getApp($p->client_id);
	    	    $p->clientTitle = '';
	    	    $p->msgs = [];
	    	    $p->msgs[] = 'Ez egy "nem hitelesített" felhasználói fiók.';
	    	    $p->msgs[] = 'Egyes alkalmazások korlátozhatják az ilyen fiók használatát.';
	    	    $p->msgs[] = 'A profil oldalon a "Hitelesítés" gombnál olvashatsz további információkat.';
	    	    $request->sessionSet('acceptScopeUser', $user);
	    	    $request->sessionSet('loggedUser', new UserRecord());
	    	    $this->createCsrToken($request, $p);
	    	    $p->nickname = $user->nickname;
	    	    $view->scopeForm($p);
	    	} else {
					echo 'Fatal error in google login. wrong user data '.json_encode($guser); return;
			}
		 } else {
			echo 'Fatal error in google login. access_token not found '.
			'code = '.json_encode($code).
			' response= '.json_encode($token);
			return;
		 }
	}
}
?>
