<?php
$dashboard = new Dashboard();

$welcomeBlock    = $dashboard -> createUserWelcomeBlock();
$sectionCmsBlock = $dashboard -> createCmsBlockSection();
$sectionCmsPage  = $dashboard -> createCmsPageSection();
$sectionGallery  = $dashboard -> createGallerySection();

$renderer -> loadTemplate('admin' . DS . 'dashboard' . DS . 'page.htm');
    $renderer -> setVariable('user_welcome_box', $welcomeBlock);
    $renderer -> setVariable('block_cms_blocks', $sectionCmsBlock);
    $renderer -> setVariable('block cms_pages' , $sectionCmsPage);
    $renderer -> setVariable('block_cms_galery', $sectionGallery);
$pageContent = $renderer -> renderTemplate();

$pageIdentifier = 'dashboard';