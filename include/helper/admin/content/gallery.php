<?php
$galleryManager = new Gallery();

$website -> input -> clean_array_gpc('r', array(
                                              'galleryid'  => TYPE_UINT,
                                              'gallerycfg' => TYPE_NOHTML,    // JSON with type-configuration
                                              'images'     => TYPE_NOHTML,    // JSON with image-data
                                              'title'      => TYPE_NOHTML,
                                              'type'       => TYPE_UINT,
                                              'enable'     => TYPE_BOOL,
                                          )
                                    );

if ( isset($website -> GPC['do']) ) {
    switch($website -> GPC['do']) {
        case 'newgallery'    : $pageContent = $galleryManager -> loadGalleryForEdit();
                               break;
        case 'savegallery'   : $pageContent = $galleryManager -> saveGallery(
                                                                     'new',
                                                                     0,
                                                                     $website -> GPC['title'],
                                                                     $website -> GPC['gallerycfg'],
                                                                     $website -> GPC['images'],
                                                                     $website -> GPC['type'],
                                                                     $website -> GPC['enable']
                                                                 );
                               break;
        case 'editgallery'   : $pageContent = $galleryManager -> loadGalleryForEdit($website -> GPC['galleryid'], 'updategallery');
                               break;
        case 'updategallery' : $pageContent = $galleryManager -> saveGallery(
                                                                     'update',
                                                                     $website -> GPC['galleryid'],
                                                                     $website -> GPC['title'],
                                                                     $website -> GPC['gallerycfg'],
                                                                     $website -> GPC['images'],
                                                                     $website -> GPC['type'],
                                                                     $website -> GPC['enable']
                                                                 );
                               break;
        case 'deletegallery' : $pageContent = $galleryManager -> deleteGalleryById($website -> GPC['galleryid']);
                               break;

        default: $pageContent = $galleryManager -> getCurrentGalleryList();
                 break;
    }
}
else {
    $pageContent = $website -> user_lang['global']['unkonwn_action'];
}

$pageIdentifier = 'content-gallery';