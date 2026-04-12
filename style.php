<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'style');

// ######################### REQUIRE BACK-END ############################
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$cssContent = new CssLoader();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('g', array(
                                              'type'     => TYPE_NOHTML,
                                              'minimize' => TYPE_BOOL
                                          )
                                    );

if ( isset($website -> GPC['minimize']) AND ( $website -> GPC['minimize'] == true ) ) {
    $cssContent -> setMinimize($website -> GPC['minimize']);
}

if ( isset($website -> GPC['type']) ) {
    if ( $website -> GPC['type'] == 'css' ) {
        header('Content-type: text/css; charset: UTF-8');
        echo $cssContent -> render();
    }
    elseif ( $website -> GPC['type'] == 'js' ) {
        header('Content-Type: application/javascript;  charset: UTF-8');
    }
}