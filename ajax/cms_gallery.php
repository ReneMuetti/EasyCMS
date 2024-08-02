<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_cms_gallery');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$result = array(
              'error'   => false,
              'message' => null,
              'data'    => null
          );

$method = 'r';

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc($method, array(
                                                  'action'   => TYPE_NOHTML,    // Action
                                                  'option'   => TYPE_UINT,      // index from selected gallery-type
                                              )
                                    );

if ( ($website -> GPC['action'] == 'gallery_option') AND ($website -> GPC['option'] >= 1) ) {
    $content = new Gallery();

    $result = $content -> getGalleryOptionTemplate($website -> GPC['option']);
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);