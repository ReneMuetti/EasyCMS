<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_media_manager');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$result = array(
              'error'   => false,
              'message' => null,
              'data'    => null
          );

$method = 'p';

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc($method, array(
                                                  'action'    => TYPE_NOHTML,
                                                  'path'      => TYPE_NOHTML,
                                                  'directory' => TYPE_NOHTML,
                                                  'filename'  => TYPE_NOHTML,
                                                  'parent'    => TYPE_BOOL,
                                              )
                                    );

if ( $website -> GPC['action'] == 'new_directory' ) {
    if ( strlen($website -> GPC['path']) AND strlen($website -> GPC['directory']) ) {
        $mediaManager = new MediaManager();

        $result = $mediaManager -> addNewDirectory($website -> GPC['path'], $website -> GPC['directory']);
    }
    else {
        $result['error']   = true;
        $result['message'] = $this -> registry -> user_lang['global']['error_sending_data'];
    }
}
elseif ( $website -> GPC['action'] == 'change_directory' ) {
    if ( strlen($website -> GPC['path']) AND strlen($website -> GPC['directory']) ) {
        $mediaManager = new MediaManager();

        $result = $mediaManager -> changeDirectory($website -> GPC['path'], $website -> GPC['directory'], $website -> GPC['parent']);
    }
    else {
        $result['error']   = true;
        $result['message'] = $website -> user_lang['global']['error_sending_data'];
    }
}
elseif ( $website -> GPC['action'] == 'delete_directory' ) {
    if ( strlen($website -> GPC['path']) AND strlen($website -> GPC['directory']) ) {
        $mediaManager = new MediaManager();

        $result = $mediaManager -> deleteDirectory($website -> GPC['path'], $website -> GPC['directory']);
    }
    else {
        $result['error']   = true;
        $result['message'] = $website -> user_lang['global']['error_sending_data'];
    }
}
elseif ( $website -> GPC['action'] == 'delete_file' ) {
     if ( strlen($website -> GPC['path']) AND strlen($website -> GPC['filename']) ) {
        $mediaManager = new MediaManager();

        $result = $mediaManager -> deleteFileFromDirectory($website -> GPC['path'], $website -> GPC['filename']);
     }
     else {
        $result['error']   = true;
        $result['message'] = $website -> user_lang['global']['error_sending_data'];
     }
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);