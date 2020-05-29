<?php

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

class UkauditController extends Controller {
	
	/**
	* pdf tartalom összevetése a UserInfo -val
	* @param Pdfdata (IO)
	* @param UserInfo
	* @return void
	*/
	protected function checkPdfdata(PdfData $res, UserInfo $user) {
        if (($res->info_creator != 'Apache FOP Version 1.0') |
            ($res->info_producer != 'Apache FOP Version 1.0; modified using iText 5.0.2 (c) 1T3XT BVBA') |
            ($res->info_pdfVersion != '1.4')) {
                $res->error .= 'PDF_INFO_CHECK_ERROR, ';
        }
		  if ($res->error != '') {
				return;		  
		  }        
        $userName = mb_strtoupper($user->family_name.' '.$user->middle_name.' '.$user->given_name);
        $userName = trim(str_replace('  ',' ',$userName));
        
        // lakcím nyilvántartásban a cím kicsit más formában van mint a lakcím kártyán
        $userAddress = mb_strtoupper($user->postal_code.' '.$user->locality.' '.$user->street_address);
        $userAddress = str_replace(' EM.',' EMELET', $userAddress);
        $userAddress = str_replace(' U. ',' UTCA ', $userAddress);
        $userAddress = str_replace(' U ',' UTCA ', $userAddress);
        $userAddress = str_replace(' Ú. ',' ÚT ', $userAddress);
        $userAddress = str_replace(' Ú ',' ÚT ', $userAddress);
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

        // aláírás infó vs PDF tartalom
        if (($res->txt_mothersname != $res->xml_anyjaNeve) |
            (mb_strtoupper($res->txt_name) != mb_strtoupper($res->xml_viseltNev)) |
        	   ($res->txt_birth_date != $res->xml_szuletesiDatum)) {
           $res->error .= 'PDF tartalom és aláírás ren egyezik, <br>'.
           $res->txt_mothersname.' / '.$res->xml_anyjaNeve.'<br>'.
           $res->txt_name.' / '.$res->xml_viseltNev.'<br />'.
           $res->txt_birth_date.' / '.$res->xml_szuletesiDatum.'<br />';
           return;
		  } 	
        
		  // PDF tartalon vs userInfo	
        if (($res->txt_mothersname != mb_strtoupper($user->mothersname)) & ($user->mothersname != '')) {
                 $res->error .= 'PDF beli anyja neve nem egyezik, ';
        }
        if ((mb_strtoupper($res->txt_name) != mb_strtoupper($userName)) & ($userName != '')) {
                 $res->error .= 'PDF beli név nem egyezik, ';
        }
        if (($res->txt_birth_date != $user->birth_date) & ($user->birth_date != '')) {
                $res->error .= 'PDF beli születési dátum nem egyezik, ';
        }
        if (($res->txt_address != $userAddress) & ($userAddress != '')) {
                $res->error .= 'PDF beli Lakcím nem egyezik, ';
        }
        
        // aláírás tartalom vs userInfo
        if (($res->xml_viseltNev != $userName) & ($userName != '')) {
            $res->error .= 'Aláírás szerinti viselt név nem egyezik, ';
        }
        if (($res->xml_szuletesiDatum != $user->birth_date) & ($user->birth_date != '')) {
            $res->error .= 'Aláírás szerinti születési dátum nem egyezik, ';
        }
        if (($res->xml_anyjaNeve != mb_strtoupper($user->mothersname)) & ($user->mothersname != '')) {
                 $res->error .= 'Aláírás szerinti anyja neve nem egyezik, ';
        }
	}	
	
	/**
	* PDF file és userinfok összevetése form megjelenités
	* paraméterek sessionba tárolása
	* @param Request  - redirect_uri, token, userinfo
	* @return void
	*/ 
	public function form(Request $request) {
    	$request->sessionSet('token',$request->input('token',''));
    	$request->sessionSet('redirect_uri',urldecode($request->input('redirect_uri','')));
    	$request->sessionSet('userinfo',urldecode($request->input('userinfo','')));
    	$p = new stdClass();
    	$p->okURL = config('MYDOMAIN').'/opt/ukaudit/audit"}';
    	$p->formTitle = 'Hitelesítés az ügyfélkapu és a kormányzati aláírás szolgáltatás segítségével';
    	$p->loggedUser = $request->sessionGet('loggedUser');
    	$this->createCsrToken($request, $p);
		$view = $this->getView('pdfform');
		$view->pdfForm($p);
   }

	/**
	* PDF file és userinfok összevetése (pdfForm feldolgozása)
	* ez ellenörzés után visszahivja a redirect_uri -t params: token, result
	*    result = 'OK' vagy hibaüzenet
	* @param Request  - uploaded PDF file (alairt_pdf)
	* @session token, redirect_uri, userInfo
	* @return void
	*/ 
	public function audit(RequestObject $request) {
	    $this->checkCsrToken($request);
		$model = $this->getModel('pdfparser');
		// tempdir kreálása
		$tmpDir = $model->createWorkDir();
		
		// uploaded file másolása temdir -be
		$fileName = getUploadedFile('alairt_pdf', $tmpDir);
		if ($fileName == '') {
		    echo 'upload error'; exit();
		}
		$filePath = $tmpDir.'/'.$fileName;
		
		// paraméterek a SESSION -ból
		$userInfo = new UserInfo();
		$w = JSON_decode($request->sessionGet('userinfo',''));
		foreach ($userInfo as $fn => $fv) {
			if (isset($w->$fn)) {
				$userInfo->$fn = $w->$fn;	
			}
		}
		$redirect_uri = $request->sessionGet('redirect_uri','');
		$token = $request->sessionGet('token','');
		
		// pdf file teljes ellemzése és összevetése a UserInfo -val
		$res = $model->parser($filePath); 
		if ($res->error == '') {
			$this->checkPdfdata($res, $userInfo);
		}
		
		// feltöltött pdf file és a kibontott csatolt fileok törlése
		$model->clearFolder($tmpDir);
		
		// eredmény kiirása
		
		if ($res->error == '') {
			if ($redirect_uri != '') {
				header('Location: '.$redirect_uri.'?result=OK&token='.$token);
			} else {
				echo 'OK token='.$token;
			}	
		} else {
			if ($redirect_uri != '') {
				header('Location: '.$redirect_uri.'?result='.urlencode($res->error).'&token='.$token);
			} else {
				echo 'result='.$res->error.' token='.$token;
			}	
		}
		
		// adat törlés a memoriából
		unset($res);         
		unset($userInfo);
		$request->sessionsSet('userinfo','');
	}
}
?>