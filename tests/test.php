<?php
	/*
   if (isset($_GET['cmd'])) {
   	$lines = shell_exec(urldecode($_GET['cmd']).' 2>&1');
   	$lines = str_replace("\n", '<br />', $lines);
   	echo '<div style="background-color:silver">'.$lines.'</div>';
   }
	*/
	
    include '../vendor/autoload.php';


function String2Hex($string){
    $hex='';
    for ($i=0; $i < strlen($string); $i++){
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}

   echo phpinfo();	
	
   $filePath = './avdhA3-18840f38-7adf-4f8a-a8b2-c3e307d63b48.pdf';		    
   $filePath = './signed.pdf';		    

   // Egyes szervereken nem megy a pdfsig hivás :(
   echo '<html><body><h1>pdf aláírás ellenörzés tesztelése</h1>';
   
   $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));

   echo '<p>pdfsig hívás eredmnye: '.JSON_encode($signatureArray).'<p>'; 
   
   $signatureArray = explode(PHP_EOL, shell_exec('/home/utopszkij/bin/mutool sign ' . escapeshellarg($filePath).' 2>&1'));
   
   echo '<p>mutol hívás eredmnye: '.JSON_encode($signatureArray).'<p>'; 
   
   $res = new stdClass();
   $handle = fopen($filePath, 'r');
   $res->error = 'ERROR_PDF_SIGN_ERROR'; // init as false
   while (($buffer = fgets($handle)) !== false) {
	       if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
	                $res->error = '';
	                break; 
	       }
	       // ugyanigy lehetne keresni a 'NISZ Nemzeti Infokmmun' stringre is.
   }
   echo "<p>Egyszerű 'adbe.pkcs7.detached' tesztelés eredménye:".JSON_encode($res)."</p>";

   if (is_file('igazolas.pdf')) {
       unlink('igazolas.pdf');
   }
   echo shell_exec('pdfdetach -save 1 -o igazolas.pdf '.escapeshellarg($filePath).' 2>&1').'<br />';
   if (is_file('igazolas.pdf')) {
       echo '<p>sikerült az igazolas.pdf kibontása</p>';
   } else {
       echo '<p>Nem sikerült az igazolas.pdf kibontása</p>';
   }
   if (is_file('igazolas.pdf')) {
       unlink('igazolas.pdf');
   }
   
     
   echo '<h2>Smalot PDF parser</h2>';
   $parser = new \Smalot\PdfParser\Parser();
   $pdf    = $parser->parseFile($filePath);
   //$pdf    = $parser->parseFile('doc.pdf');
   $objects = $pdf->getObjects();
   $text = $pdf->getText();
   $signed = false;   
   echo '[text '.JSON_encode($text).' text]<br />';
   echo '<h3>objects<h3>';
   foreach ($objects as $on => $object) {
       if ($on == '21_0') {
        echo '<h4>object '.$on.'</h4>';
        $elements = $object->getHeader()->getElements();
        foreach ($elements as $en => $element) {
            echo 'header element '.$en.' ';
            if ($en == 'DSS') {
                echo '<br />';
            } else if ($en == 'Names') {
                echo '<br />';
            } else if ($en == 'AcroForm') {
                echo '<br />';
            } else if ($en == 'ESIC') {
                echo '<br />';
            } else if ($en == 'Resources') {
                echo '<br />';
            } else if ($en == 'DR') {
                echo '<br />';
            } else if ($en == 'AP') {
                echo '<br />';
            } else if ($en == 'EF') {
                echo '<br />';
            } else if ($en == 'Font') {
                echo '<br />';
            } else if ($en == 'XObject') {
                echo '<br />';
            } else if ($en == 'Contents') {
                echo ' '.$element->__toString().'<br />';
// lehet, hogy ez a string alkalmas az egyedi aláírás ellenörzésre.
// ez akkor igaz, ha ez a string nem változik ha többször kérek aláírást ugyanarra az inputra.
// tapasztalat: nem egyformák, az első 24 byte-ban van eltérés, utána egyforma
// viszont úgy tünik a file tartalomtól ez független (ez a NISZ szervezet publikus kulcsa?)                
				$fp = fopen($filePath.'sign.txt','w+');
				fwrite($fp,String2Hex($element->__toString()));
				fclose($fp);                 
                
            } else if ($en == 'SubFilter') {
                echo JSON_encode($element->getContent()).'<br />';
                if ($element->getContent() == 'adbe.pkcs7.detached') {
						$signed = true;                
                }
            } else {
                echo JSON_encode($element->getContent()).'<br />';
            }
        }
        echo 'Content:'.JSON_encode($object->getContent()).'<br /><br />';
      }  
   }
	echo '<p>signed='.$signed.'</p>';   
   
	/*
   echo '<form method="GET" action="./test.php">
   <p>command:<input type="text" name="cmd" size="120" />
	<button type="submit">start</button>  
   </p>
   */

	echo '<h2>Technik pdf parser</h2>';   
/**
 * index.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     PdfParser
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2011-2016 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-color
 *
 * This file is part of tc-lib-pdf-parser software library.
 */
// autoloader when using Composer
require ('../vendor/autoload.php');
// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/Parser/autoload.php');
$filename = './signed.pdf';
$rawdata = file_get_contents($filename);
if ($rawdata === false) {
    die('Unable to get the content of the file: '.$filename);
}
// configuration parameters for parser
$cfg = array('ignore_filter_errors' => true);
// parse PDF data
$pdf = new \Com\Tecnick\Pdf\Parser\Parser($cfg);
$data = $pdf->parse($rawdata);
// display data
var_dump($data);   
echo 'data[0]="'.JSON_encode($data[0]).'"<br><br>';   
echo 'data[1]="'.JSON_encode($data[1]).'"';   
   
   echo '
   </form>';
   echo '</body></html>';

   
   
?>