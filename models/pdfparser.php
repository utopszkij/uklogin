<?php
/**
 * OpenId szolgáltatás magyarorszag.hu ügyfélkapu használatával
 * @package uklogin
 * @author Fogler Tibor
 */

include_once 'vendor/autoload.php';

/** PdfData rekord */
class PdfData {
    /** hibaüzenet */
    public $error = '';
    /** PDF tartalomban a név */
    public $txt_name = ''; 
    /** PDF tartalomban az anyja neve */
    public $txt_mothersname = '';
    /** PDF tartalomban a születési dátum */
    public $txt_birth_date = '';
    /** PDF tartalomban az állandó lakcím */
    public $txt_address = '';
    /** PDF tartalomban a tartozkodási hely */
    public $txt_tartozkodas = '';
    // PDF info
    /** PDF információ létrehozó sw */
    public $info_creator = '';
    /** PDF információ létrehozó sw2 */
    public $info_producer = '';
    /** PDF információ verzió */
    public $info_pdfVersion = '';
    // aláírás meghatalmazó
    /** aláírásban a név */
    public $xml_nev = '';
    /** aláírásban az email */
    public $xml_ukemail = '';
    /** aláírásban a születési név */
    public $xml_szuletesiNev = '';
    /** aláírásban az anyja neve */
    public $xml_anyjaNeve = '';
    /** aláírásban a születési dátum */
    public $xml_szuletesiDatum = '';
    /** aláírás kelte */
    public $xml_alairasKelte = '';
}

/** PDF parser model */
class PdfparserModel {

	/**
	* PDF text tartalmának kinyerése
	* @param string $filePath PDF file elérési utvonala
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
	* @param string $filePath PDF file teljes utvonal
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
	* @param string $filePath PDF file teljes elérési utvonal
	* @param array signatureArray
	* @return bool
	*/
	protected function pdf_getSignature(string $filePath, array &$signatureArray) {
	    $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));
	}

	/**
	* meghatalmazó információk kinyerése a PDF -ből
	* @param string $filePath PDF file teljes elérési út
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
    * @param string $filePath pdf file name
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
    
    protected function getDataFromTxt($text, $prefix, $postfix) {
        $i = strpos($text,$prefix);
        $j = strpos($text,$postfix);
        if (($i > 0) & ($j > $i)) {
            $result = substr($text, $i+strlen($prefix), $j-$i-strlen($prefix) );
        }
        return $result;    
    }
    
    
    protected function isStringInFile($file,$string = 'adbe.pkcs7.detached'){
        $handle = fopen($file, 'r');
        $valid = false; // init as false
        while (($buffer = fgets($handle)) !== false) {
            if (strpos($buffer, $string) !== false) {
                $valid = TRUE;
                break; // Once you find the string, you should break out the loop.
            }
        }
        fclose($handle);
        return $valid;
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
	 * @param string $filePath PDF file teljes utvonal
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
          
         //+ ha a parancssori pdf eszközök nem elérhetőek
         /*
          if (!$this->isStringInFile($filePath)) {
            $res->error="nincs aláírva";    
          }
          $parser = new \Smalot\PdfParser\Parser();
          $pdf    = $parser->parseFile($filePath);
          $text = $pdf->getText();
          $details = $pdf->getDetails();
          unlink($filePath);
          
          $res->txt_name = $this->getDataFromTxt($text,'Név (névviselés szerint)','Személyi azonosító');
          $res->txt_birth_date = $this->getDataFromTxt($text,'Születési dátum','Neme');
          $res->txt_mothersname = $this->getDataFromTxt($text,'Anyja neve és utóneve','Állampolgársága');
          $res->txt_address = $this->getDataFromTxt($text,'Cím','Bejelentés');
          $res->txt_tartozkodas = '';

          $res->xml_viseltNev = $res->txt_name;
          $res->xml_ukemail = '';
          $res->xml_szuletesiNev = $this->getDataFromTxt($text,'Születési név és utónév','Anyja neve és utóneve');
          $res->xml_anyjaNeve = $res->txt_mothersname;
          $res->xml_szuletesiDatum = $res->txt_birth_date;
          $res->xml_alairasKelte = '';
         */ 
         //- ha a parancssori eszközök nem elérhetőek 
                   
         //+ ha a parancssori pdf eszközök elérhetőek 
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
		 //- ha a parancssori eszközök elérhetőek 
		 
      }
	  return  $res;
	 }

} // PdfparserModel

?>