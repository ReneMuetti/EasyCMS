<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'admin_index');
define('THIS_TEMPLATE', 'admin/index');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$header = '';
$footer = '';
$navigation = '';
$pageIdentifier = 'index';
$pageContent = '';

// ########################### IDENTIFY USER #############################
loggedInOrReturn();
checkIfUserIsAdmin();
setDefaultForLoggedinUser();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('r', array(
                                              'action'    => TYPE_NOHTML,
                                              'do'        => TYPE_NOHTML,
                                          )
                                    );

if ($website -> userinfo) {
    if ( empty($website -> GPC['action']) OR !strlen($website -> GPC['action']) ) {
        $website -> GPC['action'] = 'dashboard';
    }

    if ( $website -> GPC['action'] == 'myaccount' ) {
        // your own user profile
        $profile = new UserProfile();
        $pageIdentifier = 'account';

        if ( $website -> GPC['do'] == 'updateMyAccount' ) {
            $pageContent = $profile -> updateCurrentProfile();
        }
        else {
            $pageContent = $profile -> getCurrentProfile();
        }
    }
    elseif ( $website -> GPC['action'] == 'accounts' ) {
        // other user accounts
        $website -> input -> clean_array_gpc('r', array('accountid' => TYPE_UINT));

        $profile = new UserProfile();
        $pageIdentifier = 'account';

        if ( isset($website -> GPC['accountid']) AND ($website -> GPC['accountid'] >= 1) ) {
            if ( isset($website -> GPC['do']) AND ($website -> GPC['do'] == 'edit') ) {
                $pageContent = $profile -> getProfileFromUser($website -> GPC['accountid'], true);
            }
            elseif ( isset($website -> GPC['do']) AND ($website -> GPC['do'] == 'updateaccount') ) {
                $renderer -> addCustonStyle(array('script' => 'skin/css/account.css'), THIS_SCRIPT);
                $pageContent = $profile -> updateProfileByID($website -> GPC['accountid']);
            }
            elseif ( isset($website -> GPC['do']) AND ($website -> GPC['do'] == 'delete') ) {
                $pageContent = $profile -> deleteProfileById($website -> GPC['accountid']);
            }
            else {
                $pageContent = $website -> user_lang['global']['unkonwn_action'];
            }
        }
        else {
            // show Account-List
            $pageContent = $profile -> getUserListForAdmin();
        }
    }
    else {
        $pathString      = '.' . DS . 'include' . DS . 'helper' . DS . 'admin' . DS .
                           str_replace('_', DS, $website -> GPC['action']) . '.php';
        $fullIncludeFile = realpath($pathString);

        if ( is_file($fullIncludeFile) ) {
            include( $fullIncludeFile );
        }
        else {
            $renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $renderer -> setVariable('error_message', $website -> user_lang['global']['error_loading_module'] . '<br />' . $pathString);
            $pageContent  = $renderer -> renderTemplate();
        }
    }
}

if ( isset($website -> GPC['action']) AND strlen($website -> GPC['action']) ) {
    $pageTitle = $website -> user_lang['page_titles']['admin_' . $website -> GPC['action']];
}
else {
    $pageTitle   = '';

    $renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
        $renderer -> setVariable('error_message', $website -> user_lang['global']['unkonwn_action']);
    $pageContent = $renderer -> renderTemplate();
}

$renderer -> loadTemplate('admin' . DS . 'default' . DS . 'header.htm');
    $renderer -> setVariable('custom_heeader_string', $website -> user_lang['default_admin_title']);
$header = $renderer -> renderTemplate();

$renderer -> loadTemplate('admin' . DS . 'default' . DS . 'footer.htm');
    $renderer -> setVariable('footer_current_date'  , date("d.m.Y", TIMENOW) );
    $renderer -> setVariable('footer_current_time'  , date("H:i:s", TIMENOW) );
    $renderer -> setVariable('footer_db_performance', $website -> db -> showStatistics());
$footer = $renderer -> renderTemplate();

$renderer -> loadTemplate(THIS_TEMPLATE . '.htm');
    $renderer -> setVariable('title_admin'    , $pageTitle);
    $renderer -> setVariable('global_header'  , $header);
    $renderer -> setVariable('global_navbar'  , $navigation);
    $renderer -> setVariable('page_identifier', $pageIdentifier);
    $renderer -> setVariable('page_content'   , $pageContent);
    $renderer -> setVariable('global_footer'  , $footer);
print_output($renderer -> renderTemplate());