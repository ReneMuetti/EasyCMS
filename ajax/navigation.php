<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_navigation');

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
                                              'nav-id'        => TYPE_UINT,       // 0 = new; current Nav-ID
                                              'nav-parent-id' => TYPE_UINT,       // 0 = root-Element; ID from Parent-Element
                                              'nav-position'  => TYPE_UINT,       // Element-Position in current Level
                                              'nav-title'     => TYPE_NOHTML,     // Title
                                              'nav-enable'    => TYPE_BOOL,       // is Enable?
                                              'nav-type'      => TYPE_UINT,       // 0 = internal; 1 = external
                                              'nav-cms'       => TYPE_UINT,       // ID from CMS-Page
                                              'nav-url'       => TYPE_NOHTML,     // external URL
                                          )
                                    );

if ( isset($website -> GPC['nav-title']) AND strlen($website -> GPC['nav-title']) ) {
    $cmsNavigation = new Navigation();

    $result = $cmsNavigation -> saveNavigationItem($website -> GPC['nav-id'],
                                                   $website -> GPC['nav-parent-id'],
                                                   $website -> GPC['nav-title'],
                                                   $website -> GPC['nav-enable'],
                                                   $website -> GPC['nav-type'],
                                                   $website -> GPC['nav-cms'],
                                                   $website -> GPC['nav-url'],
                                                   $website -> GPC['nav-position'],
                                                  );
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);