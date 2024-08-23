<?php
class Pages
{
    private $defaultHomeIdx = 'home';

    private $registry;
    private $renderer;

    public function __construct()
    {
        global $website, $renderer;

        $this -> registry = $website;
        $this -> renderer = $renderer;
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> renderer);
    }

    public function getDefaltHomeCode()
    {
        return $this -> defaultHomeIdx;
    }

    /**
     * Start Frontend-Funtions
     */

    public function getFrontendPageByCode($pageCode)
    {
        $pageData = $this -> registry -> db -> querySingleArray('SELECT * FROM `pages` WHERE `seo_code` = "' . $pageCode . '" AND `page_enable` = 1;');

        if ( is_array($pageData) AND count($pageData) ) {
            // found by SEO-Code
            return $this -> _renderForntendPage($pageData);
        }
        else {
            $pageData = $this -> registry -> db -> querySingleArray('SELECT * FROM `pages` WHERE `page_internal` = "' . $pageCode . '" AND `page_enable` = 1;');

            if ( is_array($pageData) AND count($pageData) ) {
                // found by internal
                return $this -> _renderForntendPage($pageData);
            }
            else {
                // load 404-Page
                $pageData = $this -> _renderForntendPage(null);

                $this -> renderer -> loadTemplate('frontend' . DS . '404.htm');
                    $this -> renderer -> addCustonStyle(array('script' => 'skin/css/frontend/default/404.css'), THIS_SCRIPT);
                $pageData['content'] = $this -> renderer -> renderTemplate();

                $pageData['title'] = $this -> registry -> user_lang['page_titles']['not_found'];

                return $pageData;
            }
        }
    }

    /**
     * End Frontend-Funtions
     */


    /**
     * Start Backtend-Funtions
     */
    public function getRenderBlocksFromGridsterJson($jsonData, &$returnArray)
    {
        $counter = 0;
        $this -> _rederLayoutBlocksFromConfig($jsonData, $returnArray, $counter);
    }

    public function getCurrentPages()
    {
        $query = 'SELECT `page_id`, `page_title`, `page_enable`, `is_home`, `username`, `datetime` FROM `pages` ORDER BY `page_id` ASC;';
        $pageData = $this -> registry -> db -> queryObjectArray($query);

        $data = array();
        if ( is_array($pageData) AND count($pageData[0]) ) {
            foreach($pageData AS $item) {
                $enable = ($item['page_enable'] ? 'yes' : 'no');
                $home   = ($item['is_home']     ? 'yes' : 'no');

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_list_line.htm');
                    $this -> renderer -> setVariable('cms_page_id'           , $item['page_id']);
                    $this -> renderer -> setVariable('cms_page_title'        , $item['page_title']);
                    $this -> renderer -> setVariable('cms_page_edit_username', $item['username']);
                    $this -> renderer -> setVariable('cms_page_edit_time'    , $item['datetime']);
                    $this -> renderer -> setVariable('cms_page_raw_home'     , ($home   == 'yes') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('cms_page_raw_enabled'  , ($enable == 'yes') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('cms_page_home'         , $this -> registry -> user_lang['global']['status_' . $home]);
                    $this -> renderer -> setVariable('cms_page_enabled'      , $this -> registry -> user_lang['global']['status_' . $enable]);
                $data[] = $this -> renderer -> renderTemplate();
            }
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_list_empty.htm');
            $data[] = $this -> renderer -> renderTemplate();
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_list.htm');
            $this -> renderer -> setVariable('admin_table_cms_page_list', implode("\n", $data));
        return $this -> renderer -> renderTemplate();
    }

    public function loadPageForEdit($pageId = 0, $formAction = 'savepage')
    {
        $layoutBlockCount = 0;
        $layoutBlockData = array(
                               'header'  => array(),
                               'content' => array(),
                               'footer'  => array(),
                           );


        if ( ($formAction == 'updatepage') AND ($pageId >= 1) ) {
            // load page for edit
            $pageData = $this -> registry -> db -> querySingleArray('SELECT * FROM `pages` WHERE `page_id` = ' . $pageId);

            if ( is_array($pageData) AND count($pageData) ) {
                if (strlen($pageData['page_layout'])) {
                    $pageData['page_layout'] = html_entity_decode($pageData['page_layout']);
                    $this -> _rederLayoutBlocksFromConfig($pageData['page_layout'], $layoutBlockData, $layoutBlockCount);
                }

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_editor.htm');
                    $this -> renderer -> setVariable('cms_form_action'     , $formAction);
                    $this -> renderer -> setVariable('cms_page_id'         , $pageId);
                    $this -> renderer -> setVariable('cms_page_count'      , $layoutBlockCount);
                    $this -> renderer -> setVariable('cms_page_layout'     , $pageData['page_layout']);
                    $this -> renderer -> setVariable('cms_page_title'      , $pageData['page_title']);
                    $this -> renderer -> setVariable('cms_page_internal'   , $pageData['page_internal']);
                    $this -> renderer -> setVariable('cms_page_description', $pageData['page_description']);
                    $this -> renderer -> setVariable('cms_page_keywords'   , $pageData['page_keywords']);
                    $this -> renderer -> setVariable('cms_page_seo'        , $pageData['seo_code']);

                    $this -> renderer -> setVariable('cms_page_layout_header' , implode("\n", $layoutBlockData['header']));
                    $this -> renderer -> setVariable('cms_page_layout_content', implode("\n", $layoutBlockData['content']));
                    $this -> renderer -> setVariable('cms_page_layout_footer' , implode("\n", $layoutBlockData['footer']));

                    $this -> renderer -> setVariable('cms_page_enable', $pageData['page_enable'] ? 'checked' : '');
                    $this -> renderer -> setVariable('cms_page_home'  , $pageData['is_home']     ? 'checked' : '');

                    $this -> renderer -> addCustonStyle(array('script' => 'skin/js/gridster/jquery.gridster.css'), THIS_SCRIPT);
                    $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
                return $this -> renderer -> renderTemplate();
            }
            else {
                // page not loaded
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_page_load_fail']);
                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            // generate new JSON-string from default-layout
            $gridsterJson = $this -> _getDefaultPageLayoutFromConfig();

            $this -> _rederLayoutBlocksFromConfig($gridsterJson, $layoutBlockData, $layoutBlockCount);

            // create new page
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_editor.htm');
                $this -> renderer -> setVariable('cms_form_action'     , $formAction);
                $this -> renderer -> setVariable('cms_page_id'         , $pageId);
                $this -> renderer -> setVariable('cms_page_count'      , $layoutBlockCount);
                $this -> renderer -> setVariable('cms_page_layout'     , $gridsterJson);
                $this -> renderer -> setVariable('cms_page_title'      , '');
                $this -> renderer -> setVariable('cms_page_internal'   , '');
                $this -> renderer -> setVariable('cms_page_description', '');
                $this -> renderer -> setVariable('cms_page_keywords'   , '');
                $this -> renderer -> setVariable('cms_page_seo'        , '');

                $this -> renderer -> setVariable('cms_page_layout_header' , implode("\n", $layoutBlockData['header']));
                $this -> renderer -> setVariable('cms_page_layout_content', '');
                $this -> renderer -> setVariable('cms_page_layout_footer' , implode("\n", $layoutBlockData['footer']));

                $this -> renderer -> setVariable('cms_page_enable', '');
                $this -> renderer -> setVariable('cms_page_home'  , '');

                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/gridster/jquery.gridster.css'), THIS_SCRIPT);
                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function savePage($method, $pageId, $pageTitle, $pageInternal, $pageDescription, $pageKeywords, $pageSeo, $pageEnable, $pageIsHome, $pageLayout, $pageBlockCount)
    {
        $sqlData = array(
                       'page_title'       => $pageTitle,
                       'page_internal'    => $pageInternal,
                       'page_enable'      => $pageEnable,
                       'page_layout'      => $pageLayout,
                       'page_description' => $pageDescription,
                       'page_keywords'    => $pageKeywords,
                       'is_home'          => $pageIsHome,
                       'seo_code'         => $pageSeo,
                       'username'         => $this -> registry -> userinfo['username'],
                   );

        if ( $pageIsHome == true ) {
            if ( $this -> registry -> db -> tableCount('pages') >= 1 ) {
                // reset default-home
                $query = 'UPDATE `pages` SET `is_home` = 0;';
                $this -> registry -> db -> execute($query);
            }

            $sqlData['page_internal'] = $this -> defaultHomeIdx;
            $sqlData['seo_code']      = $this -> defaultHomeIdx;
        }

        if ( ($method == 'new') AND ($pageId == 0) ) {
            // save new page
            $result = $this -> registry -> db -> insertRow($sqlData, 'pages');
        }
        else {
            // update existing page
            $result = $this -> registry -> db -> updateRow($sqlData, 'pages', 'WHERE `page_id` = ' . $pageId);
        }

        if ( $result == true ) {
            // success
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_page_save_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_pages');
            return $this -> renderer -> renderTemplate();
        }
        else {
            // Error
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_page_save_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function deletePageById($pageId = 0)
    {
        if ( $pageId >= 1 ) {
            $query  = 'DELETE FROM `pages` WHERE `page_id` = ' . $pageId;
            $this -> registry -> db -> execute($query);

            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_page_delete_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_blocks');
            return $this -> renderer -> renderTemplate();
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_page_delete_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }
    /**
     * Start Backtend-Funtions
     */


    private function _renderForntendPage($pageData)
    {
        $pageLayout = null;
        $rendered = array(
                        'header'      => '',
                        'content'     => '',
                        'footer'      => '',
                        'navbar'      => '',
                        'description' => (isset($pageData['page_description']) ? $pageData['page_description'] : ''),
                        'keywords'    => (isset($pageData['page_keywords'])    ? $pageData['page_keywords']    : ''),
                        'title'       => (isset($pageData['page_title'])       ? $pageData['page_title']       : $this -> registry -> user_lang['page_titles']['error']),
                    );

        if ( isset($pageData['page_layout']) AND strlen($pageData['page_layout']) ) {
            $pageLayout = json_decode(html_entity_decode($pageData['page_layout']), true);
        }

        if ( !is_array($pageLayout) OR !count($pageLayout) ) {
            // Error => Load default-Blocks
            $pageLayout = json_decode($this -> _getDefaultPageLayoutFromConfig(), true);
        }

        foreach( $pageLayout AS $id => $value ) {
            if ( is_string($value) ) {
                $rendered[$value] = $this -> _renderFrontendLayoutSection($pageLayout[$id + 1]);
                $id++;
            }
        }

        return $rendered;
    }

    private function _getDefaultPageLayoutFromConfig()
    {
        $config = new Config();

        $defaultLayoutHeader = html_entity_decode($config -> getConfigValue('default/layout/header'));
        $defaultLayoutFooter = html_entity_decode($config -> getConfigValue('default/layout/footer'));

        // generate new JSON-string from default-layout
        $gridsterJson = substr($defaultLayoutHeader, 0, -1) . ',"content",[],' . substr($defaultLayoutFooter, 1);

        return $gridsterJson;
    }

    private function _renderFrontendLayoutSection($layout)
    {
        $blocks = array();

        if ( is_array($layout) AND count($layout) ) {
            foreach( $layout AS $id => $block ) {
                if ( empty($block['c_type']) OR !strlen($block['c_type']) ) {
                    $html = '';
                    $blockName = 'page_block_blank.htm';
                }
                elseif ( $block['c_type'] == 'block' ) {
                    $query = 'SELECT `block_content` FROM `blocks` WHERE `block_enable` = 1 AND `block_id` = ' . intval($block['c_id']);
                    $blockContent = $this -> registry -> db -> querySingleItem($query);

                    if ( is_string($blockContent) AND strlen($blockContent) ) {
                        $html = str_replace("\\r", "", $blockContent);
                        $html = stripslashes($html);
                        $html = str_replace(
                                    array('font-family: &quot;', '&quot;;'),
                                    array('font-family: \''    , '\';'),
                                    $html
                                );

                        $blockName = 'page_block.htm';
                    }
                    else {
                        $html = '';
                        $blockName = 'page_block_blank.htm';
                    }
                }
                elseif ( $block['c_type'] == 'module' ) {
                    $class  = $this -> _findClassFromModuleId(intval($block['c_id']));
                    $module = new $class();
                    $html   = $module -> getFrontendBlock();

                    $blockName = 'page_block.htm';
                }

                $this -> renderer -> loadTemplate('frontend' . DS . $blockName);
                    $this -> renderer -> setVariable('block_content_type', $block['c_type']);
                    $this -> renderer -> setVariable('block_content_id'  , $block['c_id']);
                    $this -> renderer -> setVariable('block_col'         , $block['col']);
                    $this -> renderer -> setVariable('block_row'         , $block['row']);
                    $this -> renderer -> setVariable('block_size_x'      , $block['size_x']);
                    $this -> renderer -> setVariable('block_size_y'      , $block['size_y']);
                    $this -> renderer -> setVariable('block_content'     , $html);
                    $this -> renderer -> setVariable('page_col_count'    , 8);               // see cms_page.js:21 (TODO: configurable)
                $blocks[] = $this -> renderer -> renderTemplate();
            }
        }
        return implode("\n", $blocks);
    }

    private function _findClassFromModuleId($moduleId)
    {
        $modulePath = realpath('include/classes/Modules/');
        $files = new FileDir($modulePath);
        $modules = $files -> getFileList('Modules', false);

        if ( is_array($modules) AND count($modules) ) {
            foreach( $modules AS $classFile ) {
                $fileName  = basename($classFile);
                $className = substr($fileName, 6, -4);
                $moduleClass = new $className();
                $moduleIdent = $moduleClass -> getModuleId();

                if ( $moduleIdent == $moduleId ) {
                    return $className;
                }
            }

            return false;
        }
        else {
            return false;
        }
    }

    private function _rederLayoutBlocksFromConfig($config, &$blockOutput, &$blockCount)
    {
        $cmsBlock = new Block();
        $cfgArray = json_decode($config, true);

        $_section = '';

        foreach( $cfgArray AS $id => $value ) {
            if ( is_string($value) ) {
                // page-section
                $_section = trim($value);
            }
            elseif ( is_array($value) ) {
                // section-elements
                foreach( $value AS $idx => $block ) {
                    if ( $block['c_id'] != '' ) {
                        switch( $block['c_type'] ) {
                            case 'block'  : $query = 'SELECT `block_title` FROM `blocks` WHERE `block_id` = ' . intval($block['c_id']);
                                            $block['c_title'] = $this -> registry -> user_lang['admin']['cms_block_popup_prefix'] . ' :: ' .
                                                                $this -> registry -> db -> querySingleItem($query);
                                            break;

                            case 'gallery': $query = 'SELECT `gallery_title` FROM `gallery` WHERE `gallery_id` = ' . intval($block['c_id']);
                                            $block['c_title'] = $this -> registry -> user_lang['admin']['cms_gallery_popup_prefix'] . ' :: ' .
                                                                $this -> registry -> db -> querySingleItem($query);
                                            break;

                            case 'module' : $block['c_title'] = $this -> registry -> user_lang['admin']['cms_modules_popup_prefix'] . ' :: ' .
                                                                $this -> registry -> user_lang['admin']['cms_modules_' . intval($block['c_id'])];
                                            break;
                        }
                    }
                    else {
                        $block['c_title'] = '';
                    }
                    $blockOutput[$_section][] = $cmsBlock -> getBlockForCmsPageWithConfigData($block);
                }

                $blockCount += count($blockOutput[$_section]);
            }
        }
    }
}