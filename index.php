<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'index');
define('THIS_TEMPLATE', 'frontend/page');

// ######################### REQUIRE BACK-END ############################
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$navigation = '';
$content = '';

// ########################### IDENTIFY USER #############################
//loggedInOrReturn();
//setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('g', array('page' => TYPE_NOHTML));


if ( isset($website -> GPC['page']) ) {
    $content = $website -> GPC['page'];
}
else {
    $content = $website -> user_lang['global']['unkonwn_action'];
}

if ( empty($content) OR !strlen($content) ) {
    $content = $website -> user_lang['global']['unkonwn_action'];
}

$renderer -> loadTemplate(THIS_TEMPLATE . '.htm');
    //$renderer -> addCustonStyle(array('script' => 'skin/css/index.css'), THIS_SCRIPT);
    $renderer -> setVariable('current_page_skin'      , 'geith');
    $renderer -> setVariable('current_page_back_light', '/skin/images/frontend/geith/page_background_light.jpg');
    $renderer -> setVariable('current_page_back_dark' , '/skin/images/frontend/geith/page_background_dark.jpg');
    $renderer -> setVariable('current_page_skin'      , 'geith');
    $renderer -> setVariable('default_page_width'     , '1200px');
    $renderer -> setVariable(THIS_SCRIPT . '_content' , $content);
print_output($renderer -> renderTemplate());
