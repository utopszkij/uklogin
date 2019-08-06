<?php
	/*
   if (isset($_GET['cmd'])) {
   	$lines = shell_exec(urldecode($_GET['cmd']).' 2>&1');
   	$lines = str_replace("\n", '<br />', $lines);
   	echo '<div style="background-color:silver">'.$lines.'</div>';
   }
	*/
	
	
	require_once('/var/www/html/uklogin/vendor/TCPDF-master/tcpdf_parser.php');

   // Egyes szervereken nem megy a pdfsig hivás :(
   echo '<html><body><h1>pdf aláírás ellenörzés tesztelése</h1>';
   $filePath = './avdhA3-18840f38-7adf-4f8a-a8b2-c3e307d63b48.pdf';		    
   
   $signatureArray = explode(PHP_EOL, shell_exec('pdfsig ' . escapeshellarg($filePath).' 2>&1'));

   echo '<p>pdfsig hívás eredmnye: '.JSON_encode($signatureArray).'<p>'; 
   
   $res = new stdClass();
   $handle = fopen($filePath, 'r');
   $res->error = 'ERROR_PDF_SIGN_ERROR'; // init as false
   while (($buffer = fgets($handle)) !== false) {
	       if (strpos($buffer, 'adbe.pkcs7.detached') !== false) {
	                $res->error = '';
	                break; 
	       }
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
   
   
   // TSPDF parset test

   $rawdata = file_get_contents($filePath);
   if ($rawdata === false) {
            $this->Error('Unable to get the content of the file: ' . $filePath);
   }
   // configuration parameters for parser
   $cfg = array(
            'die_for_errors' => false,
            'ignore_filter_decoding_errors' => true,
            'ignore_missing_filter_decoders' => true,
   );

	try {
       $pdf = new TCPDF_PARSER($rawdata, $cfg);
   } catch (Exception $e) {
       die($e->getMessage());
   }   
   
   $datas = $pdf->getParsedData();
   
   echo '<h2>TCPDF parser</h2>';
   echo '[datas '.JSON_encode($datas).' datas]';   
   
	/*
   echo '<form method="GET" action="./test.php">
   <p>command:<input type="text" name="cmd" size="120" />
	<button type="submit">start</button>  
   </p>
   */
   echo '
   </form>';
   echo '</body></html>';

   
   
?>