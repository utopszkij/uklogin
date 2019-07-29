<?php
class Oauth2Controller {
    
    /**
     * user egist első képernyő (pdf letöltési link, aláirt pdf feltöltési form, help)
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
            // create csrr token
            $request->sessionSet('csrToken', random_int(1000000,9999999));
            // save client_id a sessionba
            $request->sessionSet('client_id', $client_id);
            // képernyő kirajzolás
            $data = new stdClass();
            $data->csrToken = $request->sessionGet('csrToken','');
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
	 * @return object {error, signHash}
	 */
	protected function processUploadedFile(string $tmpDir, string $fileName) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = '';
	    $igazolasPWD = $tmpDir;
	    $filePath = $tmpDir.'/'.$fileName;
	    $xmlArray = [];
	    
	    // aláirás ellenörzés. 
	    // Ha a pdfsig -es lekérdezés nem sikerül akkor 
	    // egyszerüsitett ellenörzést végez: megnézi van-e benne 'adbe.pkcs7.detached' string.
	    
	    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
	    $signatureArray[] = ''; // hogy biztos legyen 1. indexü elem
	    if (($signatureArray[1] == 'Segmentation fault') ||
	        ($signatureArray[0] == 'sh: pdfsig: command not found')) {
	        // egszerüsitett ellenörzés
	        $res->error = 'ERROR_PDF_SIGN_ERROR';
	        $handle = fopen($filePath, 'r');
	        while (($buffer = fgets($handle)) !== false) {
	           if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
	              $res->error = ''; // valószinüleg alá van irva, de nem biztos, hogy sértetlen.
	              break;
	           }
	        }
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

	    if ($res->error == '') {
	        // a pdf -ből kibontja az igazolas.pdf mellékeltet az $igazolasPWD alkönyvtárba
	        shell_exec('pdfdetach -save 1 -o '.$igazolasPWD.'/igazolas.pdf '.escapeshellarg($filePath));
	        unlink($filePath);
	        if (!is_file($igazolasPWD.'/igazolas.pdf')) {
	            $res->error = 'ERROR_PDF_SIGN_ERROR'; 
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
	 * get signHash from upladed signed_pdf file
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
    	        $res = $this->processUploadedFile($tmpDir, $fileName);
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
	    if ($request->input( $request->sessionGet('csrToken',''),'') != 1) {
	        echo '<p>invalid csr token</p>';
	        exit();
	    }
	    
	    // client_id sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $app = $appModel->getData($client_id);
	    
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
	        $request->sessionSet('signHash', $res->signHash);
	        $request->sessionSet('csrToken', random_int(1000000,9999999));
	        $request->sessionSet('client_id', $client_id);
	        // képernyő kirajzolás
	        $data = new stdClass();
	        $data->msgs = [];
	        $data->csrToken = $request->sessionGet('csrToken','');
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
	    if ($request->input( $request->sessionGet('csrToken',''),'') != 1) {
	        echo '<p>invalid csr token</p>';
	        exit();
	    }
	    
	    // client_id és signHash sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $signHash = $request->sessionGet('signHash','');
	    if ($signHash == '') {
	        echo '<p>invalid signHash</p>';
	        exit();
	    }
	    
	    $app = $appModel->getData($client_id);
	    // kitöltés ellenörzések
	    $data = new stdClass();
	    $data->client_id = $client_id;
	    $data->signHash = $signHash;
	    $data->nick = $request->input('nick','');
	    $data->psw1 = $request->input('psw1','');
	    $data->psw2 = $request->input('psw2','');
	    $msgs = $model->check($data);
	    if (count($msgs) > 0) {
	        $request->sessionSet('signHash', $data->signHash);
	        $request->sessionSet('csrToken', random_int(1000000,9999999));
	        $request->sessionSet('client_id', $data->client_id);
	        $data->csrToken = $request->sessionGet('csrToken','');
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
	           $request->sessionSet('signHash', $data->signHash);
	           $request->sessionSet('csrToken', random_int(1000000,9999999));
	           $request->sessionSet('client_id', $data->client_id);
	           $data->csrToken = $request->sessionGet('csrToken','');
	           $data->appName = $app->name;
	           $data->extraCss = $app->css;
	           $data->msgs = $msgs;
	           $data->title = 'LBL_REGISTFORM2';
	           $view->registForm2($data);
	       }
	    }
	}
}
?>