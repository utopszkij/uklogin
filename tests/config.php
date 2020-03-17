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
define('MYDOMAIN','');
define('MYPATH','');
define('MYSQLDB','test');
define('MYSQLLOG',true);
define('REFRESHMIN',2);
define('REFRESHMAX',10);
define('CODE_EXPIRE',120);

define('GITHUB_REPO','');
define('GITHUB_USER','');
define('GITHUB_PSW','');

?>