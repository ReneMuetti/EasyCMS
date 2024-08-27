<?php
define('APP_ROOT', getcwd());
define('VERSION_FILE', '.version');

if ( is_file(APP_ROOT . DIRECTORY_SEPARATOR . VERSION_FILE) ) {
    $config_data['Cms']['version'] = file_get_contents(APP_ROOT . DIRECTORY_SEPARATOR . VERSION_FILE);
}
else {
    $config_data['Cms']['version'] = '!!ERROR!!';
}

$config_data['Session']['name'] = '** Default-Session-Name **';

$config_data['Host']['protocol'] = 'https';
$config_data['Host']['host']     = '** full-domain-name **';
$config_data['Host']['script']   = THIS_SCRIPT . '.php';

$config_data['Mail']['charset']  = 'utf-8';
$config_data['Mail']['host']     = '** email-server **';
$config_data['Mail']['smtpauth'] = TRUE;
$config_data['Mail']['username'] = '** email-username **';
$config_data['Mail']['address']  = '** email-sender-address **';
$config_data['Mail']['password'] = '** email-password **';
$config_data['Mail']['port']     = '** email-server-port **';
$config_data['Mail']['secure']   = FALSE;
$config_data['Mail']['protocol'] = '** encryption-method **';
$config_data['Mail']['sender']   = '** Title for Recipient **';
$config_data['Mail']['subject']  = '** Default Subject **';
$config_data['Mail']['rec_mail'] = '** E-Mail fom Recipient **';
$config_data['Mail']['rec_name'] = '** Name for Recipient **';
$config_data['Mail']['dev_mail'] = '** E-Mail for Developer-BCC and for Debug-Mode **';

$config_data['Misc']['path']              = APP_ROOT;
$config_data['Misc']['media_directory']   = 'media';
$config_data['Misc']['skin_directory']    = 'skin';
$config_data['Misc']['js_directory']      = 'skin/js';
$config_data['Misc']['design_directory']  = 'skin/css/frontend';
$config_data['Misc']['log_directory']     = 'var/log';
$config_data['Misc']['upload_directory']  = 'var/temp';
$config_data['Misc']['baseurl']           = $config_data['Host']['protocol'] . '://' . $config_data['Host']['host'] . '/';
$config_data['Misc']['charset']           = 'UTF-8';
$config_data['Misc']['showtemplatenames'] = FALSE;
$config_data['Misc']['showtemplatetree']  = TRUE;
$config_data['Misc']['jquery_version']    = '3.7.1';
