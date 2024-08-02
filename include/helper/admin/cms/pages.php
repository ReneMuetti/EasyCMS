<?php
$cmsPage = new Pages();

$website -> input -> clean_array_gpc('r', array(
                                              'pageid'      => TYPE_UINT,
                                              'layout'      => TYPE_NOHTML,   // JSON from Page-Layout
                                              'blockcount'  => TYPE_UINT,     // count of blocks
                                              'title'       => TYPE_NOHTML,
                                              'description' => TYPE_NOHTML,
                                              'keywords'    => TYPE_NOHTML,
                                              'seo'         => TYPE_NOHTML,
                                              'enable'      => TYPE_BOOL,
                                              'home'        => TYPE_BOOL,
                                          )
                                    );


if ( isset($website -> GPC['do']) ) {
    switch($website -> GPC['do']) {
        case 'newpage'    : $pageContent = $cmsPage -> loadPageForEdit();
                            break;
        case 'savepage'   : $pageContent = $cmsPage -> savePage(
                                                           'new',
                                                           0,
                                                           $website -> GPC['title'],
                                                           $website -> GPC['description'],
                                                           $website -> GPC['keywords'],
                                                           $website -> GPC['seo'],
                                                           $website -> GPC['enable'],
                                                           $website -> GPC['home'],
                                                           $website -> GPC['layout'],
                                                           $website -> GPC['blockcount']
                                                       );
                            break;
        case 'editpage'   : $pageContent = $cmsPage -> loadPageForEdit($website -> GPC['pageid'], 'updatepage');
                            break;
        case 'updatepage' : $pageContent = $cmsPage -> savePage(
                                                           'update',
                                                           $website -> GPC['pageid'],
                                                           $website -> GPC['title'],
                                                           $website -> GPC['description'],
                                                           $website -> GPC['keywords'],
                                                           $website -> GPC['seo'],
                                                           $website -> GPC['enable'],
                                                           $website -> GPC['home'],
                                                           $website -> GPC['layout'],
                                                           $website -> GPC['blockcount']
                                                       );
                            break;
        case 'deletepage' : $pageContent = $cmsPage -> deletePageById($website -> GPC['pageid']);
                            break;
        default: $pageContent = $cmsPage -> getCurrentPages();
                 break;
    }
}
else {
    $pageContent = $website -> user_lang['global']['unkonwn_action'];
}

$pageIdentifier = 'cms-pages';