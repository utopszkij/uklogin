<?php
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
   echo '</body></html>';

   
   
?>