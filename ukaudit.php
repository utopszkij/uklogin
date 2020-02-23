<?php
/**
* ukAudit szolgáltatás
*     személyi adatokat tartalmazó aláírt pdf elemzése, ellenörzése
*     ez a szolgáltatás semmilyen adatot tartósan nem tárol. A feldolgozás során rövid ideig (pár másodpercig)
*     a személyes adatokat tartalmazó pdf fájl és az abban lévő csatolmányokat munkakönyvtárba irja, de a
*     feldolgozás után törli őket.
* 
* A kliens program teendői auditáláshoz
*   session_start();
*   .....
*   $_SESSION['token'] = md5(rand(0,10000));
*   $userinfo = '{..lásd UserInfo class...}';
*   $post = [
*    'form' => 1,
*    'redirect_uri' => '.......',
*    'userinfo'   => $userInfo,
*    'token' => $_SESSION['token']
*   ];
*   $ch = curl_init('https://uklogin.tk/ukaudit.php');
*   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
*   curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
*   curl_exec($ch);
*   curl_close($ch);
*
* A "redirect_uri" müködése
*  session_start();
*  ....
* if (($_GET['token'] == $_SESSION['token']) & ($_GET['result'] == 'OK')) {
*    // user hitelesitett
*   ......
* } else {
*	 // hitelesités sikertelen
*  ......
* }
*/

class UserInfo {
   public $family_name = '';
	public $middle_name = '';
	public $given_name = '';
   public $postal_code = '';
	public $locality = '';
   public $street_address = '';
	public $birth_date = '';
	public $mothersname = '';
}

class PdfData {
    public $error = '';
    public $txt_name = '';
    public $txt_mothersname = '';
    public $txt_birth_date = '';
    public $txt_address = '';
    public $info_creator = '';
    public $info_producer = '';
    public $info_pdfVersion = '';
    public $xml_nev = '';
    public $xml_ukemail = '';
    public $xml_szuletesiNev = '';
    public $xml_anyjaNeve = '';
    public $xml_szuletesiDatum = '';
    public $xml_alairasKelte = '';
}

