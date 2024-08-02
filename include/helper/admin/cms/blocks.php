<?php
$cmsBlock = new Block();

$website -> input -> clean_array_gpc('r', array(
                                              'blockid' => TYPE_UINT,
                                              'title'   => TYPE_NOHTML,
                                              'content' => TYPE_STR,
                                              'enable'  => TYPE_BOOL,
                                          )
                                    );

if ( isset($website -> GPC['do']) ) {
    switch($website -> GPC['do']) {
        case 'newblock'   : $pageContent = $cmsBlock -> loadBlockForEdit();
                            break;
        case 'saveblock'  : $pageContent = $cmsBlock -> saveBlock(
                                                            'new',
                                                            0,
                                                            $website -> GPC['title'],
                                                            $website -> GPC['content'],
                                                            $website -> GPC['enable']
                                                        );
                            break;
        case 'editblock'  : $pageContent = $cmsBlock -> loadBlockForEdit($website -> GPC['blockid'], 'updateblock');
                            break;
        case 'updateblock': $pageContent = $cmsBlock -> saveBlock(
                                                            'update',
                                                            $website -> GPC['blockid'],
                                                            $website -> GPC['title'],
                                                            $website -> GPC['content'],
                                                            $website -> GPC['enable']
                                                        );
                            break;
        case 'deleteblock': $pageContent = $cmsBlock -> deleteBlockById($website -> GPC['blockid']);
                            break;
        default: $pageContent = $cmsBlock -> getCurrentBlocks();
                 break;
    }
}
else {
    $pageContent = $website -> user_lang['global']['unkonwn_action'];
}

$pageIdentifier = 'cms-blocks';