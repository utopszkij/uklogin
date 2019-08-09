<?php
	echo '<html><body>';
   $out = shell_exec('pdfsig signed.pdf  2>&1');
	
   echo '<h2>Result from pdfsig signed.pdf</h2>';
	echo '<pre>'.$out.'</pre>';
	
	echo '<p><a href="signed.pdf">download signed.pdf</a></p>';
	
	echo '<h2>php and system info</h2>';
	echo phpinfo();	
	
	echo '<h2>source for this script</h2>';
	$lines = file('test2.php');
	foreach ($lines as $line) {
		$line = str_replace('<','&lt;',$line);	
		$line = str_replace('<','&gt;',$line);
		echo $line.'<br>';	
	}
 	echo '</body></html>';

   
   
?>