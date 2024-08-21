<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_vita');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'action'  => TYPE_NOHTML,    // Action
                                              'last-id' => TYPE_UINT,
                                          )
                                    );

if ( $website -> GPC['action'] == 'get_id' ) {
    $newID = $website -> db -> maxID('vita_id', 'vita');

    if ( is_int($newID) AND ($newID >= 0) ) {
        if ( $newID == 0 ) {
            if ( $website -> GPC['last-id'] >= $newID ) {
                echo ++$website -> GPC['last-id'];
            }
            else {
                echo 1;
            }
        }
        else {
            if ( $website -> GPC['last-id'] >= $newID ) {
                echo ++$website -> GPC['last-id'];
            }
            else {
                echo ++$newID;
            }
        }
    }
    else {
        echo '-2';
    }
}
else {
    echo '-1';
}