<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_file_popup');

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
                                                  'subdir'   => TYPE_NOHTML,    // current directory
                                                  'multi'    => TYPE_BOOL,      // using Checkbox or Single-Elements
                                                  'elements' => TYPE_NOHTML,    // imagelist to append
                                              )
                                    );

if ( ($website -> GPC['action'] == 'block_list') AND strlen($website -> GPC['subdir']) ) {
    $content = new MediaManager();

    $result = $content -> getGalleryPopup($website -> GPC['subdir'], $website -> GPC['multi']);
}
elseif ( ($website -> GPC['action'] == 'add_elements') AND strlen($website -> GPC['elements']) ) {
    $content = new MediaManager();

    $result = $content -> addImagesToGallery($website -> GPC['elements']);
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);