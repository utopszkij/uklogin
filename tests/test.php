<?php
	/*
   if (isset($_GET['cmd'])) {
   	$lines = shell_exec(urldecode($_GET['cmd']).' 2>&1');
   	$lines = str_replace("\n", '<br />', $lines);
   	echo '<div style="background-color:silver">'.$lines.'</div>';
   }
	*/
	
    include '../vendor/autoload.php';


	echo phpinfo();	
	
   // Egyes szervereken nem megy a pdfsig hivás :(
   echo '<html><body><h1>pdf aláírás ellenörzés tesztelése</h1>';
   $filePath = './avdhA3-18840f38-7adf-4f8a-a8b2-c3e307d63b48.pdf';		    
   
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
   $objects = $pdf->getObjects();
   $text = $pdf->getText();
   $signed = false;   
   echo '[text '.JSON_encode($text).' text]<br />';
   echo '<h3>objects<h3>';
   foreach ($objects as $on => $object) {
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
                echo 'Contents '.JSON_encode($element->parse($element->getContent())).'<br />';
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
	echo '<p>signed='.$signed.'</p>';   
   
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