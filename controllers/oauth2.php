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
        $view = getView('oauth2');
        $client_id = $request->input('client_id','?');
        $app = $appModel->getData($client_id);
        $request->sessionSet('nick','');
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
	 * user regist első képernyő (pdf letöltési link, aláirt pdf feltöltési form, help)
	 * client_id sessionbol jön
	 * app->css -t használja
	 * @param Request $request - nick
	 * @return void
	 */
	public function forgetpsw($request) {
	    $appModel = getModel('appregist');
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','?');
	    $nick = $request->input('nick','');
	    if ($nick == '') {
	        $view->errorMsg(['ERROR_NICK_EMPTY']);
	        return;
	    }
	    $app = $appModel->getData($client_id);
	    if ($app) {
	        $data = new stdClass();
	        // create csrr token
	        createCsrToken($request, $data);
	        // save client_id a sessionba
	        $request->sessionSet('client_id', $client_id);
	        $request->sessionSet('nick', $nick);
	        
	        // képernyő kirajzolás
	        $data->client_id = $client_id;
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->nick = $nick;
	        $data->title = 'FORGET_PSW';
	        $view->registForm1($data);
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	}
	
	/**
	 * jelszó változtatás
	 * sessionban érkezik a client_id
	 * @param unknown $request - nick, psw1, csrtoken
	 */
	public function changepsw($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2');
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','?');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    if ($nick == '') {
	        $view->errorMsg(['ERROR_NICK_EMPTY']);
	        return;
	    }
	    if ($psw == '') {
	        $view->errorMsg(['ERROR_PSW_EMPTY']);
	        return;
	    }
	    
	    $app = $appModel->getData($client_id);
	    $user = $model->getUserByNick($client_id, $nick);
	    if (($app) && ($user) && ($user->enabled == 1)) {
	        if ($user->pswhash == hash('sha256', $psw, false)) {
    	        $data = new stdClass();
    	        // create csrr token
    	        createCsrToken($request, $data);
    	        // save client_id, nick, sighHash a sessionba
    	        $request->sessionSet('client_id', $client_id);
    	        $request->sessionSet('nick', $nick);
    	        $request->sessionSet('signHash', $user->signhash);
    	        
    	        // képernyő kirajzolás
    	        $data->client_id = $client_id;
    	        $data->appName = $app->name;
    	        $data->extraCss = $app->css;
    	        $data->nick = $nick;
    	        $data->title = 'CHANGE_PSW';
    	        $data->msgs = [];
    	        $data->psw1 = '';
    	        $data->psw2 = '';
    	        $view->registForm2($data);
	        } else {
	            // jelszó hiba
	            $user->errorcount++;
	            if ($user->errorcount >= $app->falseLoginLimit) {
	                $user->enabled = 0;
	                $user->blocktime = date('Y-m-d H:i:s');
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            $request->sessionSet('client_id', $client_id);
	            if ($user->enabled == 1) {
	                $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	            } else {
	                $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	            }
	        }
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	    
	}
	
	/**
	 * tárolt adataim
	 * sessionban érkezik a client_id
	 * @param object $request - nick, psw1, csrtoken
	 */
	public function mydata($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2');
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','?');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    if ($nick == '') {
	        $view->errorMsg(['ERROR_NICK_EMPTY']);
	        return;
	    }
	    if ($psw == '') {
	        $view->errorMsg(['ERROR_PSW_EMPTY']);
	        return;
	    }
	    
	    $app = $appModel->getData($client_id);
	    $user = $model->getUserByNick($client_id, $nick);
	    if (($app) && ($user)) {
	        if ($user->pswhash == hash('sha256', $psw, false)) {
	            echo JSON_encode($user,JSON_PRETTY_PRINT);
	        } else {
	            // jelszó hiba
	            $user->errorcount++;
	            if ($user->errorcount >= $app->falseLoginLimit) {
	                $user->enabled = 0;
	                $user->blocktime = date('Y-m-d H:i:s');
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            $request->sessionSet('client_id', $client_id);
	            if ($user->enabled == 1) {
	                $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	            } else {
	                $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	            }
	        }
	    } else {
	        $view->errorMsg(['ERROR_NOTFOUND']);
	    }
	}

	/**
	 * fiók törlése
	 * sessionban érkezik a client_id
	 * @param object $request - nick, psw1, csrtoken
	 */
	public function deleteaccount($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2');
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','?');
	    $nick = $request->input('nick','');
	    $psw = $request->input('psw1','');
	    if ($nick == '') {
	        $view->errorMsg(['ERROR_NICK_EMPTY']);
	        return;
	    }
	    if ($psw == '') {
	        $view->errorMsg(['ERROR_PSW_EMPTY']);
	        return;
	    }
	    
	    $app = $appModel->getData($client_id);
	    $user = $model->getUserByNick($client_id, $nick);
	    if (($app) && ($user)) {
	        if ($user->pswhash == hash('sha256', $psw, false)) {
	            $request->sessionSet('client_id', $client_id);
	            $model->deleteUser($user);
                $this->recallLoginForm($request, $view, $app, ['USER_DELETED'] );
	        } else {
	            // jelszó hiba
	            $user->errorcount++;
	            if ($user->errorcount >= $app->falseLoginLimit) {
	                $user->enabled = 0;
	                $user->blocktime = date('Y-m-d H:i:s');
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            $request->sessionSet('client_id', $client_id);
	            if ($user->enabled == 1) {
	                $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	            } else {
	                $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	            }
	        }
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
	    require('./vendor/fpdf/fpdf.php');

	    $pdf = new FPDF();
	    $pdf->AddPage();
	    $pdf->SetFont('Arial','B',16);
	    $pdf->Cell(40,10,'client_id='.$client_id);
	    $pdf->Output();
	}

	/**
	 * parse pdf file
	 * @param string $filePtah
	 * @param string $client_id
	 * @param object $res {error, ...}
	 */
	protected function parsePdf(string $filePath, string $client_id, &$res) {
	    $parser = new \Smalot\PdfParser\Parser();
	    $pdf    = $parser->parseFile($filePath);
	    $text = $pdf->getText();
	    if ($text != 'client_id='.$client_id) {
	        $res->error = 'ERROR_PDF_SIGN_ERROR'; // nem megfelelő a txt tartalom
	    }
	}

	/**
	 * check pdf signature, ha a pdfsig hivás sikertelen, de
	 * tartalmazza az aláírásra utaló stringeket akkor a teljes pdf tartalmonból
	 * sha256 has-t képez és beteszi a $res->pdfHash -be.
	 * @param string $filePath
	 * @param Res $res {error:"xxxxxx" | error:"", signHash:"" }
	 */
	protected function checkPdfSig(string $filePath, &$res) {
	    $check1 = false;
	    $check2 = false;
	    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
	    $signatureArray[] = '';
    	if ((strpos($signatureArray[0],'default Firefox Folder') > 0) ||
    	    ($signatureArray[0] == 'sh: pdfsig: command not found')) {
    	        // karakteres keresés a pdf tartalomban
    	        $buffer = '';
    	        $pdfContent = '';
    	        $handle = fopen($filePath, 'r');
    	        while (($buffer = fgets($handle)) !== false) {
    	            $pdfContent .= $buffer;
    	            if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
    	                $check1 = true;
    	            }
    	            if (strpos($buffer, 'NISZ Nemzeti Infokommun') !== false) {
    	                $check2 = true;
    	            }
    	        }
    	        if ($check1 && $check2) {
    	            $res->error = '';
    	            $res->pdfHash = hash('sha256', $pdfContent, false);
    	        } else {
    	            $res->error = 'ERROR_PDF_SIGN_ERROR';
    	        }
    	} else {
    	        if (in_array('File \'' . $filePath . '\' does not contain any signatures' , $signatureArray)) {
    	            $res->error = 'ERROR_PDF_SIGN_ERROR'; // nincs aláírva
    	        }
    	        if (!in_array('  - Signature Validation: Signature is Valid.' , $signatureArray)) {
    	            $res->error = 'ERROR_PDF_SIGN_ERROR'; // aláírás nem valid
    	        }
    	        if (!in_array('  - Signer Certificate Common Name: AVDH Bélyegző' , $signatureArray)) {
    	            $res->error = 'ERROR_PDF_SIGN_ERROR'; // nem AVDH aláírás
    	        }
    	}
    }

    /**
     * extract igazolas.xml a meghatamazo.pdf -ből
     * @param string $filePath
     * @param string $igazolasPWD
     * @param Res $res {error:"xxxxxx" | error:"", ........}
     */
    protected function extractIgazolasFromPdf(string $filePath, string $igazolasPWD, &$res) {
        shell_exec('pdfdetach -save 1 -o '.$igazolasPWD.'/igazolas.pdf '.escapeshellarg($filePath));
        unlink($filePath);
        if (!is_file($igazolasPWD.'/igazolas.pdf')) {
            // nem sikerült igazolas.pdf -et kibontani
            $res->error = 'ERROR_PDF_SIGN_ERROR';
        }
    }

    /**
     * extract meghatalmazo.xml az igazolas.pdf -ből
     * @param string $igazolasPWD
     * @param Res $res {error:"xxxxxx" | error:"", ........}
     */
    protected function extractMeghatalmazoFromIgazolas(string $igazolasPWD, &$res) {
        shell_exec('pdfdetach -save 1 -o '.$igazolasPWD.'/meghatalmazo.xml '.escapeshellarg($igazolasPWD.'/igazolas.pdf'));
        unlink($igazolasPWD.'/igazolas.pdf');
        if (!is_file($igazolasPWD.'/meghatalmazo.xml')) {
            $res->error =  'ERROR_PDF_SIGN_ERROR';
        }
    }

	/**
	 * feltöltött $tmpdir/signed.pdf feldolgozása
	 * @param string $tmpDir
	 * @param string $filename
	 * @param string $client_id
	 * @return object {error:"" | error:"xxxxxx", signHash:"xxxxx"}
	 */
	protected function processUploadedFile(string $tmpDir, string $fileName,  string $client_id) {
	    $res = new stdClass();
	    $res->error = '';
	    $res->signHash = '';
	    $igazolasPWD = $tmpDir;
	    $filePath = $tmpDir.'/'.$fileName;

	    // pdf tartalom ellenörzése Smalot parserrel
	    $this->parsePdf($filePath, $client_id, $res);

	    // aláirás ellenörzés pdfsig segitségével.
	    $this->checkPdfSig($filePath, $res);

	    if ($res->error == '') {
	        $this->extractIgazolasFromPdf($filePath, $igazolasPWD, $res);
        }

        if ($res->error == '') {
            $this->extractMeghatalmazoFromIgazolas($igazolasPWD, $res);
	    }

	    if (($res->error != '') && (isset($res->pdfHash))) {
	        $res->error = '';
	        $res->signHash = $res->pdfHash;
            return $res;
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
	 * könyvtár teljes tartalmának törlése
	 * @param string $tmpDir
	 */
	protected function clearFolder(string $tmpDir) {
    	if (is_dir($tmpDir)) {
    	    $files = glob($tmpDir.'/*'); // get all file names
    	    foreach ($files as $file) { // iterate files
    	        if (is_file($file)) {
    	            unlink($file); // delete file
    	        }
    	    }
    	}
	}

	/**
	 * munkakönyvtár létrehozása sessin ID -t használva
	 */
	protected function createWorkDir() {
    	$sessionId = session_id();
    	$tmpDir = 'work/tmp'.$sessionId;
    	if (!is_dir($tmpDir)) {
    	    mkdir($tmpDir, 0777);
    	}
    	return $tmpDir;
	}

	/**
	 * user egist második képernyő (aláirt pdf feltöltés feldolgozása, nick/psw1/psw2 form)
	 * sessionban érkezik client_id
	 * ha elfelejtett jelszó miatti regisztráció ismétlés vagy jelszó modosítás akkor 
	 * sessionban nick is érkezhet
     * app->css -t használja
	 * @param Request $request - signed_pdf, cssrtoken, nick
     * @return void
	 */
	public function registform2($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál szükség esetén táblát.
	    $view = getView('oauth2');
	    $client_id = $request->sessionGet('client_id','');
	    $nick = $request->input('nick','');
	    $request->sessionSet('nick',$nick);
	    $forgetPswNick = $request->sessionGet('nick','');
	    if ($forgetPswNick != '') {
	        $nick = $forgetPswNick;
	    }
	    $app = $appModel->getData($client_id);
	    if (!$app) {
	        $app = new stdClass();
	        $app->name = 'testApp';
	        $app->css = '';
	    }

	    // csrttoken ellnörzés
	    checkCsrToken($request);

	    // munkakönyvtár létrehozása a sessionId -t használva -> $tmpDir
        $tmpDir = $this->createWorkDir();

	    // biztos ami biztos...
	    $this->clearFolder($tmpDir);

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

	    if (($res->error == '') && ($forgetPswNick == '')) {
	        $res = $this->checkSignHashExist($client_id, $res->signHash);
	        if ($res->error != '') {
	            $view->errorMsg([$res->error, 'nick:'.$res->nick]);
	        }
	    }
	    
	    if (($res->error == '') && ($forgetPswNick != '')) {
	        // most azt kell megnézni azonos signHash keletkezett-e?
	        $user = $model->getUserByNick($client_id, $forgetPswNick);
	        if ($res->signHash != $user->signhash) {
	            $view->errorMsg([txt('ERROR_SIGNNOTEQUAL')]);
	        }
	    }
	    
	    // munkakönyvtár és tartalmának teljes törlése
	    $this->clearFolder($tmpDir);
	    rmdir($tmpDir);

	    if ($res->error == '') {
	        // echo ouput form
	        $data = new stdClass();
	        createCsrToken($request, $data);
	        $request->sessionSet('signHash', $res->signHash);
	        $request->sessionSet('client_id', $client_id);
	        $data->msgs = [];
	        $data->appName = $app->name;
	        $data->extraCss = $app->css;
	        $data->nick = $request->input('nick','');
	        if ($forgetPswNick != '') {
	            $data->nick = $forgetPswNick;
	        }
	        $data->psw1 = '';
	        $data->psw2 = '';
	        $data->title = 'FORGET_PSW';
	        $view->registForm2($data);
	    }
	}

	/**
	 * registForm2 visszahívbása hibaüzenetekkel
	 * @param object $request
	 * @param object $view
	 * @param object $data
	 * @param object $app
	 * @param string $forgetPswNick
	 * @param array $msgs
	 */
	protected function recallRegistForm2(&$request, &$view, &$data, $app, string $forgetPswNick, array $msgs) {
    	createCsrToken($request, $data);
    	if (isset($data->signHash)) {
    	    $request->sessionSet('signHash', $data->signHash);
    	}
    	$request->sessionSet('client_id', $app->client_id);
    	$data->appName = $app->name;
    	$data->extraCss = $app->css;
    	$data->msgs = $msgs;
    	$data->forgetPswNick = $forgetPswNick;
    	$data->psw1 = '';
    	$data->psw2 = '';
    	if ($forgetPswNick == '') {
    	    $data->title = 'LBL_REGISTFORM2';
    	} else {
    	    $data->nick = $forgetPswNick;
    	    $data->title = 'FORGET_PSW';
    	}
    	$view->registForm2($data);
	}
	
	/**
	 * user Regist 2.képernyő feldolgozás)
	 * sessionban érkezik client_id és forgetPsw, changePsw esetén nick
	 * @param Request $request - nick, psw1, psw2, csrToken
     * @return void
	 */
	public function doregist($request) {
	    $appModel = getModel('appregist');
	    $model = getModel('oauth2'); // szükség van rá, ez kreál táblát.
	    $view = getView('oauth2');

	    // csrttoken ellnörzés
	    checkCsrToken($request);

	    // client_id és signHash, forgetPswNick sessionból
	    $client_id = $request->sessionGet('client_id','');
	    $signHash = $request->sessionGet('signHash','');
	    $forgetPswNick = $request->sessionGet('nick','');
	    
	    $app = $appModel->getData($client_id);
	    
	    if ($signHash == '') {
	        echo '<p>invalid signHash</p>';
	        exit();
	    }

	    if ($forgetPswNick == '') {
	        $data = new stdClass();
	    } else {
	        $data = $model->getUserByNick($client_id, $forgetPswNick);
	    }
	    
	    // adat és cookie kezelés elfogadva?
	    if (($request->input('dataProcessAccept',0) != 1) ||
	        ($request->input('cookieProcessAccept',0) != 1)) {
	            $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick,
	                ['ERROR_DATA_ACCEP_REQUEST','ERROR_COOKIE_ACCEP_REQUEST']);
	            return;
	    }

	    $app = $appModel->getData($client_id);
	    
	    // kitöltés ellenörzések
	    $data->client_id = $client_id;
	    $data->signHash = $signHash;
	    $data->nick = $request->input('nick','');
	    $data->psw1 = $request->input('psw1','');
	    $data->psw2 = $request->input('psw2','');
	    if ($forgetPswNick != '') {
	       $data->forgetPswNick = $forgetPswNick;
	    }
	    if ($forgetPswNick != '') {
	        $data->nick = $forgetPswNick;
	    }
	    $request->sessionset('nick',$data->forgetPswNick);
	    $msgs = $model->check($data);
	    if (count($msgs) > 0) {
	        $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick, $msgs);
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
	       if ($data->forgetPswNick == '') {
	           unset($data->forgetPswNick);
	           $msgs = $model->addUser($data);
	       } else {
	           unset($data->forgetPswNick);
	           $msgs = $model->updateUser($data);
	       }
	       if (count($msgs) == 0) {
	           $view->successMsg(['USER_SAVED']);
	       } else {
	           $this->recallRegistForm2($request, $view, $data, $app, $forgetPswNick, $msgs);
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
	        foreach ($_POST as $fn => $fv) {
	            if ($fn != 'client_id') {
	                $extraParams[$fn] = $fv;
	            }
	        }
            $request->sessionSet('extraParams',$extraParams);

	        $data->appName = $app->name;
	        $data->client_id = $app->client_id;
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
	    $data->client_id = $app->client_id;
	    $data->msgs = $msgs;
	    $view->loginform($data);
	}

	/**
	 * callback url kialakitása
	 * @param App $app
	 * @param User $user
	 * @param Request $request
	 * @return string
	 */
	protected function getCallbackUrl($app, $user, $request): string {
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
    	        if (($fn != 'task') && ($fn != 'client_id') &&
    	            ($fn != 'css') && ($fn != 'path') && ($fn != 'option')) {
    	           $url .= $fn.'='.$fv;
    	        }
    	    }
    	}
	    return $url;
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
	        if ($user->enabled == 0) {
	            // letiltott user, login képernyő visszahívása
	            $request->sessionSet('client_id', $client_id);
	            $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	        } else if ($user->pswhash == hash('sha256', $psw, false)) {
    	        // sikeres login, code és accessToken generálás, callback visszahívás
    	        $user->code = md5(random_int(1000000, 5999999)).$user->id;
    	        $user->access_token = md5(random_int(6000000, 9999999)).$user->id;
    	        $user->codetime = date('Y-m-d H:i:s');
    	        $user->errorcount = 0;
    	        $user->blocktime = '';
    	        $model->updateUser($user);

    	        $url = $this->getCallbackUrl($app, $user, $request);
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
	                $user->blocktime = date('Y-m-d H:i:s');
	            }
	            $tryCount = $app->falseLoginLimit - $user->errorcount;
	            $model->updateUser($user);
	            $request->sessionSet('client_id', $client_id);
	            if ($user->enabled == 1) {
	               $this->recallLoginForm($request, $view, $app, ['INVALID_LOGIN', $tryCount] );
	            } else {
	               $this->recallLoginForm($request, $view, $app,['LOGIN_DISABLED', ''] );
	            }
	        }
	    } else {
	        // nick név hiba
	        $tryCount = $app->falseLoginLimit;
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

	    if (!headers_sent()) {
	        header('Content-Type: application/json');
	    }
	    if ($user == false) {
	        echo '{"error":"user not found code='.$code.'"}'; exit();
	    }

	    $app = $appModel->getData($client_id);

	    if ($app == false) {
	        echo '{"error":"app not found client_id='.$client_id.'"}'; exit();
	    }

	    $access_token = '';
	    if (($app) && ($user)) {
	        if (($app->client_secret == $client_secret) &&
	            ($user->enabled == 1) &&
	            ($user->client_id == $app->client_id)
	           ) {
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

        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        if ($rec) {
            echo '{"nick":"'.$rec->nick.'"}';
        } else {
            echo '{"error":"not found"}';
        }
	}

}
?>
