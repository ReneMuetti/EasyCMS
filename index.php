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
                    'cms_class'   => '',
                );

$cmsPage    = new Pages();
$config     = new Config();
$navigation = new Navigation();

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('r', array('page' => TYPE_NOHTML));


if ( isset($website -> GPC['page']) ) {
    if ( empty($website -> GPC['page']) ) {
        $website -> GPC['page'] = $cmsPage -> getDefaltHomeCode();
    }
    $cmsPage -> getFrontendPageByCode($website -> GPC['page'], $pageElements);
}
else {
    $pageElements['title']   = $website -> user_lang['page_titles']['error'];
    $pageElements['content'] = $website -> user_lang['global']['unkonwn_action'];
}

if ( empty($pageElements['content']) OR !strlen($pageElements['content']) ) {
    $pageElements['title']   = $website -> user_lang['page_titles']['error'];
    $pageElements['content'] = $website -> user_lang['global']['unkonwn_action'];
}

$pageElements['navbar'] = $navigation -> getFrontendNavigation();

$renderer -> loadTemplate(THIS_TEMPLATE . '.htm');
    $renderer -> setVariable('current_page_skin'      , $config -> getConfigValue('design/theme/skin') );
    $renderer -> setVariable('default_page_width'     , $config -> getConfigValue('design/theme/page_width') );
    $renderer -> setVariable('default_block_height'   , '100' ); // TODO :: replace fixed gridster with CSS-GRID
    $renderer -> setVariable('current_page_back_light', $config -> getConfigValue('design/theme/page_back_light') );
    $renderer -> setVariable('current_page_back_dark' , $config -> getConfigValue('design/theme/page_back_dark') );
    $renderer -> setVariable('current_page_code'      , $website -> GPC['page']);

    foreach ($pageElements AS $element => $data) {
        $renderer -> setVariable(THIS_SCRIPT . '_' . $element, $data);
    }

print_output($renderer -> renderTemplate());
