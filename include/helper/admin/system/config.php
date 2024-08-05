<?php
$config = new Config();

if ( isset($website -> GPC['do']) ) {
    switch($website -> GPC['do']) {
        case 'update_config' : // Update
                               break;
        default: $pageContent = $config -> getCurrentConfig();
                 break;
    }
}
else {
    $pageContent = $website -> user_lang['global']['unkonwn_action'];
}

$pageIdentifier = 'system-configuration';