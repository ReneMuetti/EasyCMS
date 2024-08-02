<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_cms_block');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$result = array(
              'error'   => false,
              'message' => null,
              'data'    => null
          );

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'action' => TYPE_NOHTML,    // Action
                                              'prefix' => TYPE_NOHTML,    // Prefix from Block-Elements
                                              'number' => TYPE_UINT,      // ID for new Element
                                          )
                                    );

if ( ($website -> GPC['action'] == 'new_block') AND ($website -> GPC['number'] >= 1) AND strlen($website -> GPC['prefix']) ) {
    $cmsBlock = new Block();

    $result['data'] = $cmsBlock -> getNewBlockForCmsPage($website -> GPC['prefix'],$website -> GPC['number']);
}
elseif( $website -> GPC['action'] == 'block_list' ) {
    $cmsBlock = new Block();

    $result = $cmsBlock -> getAllBlocksForLayout();
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);