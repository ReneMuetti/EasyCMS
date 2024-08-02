<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'index');

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
$website -> input -> clean_array_gpc('g', array('action' => TYPE_NOHTML));


if ( isset($website -> GPC['action']) ) {
}
else {
    $content = $website -> user_lang['global']['unkonwn_action'];
}

if ( empty($content) OR !strlen($content) ) {
    $content = $website -> user_lang['global']['unkonwn_action'];
}

$renderer -> loadTemplate(THIS_SCRIPT . '.htm');
    $renderer -> addCustonStyle(array('script' => 'skin/css/index.css'), THIS_SCRIPT);
    $renderer -> setVariable(THIS_SCRIPT . '_content', $content);
print_output($renderer -> renderTemplate());
