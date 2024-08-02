<?php
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) {
	die('An attempt to manipulate the program by introducing variables was prevented.');
}

if ( !defined('THIS_SCRIPT') ) {
	die('A hacking attempt was prevented.');
}

define('CWD',       (($getcwd = getcwd()) ? $getcwd : '.'));
define('SAPI_NAME', php_sapi_name());

define('CHARSET', 'UTF-8');

// Current Session
define ('SESSION', "Geith-Steinrestaurierung");

session_name(SESSION);
session_start();


$website  = new Registry();
$website -> fetch_config();

$database = new Website_Pdo($website);
$website -> db = $database;

$renderer = Templater::getInstance();


if ( !isset($website -> userinfo['language']) ) {
    // not-loggedin-User switched Language
    $website -> input -> clean_array_gpc('r', array('lang' => TYPE_NOHTML));
    if ( !empty($website -> GPC['lang']) ) {
        $website -> change_language($website -> GPC['lang']);
        $renderer -> updateLanguage();
    }
}
