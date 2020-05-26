<?php
error_reporting(E_ALL);
global $config;

function defineConfig(string $name, string $value) {
    global $config;
    $config[$name] = $value;
    if (($name == 'MYDOMAIN') | ($name == 'MYPATH') | ($name == 'TEMPLATE')) {
        define($name, $value);
    }
}

function config($name) {
    global $config;
    if (isset($config[$name])) {
        $result = $config[$name];
    } else {
        $result = $name;
    }
    return $result;
}

defineConfig('MYSQLHOST','localhost');
defineConfig('MYSQLUSER','root');
// local developer enviroment or travis enviroment ?
if (file_exists('../index.php')) {
    defineConfig('MYSQLPSW','13Marika');
} else {
    defineConfig('MYSQLPSW','');
}	
defineConfig('MYDOMAIN','');
defineConfig('MYPATH','');
defineConfig('MYSQLDB','test');
defineConfig('MYSQLLOG',true);
defineConfig('REFRESHMIN',2);
defineConfig('REFRESHMAX',10);
defineConfig('CODE_EXPIRE',120);

defineConfig('GITHUB_REPO','');
defineConfig('GITHUB_USER','');
defineConfig('GITHUB_PSW','');

defineConfig('OPENID',1); // felülirható
?>