<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_navigation');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$result = array(
              'error'   => false,
              'message' => null
          );

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'action' => TYPE_NOHTML,   // Action
                                              'navid'  => TYPE_UINT,     // ID from Block-Elements
                                              'state'  => TYPE_BOOL,     // new State for Element
                                          )
                                    );

if ( ($website -> GPC['action'] == 'change_state') AND ($website -> GPC['navid'] >= 1) ) {
    $nav = new Navigation();

    $result['error'] = $nav -> switchNavigationItemState($website -> GPC['navid'], $website -> GPC['state']);

    if ( $result['error'] == true ) {
        $website -> user_lang['global']['error_saving_data'];
    }
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);