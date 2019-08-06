<?php

include 'vendor/autoload.php';

class Oauth2Controller {
    
    /**
     * user regist első képernyő (pdf letöltési link, aláirt pdf feltöltési form, help)
     * client_id tárolása sessionba
     * app->css -t használja
     * @param Request $request - client_id
     * @return void
     */
    public function registform($request) {
        $appModel = getModel('appregist');
        $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
        $view = getView('oauth2');
        $client_id = $request->input('client_id','?');
        $app = $appModel->getData($client_id);
        if ($app) {
            $data = new stdClass();
            // create csrr token
            createCsrToken($request, $data);
            // save client_id a sessionba
            $request->sessionSet('client_id', $client_id);
            // képernyő kirajzolás
            $data->client_id = $client_id;
            $data->appName = $app->name;
            $data->extraCss = $app->css;
            $data->nick = $request->input('nick','');
            $data->title = 'LBL_REGISTFORM1';
            $view->registForm1($data);
        } else {
            $view->errorMsg(['ERROR_NOTFOUND']);
        }
	}
	
	/**
	 * Aláírandó pdf előállítása
	 * @param Request $request {client_id}
     * @return void
	 */
	public function pdf($request) {
	    $client_id = $request->input('client_id','?');
	    require('./core/fpdf/fpdf.php');
	    
	    $pdf = new FPDF();
	    $pdf->AddPage();
	    $pdf->SetFont('Arial','B',16);
	    $pdf->Cell(40,10,'client_id='.$client_id);
	    $pdf->Output();
	}
	
	/**
	 * feltöltött $tmpdir/signed.pdf feldolgozása 
	 * @param string $tmpDir
	 * @param string $filename
	 * @param string $client_id
	 * @return object {error, signHash}
	 */
	protected function processUploadedFile(string $tmpDir, string $fileName,  string $client_id) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = '';
	    $igazolasPWD = $tmpDir;
	    $filePath = $tmpDir.'/'.$fileName;
	    $xmlArray = [];
	    $pdfContent = '';
	    $pdfsigFalse = false; // ha nem sikerüét futtani a pdfsig -et akkor tuue.
	    
