<?php
$config = new Config();

if ( isset($website -> GPC['do']) ) {
    switch($website -> GPC['do']) {
        case 'update_config' : $pageContent = $config -> updateCurrentConfig();
                               break;
        default: $pageContent = $config -> getCurrentConfig();
                 break;
    }
}
else {
    $pageContent = $website -> user_lang['global']['unkonwn_action'];
}

$pageIdentifier = 'system-configuration';