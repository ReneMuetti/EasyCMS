<?php
$cmsNavigation = new Navigation();

$website -> input -> clean_array_gpc('p', array(
                                              'nav_data' => TYPE_NOHTML,   // JSON with navigation-data
                                          )
                                    );

if ( isset($website -> GPC['do']) AND strlen($website -> GPC['do']) ) {
    if ( $website -> GPC['do'] == 'save_navigation' ) {
        // save current configuration
        $pageContent = $cmsNavigation -> saveNavigationData($website -> GPC['nav_data']);
    }
    else {
        $pageContent = $website -> user_lang['global']['unkonwn_action'];
    }
}
else {
    $pageContent = $cmsNavigation -> editCurrentNavigation();
}

$pageIdentifier = 'cms-navigation';