	    // aláirás ellenörzés. 
	    // Ha a pdfsig -es lekérdezés nem sikerül akkor 
	    // egyszerüsitett ellenörzést végez: megnézi van-e benne 'adbe.pkcs7.detached' string.
	    
	    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
	    $signatureArray[] = ''; // hogy biztos legyen 1. indexü elem
	    if (($signatureArray[1] == 'Segmentation fault') ||
	        ($signatureArray[0] == 'sh: pdfsig: command not found')) {
	            // egyszerüsitett ellenörzés: ha van benne adbe.pkcs7.detached akkor aláírtnak tekintem,
	        $res->error = 'ERROR_PDF_SIGN_ERROR';
	        $handle = fopen($filePath, 'r');
	        while (($buffer = fgets($handle)) !== false) {
	           $pdfContent .= $buffer;
	            if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
	              $res->error = ''; // valószinüleg alá van irva, de nem biztos, hogy sértetlen.
	           }
	        }
	        $pdfSigFalse = true;
	    } else {
	        if (in_array('File \'' . $filePath . '\' does not contain any signatures' , $signatureArray)) {
	            $res->error = 'ERROR_PDF_SIGN_ERROR';
	        }
	        if (!in_array('  - Signature Validation: Signature is Valid.' , $signatureArray)) {
	            $res->error = 'ERROR_PDF_SIGN_ERROR';
	        }
	        if (!in_array('  - Signer Certificate Common Name: AVDH Bélyegző' , $signatureArray)) {
	            $res->error = 'ERROR_PDF_SIGN_ERROR';
	        }
	    }

	    // pdf txt tartalom ellenörzése
	    
	    $parser = new \Smalot\PdfParser\Parser();
	    $pdf    = $parser->parseFile($filePath);
	    $text = $pdf->getText();
	    if ($text != 'client_id='.$client_id) {
	        $res->error = 'ERROR_PDF_SIGN_ERROR '.$text;
	    }
	    
	    if ($res->error == '') {
	        // a pdf -ből kibontja az igazolas.pdf mellékeltet az $igazolasPWD alkönyvtárba
	        shell_exec('pdfdetach -save 1 -o '.$igazolasPWD.'/igazolas.pdf '.escapeshellarg($filePath));
	        unlink($filePath);
	        if (!is_file($igazolasPWD.'/igazolas.pdf')) {
	            // nem sikerült igazolas.pdf -et kibontani
	            if (($pdfSigFalse) && ($res->error == '')) {
	                // a pdfSig sem volt futtatható akkor - jobb hijján - elfogadjuk
	                // ilyenkor az egész pdf fájlból képezzük a signHash értéket.
	                $res->signHash = hash('sha256', $pdfContent ,false);
	                return $res;
	            } else {
	                // a pdfsig futtatható valt akkor ez umbuldált pdf fájl, nem fogadjuk el.
    	            $res->error = 'ERROR_PDF_SIGN_ERROR';
	            }
			}
        }
	            
        if ($res->error == '') {
	        // a $igazolasPWD könyvtárban lévő igazolas.pdf fájlból kibontja a meghatalmazo.xml -t
            shell_exec('pdfdetach -save 1 -o '.$igazolasPWD.'/meghatalmazo.xml '.escapeshellarg($igazolasPWD.'/igazolas.pdf'));
            unlink($igazolasPWD.'/igazolas.pdf');
            if (!is_file($igazolasPWD.'/meghatalmazo.xml')) {
			      $res->error =  'ERROR_PDF_SIGN_ERROR';
	        }
	    }
	    
	    if ($res->error == '') {
            // feldolgozza a meghatalmazo.xml fájlt
	        $email = '';
	        $emails = [''];
	        $xmlStr = implode("\n", file($igazolasPWD.'/meghatalmazo.xml'));
	        preg_match('/emailAddress\"\>.*\</', $xmlStr , $emails);
	        if (count($emails) > 0) {
	            $email = $emails[0];
	        }
            if (false === strpos($email, '@')) {
	            $res->error = 'ERROR_PDF_SIGN_ERROR';
	        } else {
	            $res->signHash = hash('sha256', $email ,false);
	        }
	        unlink($igazolasPWD.'/meghatalmazo.xml');
	    }
	    return $res;
	}
	
	/**
	 * signHash kinyerése a feltöltött aláírt pdf fájlból
	 * sessionban érkezik a client_id
	 * @param Request $request {signed_pdf}
	 * @param string $tmpDir
	 * @return object {error, signHash}
	 */
	public function getSignHash($request, string $tmpDir) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = '';
	    if ($request->input('signed_pdf','') == 'test_notuploaded') { // for unittest
	        $res->error = 'ERROR_PDF_NOT_UPLOADED';
	    } else {
    	    $fileName = getUploadedFile('signed_pdf', $tmpDir);
    	    if ($fileName != '') {
    	        $res = $this->processUploadedFile($tmpDir, $fileName, $request->sessionGet('client_id'));
    	    } else {
    	        $res->error = 'ERROR_PDF_NOT_UPLOADED';
    	    }
	    }
	    return $res;
	}
	
	/**
	 * van már ezzel a signHas -al regisztráció?
	 * @param string $client_id
	 * @param string $signHash
	 * @return object {error, signhash, nick}
	 */
	public function checkSignHashExist(string $client_id, string $signHash) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = $signHash;
	    $res->nick = '';
	    $model = getModel('oauth2');
	    $user = $model->getUserBySignHash($client_id, $signHash);
	    if ($user) {
	        $res->error = 'ERROR_PDF_SIGN_EXISTS';
	        $res->nick = $user->nick;
	    }
	    return $res;
	}
	
	
	/**
	 * user egist második képernyő (aláirt pdf feltöltés feldolgozása, nick/psw1/psw2 form)
	 * sessionban érkezik client_id
     * app->css -t használja
	 * @param Request $request - signed_pdf, cssrtoken, nick
     * @return void
	 */
	public function registform2($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
	    $view = getView('oauth2');
	    
	    // csrttoken ellnörzés
	    checkCsrToken($request);
	    
	    // client_id sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $app = $appModel->getData($client_id);
	    if ($app == false) {
	        $app = new stdClass();
	        $app->name = 'testApp';
	        $app->css = '';
	    }
	    // munkakönyvtár létrehozása a sessionId -t használva -> $tmpDir
	    $sessionId = session_id();
	    $tmpDir = 'work/tmp'.$sessionId;
	    if (!is_dir($tmpDir)) {
	        mkdir($tmpDir, 0777);
	    }
	    // biztos ami biztos...
	    $files = glob($tmpDir.'/*'); // get all file names
	    foreach ($files as $file) { // iterate files
	        if (is_file($file))
	            unlink($file); // delete file
	    }
	    
	    // uploaded file feldolgozása
	    // aláírás ellenörzés, $signHash kinyerése
	    $res = $this->getSignHash($request, $tmpDir);
	    if ($res->error != '') {
	        // $res->error formája ERRORTOKEN(num)
	        $w = explode('(',$res->error);
	        if (count($w) > 1) {
	            $w[1] = '('.$w[1];
	        } else {
	            $w[1] = '';
	        }
	        $view->errorMsg([$w[0],  $w[1]]);
	    }

	    if ($res->error == '') {
	        $res = $this->checkSignHashExist($client_id, $res->signHash);
	        if ($res->error != '') {
	            $view->errorMsg([$res->error, 'nick:'.$res->nick]);
	        }
	    }

	    // munkakönyvtár teljes törlése
	    if (is_dir($tmpDir)) {
	        $files = glob($tmpDir.'/*'); // get all file names
	        foreach ($files as $file) { // iterate files
	            if (is_file($file)) {
	                unlink($file); // delete file
	            }
	        }
	        rmdir($tmpDir);
	    }
	    
	    if ($res->error == '') {
	        $data = new stdClass();
	        createCsrToken($request, $data);
	        $request->sessionSet('signHash', $res->signHash);
	        $request->sessionSet('client_id', $client_id);
	        // képernyő kirajzolás
	        $data->msgs = [];
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->nick = '';
	        $data->psw1 = '';
	        $data->psw2 = '';
	        $data->nick = $request->input('nick','');
	        $data->title = 'LBL_REGISTFORM2';
	        $view->registForm2($data);
	    }
	}
	
	/**
	 * user Regist 2.képernyő feldolgozás)
	 * sessionban érkezik client_id
	 * @param Request $request - nick, psw1, psw2, csrToken
     * @return void
	 */
	public function doregist($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
	    $view = getView('oauth2');
	    
	    // csrttoken ellnörzés
	    checkCsrToken($request);
	    	    
	    // client_id és signHash sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $signHash = $request->sessionGet('signHash','');
	    if ($signHash == '') {
	        echo '<p>invalid signHash</p>';
	        exit();
	    }
	    
	    $app = $appModel->getData($client_id);
	    $data = new stdClass();
	    // kitöltés ellenörzések
	    $data->client_id = $client_id;
	    $data->signHash = $signHash;
	    $data->nick = $request->input('nick','');
	    $data->psw1 = $request->input('psw1','');
	    $data->psw2 = $request->input('psw2','');
	    $msgs = $model->check($data);
	    if (count($msgs) > 0) {
	        createCsrToken($request, $data);
	        $request->sessionSet('signHash', $data->signHash);
	        $request->sessionSet('client_id', $data->client_id);
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->msgs = $msgs;
	        $data->title = 'LBL_REGISTFORM2';
	        $view->registForm2($data);
	    } else {
	       $data->enabled = 1;
	       $data->errorcount = 0;
	       $data->code = '';
	       $data->access_token = '';
	       $data->codetime = '';
	       $data->pswhash = hash('sha256', $data->psw1, false);
	       unset($data->psw1);
	       unset($data->psw2);
	       unset($data->msgs);
	       $msgs = $model->addUser($data);
	       if (count($msgs) == 0) {
	           $view->successMsg(['USER_SAVED']);
	       } else {
	           createCsrToken($request, $data);
	           $request->sessionSet('signHash', $data->signHash);
	           $request->sessionSet('client_id', $data->client_id);
	           $data->appName = $app->name;
	           $data->extraCss = $app->css;
	           $data->msgs = $msgs;
	           $data->title = 'LBL_REGISTFORM2';
	           $data->psw1 = '';
	           $data->psw2 = '';
	           $view->registForm2($data);
	       }
	    }
	}
	
	/**
	 * echo loginform
	 * @param Request $request - client_id és más paraméterek is jöhetnek, ezeket sessionba tárolja
	 * @return void
	 */
	public function loginform($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
	    $view = getView('oauth2');
	    $client_id = $request->input('client_id','');
	    $app = $appModel->getData($client_id);
	    if ($app) {
	        $data = new stdClass();
	        createCsrToken($request, $data);
	        $request->sessionSet('client_id', $client_id);
	        
	        // egyébb érkező paraméterek tárolása
	        $extraParams = [];
	        foreach ($_GET as $fn => $fv) {
	            if ($fn != 'client_id') {
	                $extraParams[$fn] = $fv;
	            }
	        }
	        foreach ($_POST as $gn => $fv) {
	            if ($fn != 'client_id') {
	                $extraParams[$fn] = $fv;
	            }
	        }
            $request->sessionSet('extraParams',$extraParams);
	        
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->nick = '';
	        $data->psw1 = '';
	        $data->msgs = [];
	        $view->loginform($data);
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	}
	
	/**
	 * Login képernyő ujboli kirajzolás hiba esetén
	 * @param Request $request
	 * @param View $view
	 * @param App $app
	 * @param array $msgs
	 * @return void
	 */
	protected function recallLoginForm(&$request, &$view, &$app, $msgs) {
	    $data = new stdClass();
	    createCsrToken($request, $data);
	    $data->appName = $app->name;
	    $data->extraCss = $app->css;
	    $data->nick = $request->input('','');
	    $data->psw1 = '';
	    $data->msgs = $msgs;
	    $view->loginform($data);
	}
	
	/**
	 * login képernyő feldolgozása
	 * sessionban érkezik a client_id
	 * @param Request $request csrToken, nick, psw1
	 * @return string -- for unittest: return code
	 */
	public function dologin($request): string {
	    
	    checkCsrToken($request);
	    
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    $user = $model->getUserByNick($client_id, $nick);
	    $app = $appModel->getData($client_id);
	    if ($app == '') {
	        // nem jó client_id van a sessionban!
	        echo '<p class="alert alert-danger">Invalid client_id</p>'; exit();
	    }
	    
	    if ($user) {
	        // letiltott user
	        if ($user->enabled == 0) {
	            // login képernyő visszahívása
	            $request->sessionSet('client_id', $client_id);
	            $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', 0] );
	        } else if ($user->pswhash == hash('sha256', $psw, false)) {
    	        // sikeres login, code és accessToken generálás, callback visszahívás
    	        $user->code = md5(random_int(1000000, 5999999)).$user->id;
    	        $user->access_token = md5(random_int(6000000, 9999999)).$user->id;
    	        $user->codetime = date('Y-m-d H:i:s');
    	        $model->updateUser($user);
    	        $url = $app->callback;
    	        if (strpos($url, '?') > 0) {
    	            $url .= '&';
    	        } else {
    	            $url .= '?';
    	        }
    	        $url .= 'code='.$user->code;
    	        
    	        // extra paraméterek feldolgozása
    	        $extraParams = $request->sessionGet('extraParams',[]);
    	        if (count($extraParams) > 0) {
    	            foreach ($extraParams as $fn => $fv) {
    	                if (strpos($url, '?') > 0) {
    	                    $url .= '&';
    	                } else {
    	                    $url .= '?';
    	                }
    	                $url .= $fn.'='.$fv;
    	            }
    	         }
    	         if (!headers_sent()) {
    	           header('Location:'.$url.'", true, 301');
    	         } else {
    	            echo 'headers sent. Not redirect '.$url; 
    	            return $user->code;
    	         }
	        } else {
	            // jelszó hiba
	            $user->errorcount++;
	            if ($user->errorcount >= $app->falseLoginLimit) {
	                $user->enabled = 0;
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            // login képernyő visszahívása
	            $request->sessionSet('client_id', $client_id);
	            $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	        }
	    } else {
	        // nick név hiba
	        $tryCount = 10;
	        // login képernyő visszahívása
	        $request->sessionSet('client_id', $client_id);
	        $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	    }
	    return '';
	}
	
	/**
	 * oAuth2 backend function  
	 * echo  {"access_token":"xxxxx"} vagy {"error":"xxxxxx"}
	 * @param Request $request code, client_id, client_secret
	 * @return string access_token  -- only for unittest
	 */
	public function access_token($request) {
	    $code = $request->input('code');
	    $client_id = $request->input('client_id');
	    $client_secret = $request->input('client_secret');
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); 
	    $user = $model->getUserByCode($code);
	    $app = $appModel->getData($client_id);
	    
	    $access_token = '';
	    if (($app) && ($user)) {
	        if (($app->client_secret == $client_secret) && 
	            ($user->enabled == 1) &&
	            ($user->client_id == $app->client_id)) {
	            $access_token = $user->access_token;    
                echo '{"access_token":"'.$user->access_token.'"}';	            
	        } else {
	            echo '{"error":"client_secret invalid"}';
	        }
	    } else {
	        echo '{"error":"client_id or code invalid"}';
	    }
	    return $access_token;
	}
	
	/**
	 * oAuth2 backend function 
	 * echo {"nickname":"..."} vagy {"error":"not found"}
	 * @param Request $request   access_token
	 * @return void
	 */
	public function userinfo($request) {
        $access_token = $request->input('access_token');
        $model = getModel('oauth2'); 
        $rec = $model->getUserByAccess_token($access_token);
        
        if ($rec) {
            echo '{"nick":"'.$rec->nick.'"}';
        } else {
            echo '{"error":"not found"}';
        }
	}
	
}
?>