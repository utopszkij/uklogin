<?php
error_reporting(E_ALL);
define('MYSQLHOST','localhost');
define('MYSQLUSER','root');
// local developer enviroment or travis enviroment ?
if (file_exists('../index.php')) {
	define('MYSQLPSW','13Marika');
} else {
	define('MYSQLPSW','');
}	
define('MYSQLDB','test');
define('MYSQLLOG',false);
define('REFRESHMIN',2);
define('REFRESHMAX',10);
?>