function pdf_getText(string $filePath, array &$lines): bool {
    $outPath = str_replace(".pdf",".txt", $filePath);
    shell_exec('pdftotext ' . $filePath." ".$outPath);
    if (file_exists($outPath)) {
        $lines = file($outPath);
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function pdf_getInfo(string $filePath, array &$lines): bool {
    $outPath = str_replace(".pdf",".inf", $filePath);
    shell_exec('pdfinfo ' . $filePath." > ".$outPath);
    if (file_exists($outPath)) {
        $lines = file($outPath);
        $result = true;
    } else {
        $result = false;
    }
    return $result;
}

function pdf_getMeghatalmazo(string $filePath, array &$lines): bool {
    $outPath = str_replace(".pdf","_igazolas.pdf",$filePath);
    shell_exec('pdfdetach -save 1 -o '.$outPath." ".escapeshellarg($filePath));
    if (is_file($outPath)) {
        $outPath2 = str_replace(".pdf","_meghatalmazo.xml", $filePath);
        shell_exec("pdfdetach -save 1 -o ".$outPath2." ".$outPath);
        if (is_file($outPath2)) {
            $lines = file($outPath2);
            $result = true;
        } else {
            $result = false;
        }
    } else {
        $result = false;
    }
    return $result;
}

function pdf_getSignature(string $filePath, array &$signatureArray) {
    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
}

function txt($s) {
	return $s;
}


class UkauditController {
    
    /**
     * parse pdf file
     * DEMO változatnál a pdf -et mindig jónak jelzi
     * @param string $filePtah
     * @return object $res {error:"",txt_..., info_...., xml_....}
     */
    protected function parsePdf(string $filePath) {
        $res = new PdfData();
        if (!file_exists($filePath)) {
            $res->error = txt('NOT_FOUND').'(1) '.$filePath.' ';
        } else {
          $filePath = str_replace("'",'',escapeshellarg($filePath)); 
          $this->parseTxt($filePath, $res);
       	  $this->checkPdfSign($filePath, $res);
		  $this->parseInfo($filePath, $res);
		  $this->parseMeghatalmazo($filePath, $res);
        }
		return  $res;
	}
	/**
	 * pdf text tartalmának elemzése
	 * @param string $filePath
	 * @param PdfData $res {...txt_name, txt_mothername, txt_birth_date, txt_address...}
	 */
    protected function parseTxt(string $filePath, PdfData &$res) {
    	   $res->txt_tartozkodas = ''; // ez nem mindig szerepel a pdf -ben
			$lines = array();
			if (pdf_getText($filePath, $lines)) {
			    $i = 0;
			    while ($i < count($lines)) {
			       $s = trim(str_replace("\n",'', str_replace("\r",'',$lines[$i])));
    			    if ($s == 'Név (névviselés szerint)') {
    			        $res->txt_name = mb_strtoupper(trim($lines[$i + 2]));
    			    }
    			    if ($s == 'Születési dátum') {
    			        $res->txt_birth_date = str_replace('.','-',trim($lines[$i + 2]));
    			        $res->txt_birth_date = substr($res->txt_birth_date,0,10);
    			    }
    			    if ($s == 'Anyja neve és utóneve') {
    			        $res->txt_mothersname = mb_strtoupper(trim($lines[$i + 2]));
    			    }
    			    if ($s == 'Lakóhely adatok') {
    			    	 $i++;
				       $s = trim(str_replace("\n",'', str_replace("\r",'',$lines[$i])));
       			    if ($s == 'Cím') {
	    			        $res->txt_address = mb_strtoupper(trim($lines[$i + 2]));
	    			    }
    			    }
    			    if ($s == 'Tartózkodási hely adatok') {
    			    	 $i++;
				       $s = trim(str_replace("\n",'', str_replace("\r",'',$lines[$i])));
       			    if ($s == 'Cím') {
	    			        $res->txt_tartozkodas = mb_strtoupper(trim($lines[$i + 2]));
	    			    }
    			    }
    			    
			       $i++;
			     }
		} else {
			$res->error .= txt("PDF_ERROR_TXT").' ';
		}
	}
	
	/**
	 * pdf információk kezelése ellenörzése
	 *   Creator:        Apache FOP Version 1.0
     *   Producer:       Apache FOP Version 1.0; modified using iText 5.0.2 (c) 1T3XT BVBA
     *   PDF version:    1.4
	 * @param string $filePath
	 * @param PdfData $res  { ...info_creator, info_producer, info_PDFversion....}
	 */
	protected function parseInfo(string $filePath, PdfData &$res) {
		$lines = array();	
		if (pdf_getInfo($filePath, $lines)) {
			$i = 0;
			while ($i < count($lines)) {
			    $s = str_replace("\r",'',$lines[$i]);
			    $s = trim(str_replace("\n",'',$s));
			    if (substr($s,0,8) == 'Creator:') {
			        $res->info_creator = trim(substr($s,8,100));
			    }
			    if (substr($s,0,9) == 'Producer:') {
			        $res->info_producer = trim(substr($s,9,100));
			    }
			    if (substr($s,0,12) == 'PDF version:') {
			        $res->info_pdfVersion = trim(substr($s,12,100));
			    }
			    $i++;
			}
		} else {
			$res->error .= txt("PDF_ERROR_INFO").' ';
		}
    }
    
    /**
     * pdf aláírás elenörzés karakteres kereséssel
     * @param string $filePath pdf file 
     * @param PdfData $res
     */
    protected function checkPdfSignStr(string $filePath, Pdfdata &$res) {
        // karakteres keresés a pdf tartalomban
        $check1 = false;
        $check2 = false;
        $buffer = '';
        $handle = fopen($filePath, 'r');
        while (($buffer = fgets($handle)) !== false) {
            if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
                $check1 = true;
            }
            if (strpos($buffer, 'NISZ Nemzeti Infokommun') !== false) {
                $check2 = true;
            }
        }
        if ($check1 && $check2) {
            $res->error = '';
        } else {
            $res->error .= txt('ERROR_PDF_SIGN_ERROR').'(1) ';
        }
    }
    
    /**
     * check pdf signature, ha a pdfsig hivás sikertelen, de
     * tartalmazza az aláírásra utaló stringeket akkor a teljes pdf tartalmonból
     * sha256 has-t képez és beteszi a $res->pdfHash -be.
     * @param string $filePath
     * @param PdfData $res {error:"xxxxxx" | error:"", signHash:"" }
     */
    protected function checkPdfSign(string $filePath, PdfData &$res) {
        $signatureArray = [];
        pdf_getSignature($filePath, $signatureArray);
        if (count($signatureArray) == 0) {
            $this->checkPdfSignStr($filePath, $res);
        } else if ((strpos($signatureArray[1],'Segmentation fault') >= 0) ||
                   ($signatureArray[0] == 'sh: pdfsig: command not found')) {
            $this->checkPdfSignStr($filePath, $res);
        } else {
            if (in_array('File \'' . $filePath . '\' does not contain any signatures' , $signatureArray)) {
               $res->error .= txt('ERROR_PDF_SIGN_ERROR').'(2) '; // nincs aláírva
            }
            if (!in_array('  - Signature Validation: Signature is Valid.' , $signatureArray)) {
               $res->error .= txt('ERROR_PDF_SIGN_ERROR').'(3) '; // aláírás nem valid
            }
            if (!in_array('  - Signer Certificate Common Name: AVDH Bélyegző' , $signatureArray)) {
               $res->error .= txt('ERROR_PDF_SIGN_ERROR').'(4) '; // nem AVDH aláírás
            }
        }
    }
    
    /**
     * meghatalmazo.xml kinyerése és elemzése
     * @param string pdf file name
     * @param PdfData $res {.....xml_name....}
     */
    protected function parseMeghatalmazo(string $filePath, PdfData &$res) {
        $res->xml_viseltNev = '';
        $res->xml_ukemail = '';
        $res->xml_szuletesiNev = '';
        $res->xml_anyjaNeve = '';
        $res->xml_szuletesiDatum = '';
        $res->xml_alairasKelte = '';
        $lines = array();
        if (pdf_getMeghatalmazo($filePath, $lines)) {
        	$i = 0;
        	while ($i < count($lines)) {
                   $s = trim($lines[$i]);
                   if ((strpos($s,'fogalmak/Nev') > 0)  & (isset($lines[$i+1]))) {
                       $s = trim($lines[$i+1]);
                       $w = explode('>',$s);
                       if (count($w) > 1) {
                            $w2 = explode('<',$w[1]);
                            $res->xml_name = $w2[0];
                       }
                   }
                   if (strpos($s,'emailAddress') > 0) {
                       $emails = [];
                       preg_match('/emailAddress\"\>.*\</', $s , $emails);
                       if (count($emails) > 0) {
                           $w = explode('>',$emails[0]);
                           $w2 = explode('<',$w[1]);
                           $res->xml_ukemail = $w2[0];
                       }
                   }
                   // Új aláíró rendszerhez:
                   if ((strpos($s,'viseltNev') > 0)  & (isset($lines[$i+1]))) {
                       $s = trim($lines[$i+1]);
                       $w = explode('>',$s);
                       if (count($w) > 1) {
                            $w2 = explode('<',$w[1]);
                            $res->xml_viseltNev = $w2[0];
                       }
                   }
                   if ((strpos($s,'szuletesiNev') > 0)  & (isset($lines[$i+1]))) {
                       $s = trim($lines[$i+1]);
                       $w = explode('>',$s);
                       if (count($w) > 1) {
                            $w2 = explode('<',$w[1]);
                            $res->xml_szuletesiNev = mb_strtoupper($w2[0]);
                       }
                   }
                   if ((strpos($s,'anyjaNeve') > 0)  & (isset($lines[$i+1]))) {
                       $s = trim($lines[$i+1]);
                       $w = explode('>',$s);
                       if (count($w) > 1) {
                            $w2 = explode('<',$w[1]);
                            $res->xml_anyjaNeve = mb_strtoupper($w2[0]);
                       }
                   }
                   if ((strpos($s,'szuletesiDatum') > 0)  & (isset($lines[$i+1]))) {
                       $s = trim($lines[$i+1]);
                       $w = explode('>',$s);
                       if (count($w) > 1) {
                            $w2 = explode('<',$w[1]);
                            $res->xml_szuletesiDatum = substr(str_replace('.','-',$w2[0]),0,10);
                       }
                   }
                   if (strpos($s,'IssueInstant="') > 0) {
                       $s = trim($lines[$i]);
                       $w = explode('IssueInstant="',$s);
                       $res->xml_alairasKelte = substr($w[1],0,10);
                   }
                   $i++;
        	}
		} else {
			$res->error .= txt("PDF_ERROR_XML").' ';
		}
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
            rmdir($tmpDir);
        }
    }
    
    /**
     * munkakönyvtár létrehozása session ID -t használva
     */
    protected function createWorkDir(): string {
        $sessionId = session_id();
        $tmpDir = __DIR__.'/work/tmp'.$sessionId;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777);
        }
        return $tmpDir;
    }
    
   /**
    * pdfdata teljes ellenörzése
    * @param Pdfdata $res
    * @param UserRecord $user
    */
    protected function checkPdfData(Pdfdata &$res, $user) {
        if (($res->info_creator != 'Apache FOP Version 1.0') |
            ($res->info_producer != 'Apache FOP Version 1.0; modified using iText 5.0.2 (c) 1T3XT BVBA') |
            ($res->info_pdfVersion != '1.4')) {
                $res->error .= txt('PDF_INFO_CHECK_ERROR').' ';
        }
        
        $userName = mb_strtoupper($user->family_name.' '.$user->middle_name.' '.$user->given_name);
        $userName = trim(str_replace('  ',' ',$userName));
        $userAddress = mb_strtoupper($user->postal_code.' '.$user->locality.' '.$user->street_address);
        
        // lakcím nyilvántartásban a cím kicsit más formában van mint a lakcím kártyán
        $userAddress = str_replace(' EM.',' EMELET', $userAddress);
        $userAddress = str_replace('.','', $userAddress);
        $userAddress = str_replace('/','', $userAddress);
        $userAddress = str_replace('  ',' ', $userAddress);
        $userAddress = str_replace('  ',' ', $userAddress);
        $userAddress = trim($userAddress);
        $res->txt_address = str_replace('.','', $res->txt_address);
        $res->txt_address = str_replace('/','', $res->txt_address);
        $res->txt_address = str_replace('  ',' ', $res->txt_address);
        $res->txt_address = trim($res->txt_address);
        $res->txt_address = mb_substr($res->txt_address, 0, mb_strlen($userAddress));
        
        // ha a dátumot másképpen irta...
        $user->birth_date = str_replace('.','-',$user->birth_date);
        $user->birth_date = substr($user->birth_date,0,10);
        
        if (($res->txt_mothersname != mb_strtoupper($user->mothersname)) & ($user->mothersname != '')) {
                 $res->error .= txt('PDF_TXT_CHECK_ERROR').' Anyja neve nem egyezik ';
        }
        if (mb_strtoupper($res->txt_name) != mb_strtoupper($userName)) {
                 $res->error .= txt('PDF_TXT_CHECK_ERROR').' Név adat nem egyezik ';
        }
        if (($res->txt_birth_date != $user->birth_date) & ($user->birth_date != '')) {
                $res->error .= txt('PDF_TXT_CHECK_ERROR').' születési dátum nem egyezik ';
        }
        if (($res->txt_address != $userAddress) & ($userAddress != '')) {
                $res->error .= txt('PDF_TXT_CHECK_ERROR').' lakcím nem egyezik ';
        }
        if ($res->xml_viseltNev != $userName) {
            $res->error .= txt('PDF_XML_CHECK_ERROR').' Név nem egyezik ';
        }
        if (($res->xml_szuletesiDatum != $user->birth_date) & ($user->birth_date != '')) {
            $res->error .= txt('PDF_XML_CHECK_ERROR').' Születési dátum nem egyezik ';
        }
        if (($res->xml_anyjaNeve != mb_strtoupper($user->mothersname)) & ($user->mothersname != '')) {
                 $res->error .= txt('PDF_XML_CHECK_ERROR').' Anyja neve nem egyezik ';
        }
        $user->signdate = $res->xml_alairasKelte;  // Új mező
        $user->origname = $res->xml_szuletesiNev;  // Újmező    
                
    }
    
    /**
    * auditáláshoz form megjelenítés
    * $_POST -ban: redirect_uri, userinfo, token
    */
    public function form() {
    	$_SESSION['token'] = $_POST['token'];
    	$_SESSION['redirect_uri'] = urldecode($_POST['redirect_uri']);
    	$_SESSION['userinfo'] = urldecode($_POST['userinfo']);
		echo '
		<html>
		  <head>
		    <meta charset="utf-8">
		    <meta name="title" content="Ügyfélkapus login rendszer">
		    <meta name="description" content="web szolgáltatás e-demokrácia programok számára. Regisztráció ügyfélkapus aláírás segitségével.">
		    <meta name="viewport" content="width=device-width, initial-scale=1.0">
		    <title>uklogin</title>
			 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
		  </head>
		';
		echo '
		<body style="background-color:#d0d0d0; width:100%; height:100%">
        <div id="ukAudit" class="page" style="padding:20px; margin:20px; background-color:white; opacity:0.9">
        <h2>Hitelesítés az ügyfélkapu és a kormányzati aláírás szolgáltatás segitségével
				<img src="templates/default/cimer.png" style="height:150px; float:right">        
        </h2>
        <p>1. A lentebb megadott linkre kattintva (új böngésző fülön nyílik meg), az ügyfélkapus
         belépésedet használva; le kell töltened a személyi
         adataidat tartalmazó pdf fájlt a saját gépedre. Miután az ügyfélkapus belépéseddel azonosítottad magad, 
         görgesd le a megjelenő oldalt az aljára, a jobb alsó sarokban van a "LETÖLTÉS" gomb.
         A letöltés után térjél vissza erre a böngésző fülre!</p> 
        <p style="padding-left:30px;"><a href="https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml"
        	 target="_new">személyi adatokat tartalmazó pdf letöltése<br />
        	 https://www.nyilvantarto.hu/ugyseged/NyilvantartottSzemelyesAdatokLekerdezeseMegjelenitoPage.xhtml
           </a>
        </p> 
        <p>2. Ezután a pdf fájl elektronikussan  alá kell írnod. Ennek érdekében kattints
         a lentebb megadott linkre (új böngésző fülön nyílik meg), 
         válaszd ki az elöző lépésben letöltött a pdf fájlt, válaszd a "hitelsített pdf" opciót,
         fogadd el a felhasználási feltételeket, ha a program kéri akkor azonosítsd
         magad az ügyfélkapus belépéseddel, kattints a "Documentum elküldése" ikonra!
         Ezután a megjelenő új képernyöröl töltsd le az aláirt pdf -t a saját gépedre.
         Az aláírt fájl letöltése után térjél vissza erre a böngésző fülre!</p> 
        <p style="padding-left:30px;"><a href="https://szuf.magyarorszag.hu/szuf_avdh_feltoltes" 
        	target="_new">
        	pdf aláírása<br />
        	https://szuf.magyarorszag.hu/szuf_avdh_feltoltes
           </a>
        </p> 
        <p>3. Töltsd fel a fentiek szerint letöltött és aláírt pdf fájlt! (válaszd ki, majd kattints a kék szinű gombra!)</p>
        <form name="formRegist1" id="formRegist1"	action="ukaudit.php" class="form"
            method="post"target="_self" enctype="multipart/form-data">
				<input type="hidden" name="pdffile" value="alairt_pdf" />
				<input type="hidden" name="process" value="1" />
				<br />
				<div class="form-control-group">
				<label>aláírt pdf file:</label>
				<input type="file" class="form-control" name="alairt_pdf" />
				</div>
				<br />
				<br />
				<div style="display:none">
				</div>
				<div>
				<h3>Adatkezelési leírás</h3>
				<strong>A hitelesítést végző uklogin.tk szerver semmilyen rád vonatkozó személyes adatot tartósan nem tárol.</strong>
				A feltöltött pdf file tartalmazza a te személyes adataidat (név, lakcím, születési dátum, anyja neve,
				személyi okmányok azonosító számai, személyi szám) ezeket az adatokat - technikai okokból - a feldolgozás
				során néhány másodpercre egy ideiglenes könyvtárba tárolja a program, de a szükséges ellenörzések elvégzése 
				után onnan azonnal törli is őket. Maga a feltöltött pdf fájl is törlődik.
				Ez a szolgáltatás egy darab munkamenet cooke-t tárol a gépeden, ez semmilyen személyes adatot nem tartalmaz.				
				</div>
				<div class="buttons">
				<button type="submit" class="btn btn-primary">
					<em class="fa fa-upload"></em>
					A fent leirt adatkezeléshez hozzájárulok, az aláírt pdf -et feltöltöm
				</button>
				<br />
				<br />
				</div>
			</form>
        </div>
   	</body>
   	';
   	echo '
   	</html>
   	'; 
    }
    
    /**
    * aláírt pdf és userinfo ellenörzése
    * $_POST: redirect_uri, userinfo, alairt_pdf
    * result: redirect_uri?result=OK|errormsg
    */
    public function audit() {
		// tempdir kreálása
		$tmpDir = $this->createWorkDir();
		
		// uploaded file másolása temdir -be
		$filePath = $tmpDir.'/alairt.pdf';
		if (!move_uploaded_file($_FILES['alairt_pdf']['tmp_name'], $filePath)) {
			echo 'upload error'; exit();
		}
		
		// paraméterek a $_SESSION -ból
		$userInfo = new UserInfo();
		$w = JSON_decode($_SESSION['userinfo']);
		foreach ($userInfo as $fn => $fv) {
			if (isset($w->$fn)) {
				$userInfo->$fn = $w->$fn;	
			}
		}
		$redirect_uri = $_SESSION['redirect_uri'];
		$token = $_SESSION['token'];
		
		// pdf file teljes ellemzése
		$res = $this->parsePdf($filePath); 
		$this->checkPdfdata($res, $userInfo);
		
		// feltöltött pdf file és a kibontott csatolt fileok törlése
		$this->clearFolder($tmpDir);
		
		// eredmény kiirása
		if ($res->error == '') {
			if ($redirect_uri != '') {
				header('Location: '.$redirect_uri.'?result=OK&token='.$token);
			} else {
				echo 'OK token='.$token;
			}	
		} else {
			if ($redirect_uri != '') {
				header('Location: '.$redirect_uri.'?result='.urlencode(JSON_encode($res)).'&token='.$token);
			} else {
				echo 'result='.JSON_encode($res).' token='.$token;
			}	
		}
		
		// adat törlés a memoriából
		$res = md5(time());         
		$userInfo = md5(time());
    }
    
} // UkauditController

// main
session_start();
$controller = new UkauditController();
if (isset($_GET['testform'])) {
	$userInfo = new UserInfo();
   $userInfo->family_name = '?';
	$userInfo->middle_name = '?';
	$userInfo->given_name = '?';
   $userInfo->postal_code = '?';
	$userInfo->locality = '?';
   $userInfo->street_address = '?';
	$userInfo->birth_date = '?';
	$userInfo->mothersname = '?';
	
	$_POST['userinfo'] = JSON_encode($userInfo);
	$_POST['token'] = '123';
	$_POST['redirect_uri'] = '';
	$_POST['form'] = '1';
	$controller->form();
} else if (isset($_POST['form']))  {
	$controller->form();
} else {
	$controller->audit();
	session_destroy();
}

?>