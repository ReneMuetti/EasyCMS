<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'index');
define('THIS_TEMPLATE', 'frontend/page');

// ######################### REQUIRE BACK-END ############################
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$pageElements = array(
                    'header'      => '',
                    'navbar'      => '',
                    'content'     => '',
                    'footer'      => '',
                    'description' => '',
                    'keywords'    => '',
                    'title'       => '',
                );

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('r', array('page' => TYPE_NOHTML));


if ( isset($website -> GPC['page']) ) {
    $cmsPage = new Pages();

    if ( empty($website -> GPC['page']) ) {
        $website -> GPC['page'] = $cmsPage -> getDefaltHomeCode();
    }
    $pageElements = $cmsPage -> getFrontendPageByCode($website -> GPC['page']);
}
else {
    $pageElements['title']   = $website -> user_lang['page_titles']['error'];
    $pageElements['content'] = $website -> user_lang['global']['unkonwn_action'];
}

if ( empty($pageElements['content']) OR !strlen($pageElements['content']) ) {
    $pageElements['title']   = $website -> user_lang['page_titles']['error'];
    $pageElements['content'] = $website -> user_lang['global']['unkonwn_action'];
}

$renderer -> loadTemplate(THIS_TEMPLATE . '.htm');
    $renderer -> setVariable('current_page_skin'      , 'geith');
    $renderer -> setVariable('current_page_back_light', '/skin/images/frontend/geith/page_background_light.jpg');
    $renderer -> setVariable('current_page_back_dark' , '/skin/images/frontend/geith/page_background_dark.jpg');
    $renderer -> setVariable('current_page_skin'      , 'geith');
    $renderer -> setVariable('current_page_code'      , $website -> GPC['page']);
    $renderer -> setVariable('default_page_width'     , '1200');
    $renderer -> setVariable('default_block_height'   , '100');

    foreach ($pageElements AS $element => $data) {
        $renderer -> setVariable(THIS_SCRIPT . '_' . $element, $data);
    }

    //$renderer -> addCustonStyle(array('script' => 'skin/css/index.css'), THIS_SCRIPT);
print_output($renderer -> renderTemplate());
