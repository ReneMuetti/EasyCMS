<?php
class Pages
{
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
        if ( ($formAction == 'updatepage') AND ($pageId >= 1) ) {
            // load page for edit
            $pageData = $this -> registry -> db -> querySingleArray('SELECT * FROM `pages` WHERE `page_id` = ' . $pageId);

            $layoutBlockCount = 0;
            $layoutBlockData = array(
                                   'header'  => array(),
                                   'content' => array(),
                                   'footer'  => array(),
                               );

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
            // create new page
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'page_editor.htm');
                $this -> renderer -> setVariable('cms_form_action'     , $formAction);
                $this -> renderer -> setVariable('cms_page_id'         , $pageId);
                $this -> renderer -> setVariable('cms_page_count'      , 0);
                $this -> renderer -> setVariable('cms_page_layout'     , '');
                $this -> renderer -> setVariable('cms_page_title'      , '');
                $this -> renderer -> setVariable('cms_page_description', '');
                $this -> renderer -> setVariable('cms_page_keywords'   , '');
                $this -> renderer -> setVariable('cms_page_seo'        , '');

                $this -> renderer -> setVariable('cms_page_layout_header' , '');
                $this -> renderer -> setVariable('cms_page_layout_content', '');
                $this -> renderer -> setVariable('cms_page_layout_footer' , '');

                $this -> renderer -> setVariable('cms_page_enable', '');
                $this -> renderer -> setVariable('cms_page_home'  , '');

                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/gridster/jquery.gridster.css'), THIS_SCRIPT);
                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function savePage($method, $pageId, $pageTitle, $pageDescription, $pageKeywords, $pageSeo, $pageEnable, $pageIsHome, $pageLayout, $pageBlockCount)
    {
        $sqlData = array(
                       'page_title'       => $pageTitle,
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
                        $query = 'SELECT `block_title` FROM `blocks` WHERE `block_id` = ' . intval($block['c_id']);
                        $block['c_title'] = $this -> registry -> db -> querySingleItem($query);
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