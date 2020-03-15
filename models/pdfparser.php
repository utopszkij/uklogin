<?php

class PdfData {
    public $error = '';
    // PDF tartalom
    public $txt_name = ''; 
    public $txt_mothersname = '';
    public $txt_birth_date = '';
    public $txt_address = '';
    public $txt_tartozkodas = '';
    // PDF info
    public $info_creator = '';
    public $info_producer = '';
    public $info_pdfVersion = '';
    // aláírás meghatalmazó
    public $xml_nev = '';
    public $xml_ukemail = '';
    public $xml_szuletesiNev = '';
    public $xml_anyjaNeve = '';
    public $xml_szuletesiDatum = '';
    public $xml_alairasKelte = '';
}

class PdfparserModel {

	/**
	* PDF text tartalmának kinyerése
	* @param string PDF file elérési utvonala
	* @param array $lines (IO)
	* @return bool
	*/	
	protected function pdf_getText(string $filePath, array &$lines): bool {
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

	/**
	* PDF információk kinyerése
	* @param string PDF file teljes utvonal
	* @param array $lines (IO)
	* return bool
	*/
	protected function pdf_getInfo(string $filePath, array &$lines): bool {
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

	/**
	* PDF aláírás infok kinyerése
	* @param string PDF file teljes elérési utvonal
	* @param array signatureArray
	* @return bool
	*/
	protected function pdf_getSignature(string $filePath, array &$signatureArray) {
	    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
	}

	/**
	* meghatalmazó információk kinyerése a PDF -ből
	* @param string PDF file teljes elérési út
	* @param array $lines (IO)
	* @return bool
	*/
	protected function pdf_getMeghatalmazo(string $filePath, array &$lines): bool {
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


	/**
	* pdf text tartalmának elemzése
	* @param string $filePath PDF file teljes utvpnal
	* @param PdfData $res 
	*/
   protected function parseTxt(string $filePath, PdfData &$res) {
    	    $res->txt_tartozkodas = ''; // ez nem mindig szerepel a pdf -ben
			$lines = array();
			if ($this->pdf_getText($filePath, $lines)) {
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
			$res->error .= 'Nem lehet értelmezni a PDF szöveggét, ';
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
		if ($this->pdf_getInfo($filePath, $lines)) {
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
			$res->error .= 'Nem lehet értelmezni a PDF file infokat, ';
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
            $res->error .= 'Nem megfelelő aláírás(1), ';
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
        $this->pdf_getSignature($filePath, $signatureArray);
        if (count($signatureArray) == 0) {
            $this->checkPdfSignStr($filePath, $res);
        } else if ((strpos($signatureArray[1],'Segmentation fault') >= 0) ||
                   ($signatureArray[0] == 'sh: pdfsig: command not found')) {
            $this->checkPdfSignStr($filePath, $res);
        } else {
            if (in_array('File \'' . $filePath . '\' does not contain any signatures' , $signatureArray)) {
               $res->error .= 'Nincs aláírva, ';
            }
            if (!in_array('  - Signature Validation: Signature is Valid.' , $signatureArray)) {
               $res->error .= 'Aláírás nem érvényes, ';
            }
            if (!in_array('  - Signer Certificate Common Name: AVDH Bélyegző' , $signatureArray)) {
               $res->error .= 'nem AVDH aláírás, ';
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
        if ($this->pdf_getMeghatalmazo($filePath, $lines)) {
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
			$res->error .= 'Nem lehetett az aláíró info XML-t kibontani, ';
		}
    }
    
    /**
    * könyvtár teljes tartalmának törlése
    * @param string $tmpDir
    */
    public function clearFolder(string $tmpDir) {
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
    public function createWorkDir(): string {
        $sessionId = session_id();
        $tmpDir = './work/tmp'.$sessionId;
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777);
        }
        return $tmpDir;
    }

	 /**
	 * PDF fájl elemzése
	 * @param string PDF file teljes utvonal
	 * @return PdfData 
	 */
	 public function parser(string $filePath): PdfData {
	  global $pdfParserResult;
	  if (isset($pdfParserResult)) {  // UNIT testhez
	      return $pdfParserResult;
	  }
      $res = new PdfData();
      if (!file_exists($filePath)) {
          $res->error = 'PDF_Nem_található. ('.$filePath.'), ';
      } else {
          $filePath = str_replace("'",'',escapeshellarg($filePath)); 
       	 $this->checkPdfSign($filePath, $res);
       	 if ($res->error == '') {
          	$this->parseTxt($filePath, $res);
		    	$this->parseInfo($filePath, $res);
		    	$this->parseMeghatalmazo($filePath, $res);
		    	// meghatalmazó és pdf tartalom azonos?
		      if ((mb_strtoupper($res->txt_mothersname) != mb_strtoupper($res->xml_anyjaNeve)) |
		          (mb_strtoupper($res->txt_name) != mb_strtoupper($res->xml_viseltNev)) |
		        	 ($res->txt_birth_date != $res->xml_szuletesiDatum)) {
		           $res->error .= 'PDF tartalom és aláírás ren egyezik, <br>'.
		           $res->txt_mothersname.' / '.$res->xml_anyjaNeve.'<br>'.
		           $res->txt_name.' / '.$res->xml_viseltNev.'<br />'.
		           $res->txt_birth_date.' / '.$res->xml_szuletesiDatum.'<br />';
				} 	
		    }	
      }
	  return  $res;
	 }

} // PdfparserModel

?>