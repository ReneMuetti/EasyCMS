<?php
//error_reporting (E_ALL);
//ini_set ('display_errors', 'On');

date_default_timezone_set('Europe/Berlin');

if ( !is_file( realpath('./include/configs/database.php') ) OR !is_file( realpath('./include/configs/misc.php') ) ) {
    die("<!DOCTYPE html><html><head><title>ERROR</title><link rel=\"shortcut icon\" href=\"favicon.ico\"></head><body><p>The website has not yet been configured.</p></body></html>");
}

require_once( realpath('./include/functions/function_autoload.php') ); // Default-Class-Loader
require_once( realpath('./include/functions/function_global.php') );   // Common-Functions
require_once( realpath('./include/functions/function_sha512.php') );   // SHA512-Functions for Login
require_once( realpath('./include/functions/function_login.php') );    // Login and Register

/**
 * Helper including
 */
require_once( realpath('./include/helper/file.php') );  // File-System
require_once( realpath('./include/helper/xml.php') );   // XML-Functions

/**
 * Base-Init
 * Registry, Database
 */
require_once( realpath('./include/functions/function_init.php') );


if ( !defined('LOCAL_CHARSET') ) {
    define('LOCAL_CHARSET', $website -> config['Misc']['charset']);
}

if ( !defined('TIMENOW') ) {
    define('TIMENOW', time());
}

if ( !defined('DIR') ) {
    define('DIR', $website -> config['Misc']['path'] . '/');
}

if ( !defined('BASEDIR') ) {
    define('BASEDIR', $website -> config['Misc']['path'] . '/');
}

if ( !defined('DS') ) {
    define('DS', DIRECTORY_SEPARATOR );
}