<?php
class Navigation
{
    private $registry;
    private $renderer;

    private $currNavigationData = null;
    private $currNavigationHTML = '';
    private $currCmsPages       = '';
    private $currCmsArray       = array();

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

    public function switchNavigationItemState($navID, $newState)
    {
        $sqlData = array(
                       'item_enable' => $newState,
                   );
        $insertResult = $this -> registry -> db -> updateRow($sqlData, 'navigation', 'WHERE `item_id` = ' . $navID);

        return ( ( $insertResult == false ) ? true : false);
    }

    public function editCurrentNavigation()
    {
        $this -> currNavigationData = null;
        $this -> currNavigationHTML = '';
        $this -> currCmsPages       = '';

        $this -> _loadCmsFromDatabase();
        $this -> _loadNavigationFromDataBase();

        $buttons    = $this -> _renderDefaultButton();
        $jsTemplate = $this -> _createJsTemplateForNewItems();

        if ( is_array($this -> currNavigationData) AND count($this -> currNavigationData) ) {
            $this -> _renderCurrentNavigation();
            $currNavEmpty = 'false';
        }
        else {
            $currNavEmpty = 'true';
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'admin.htm');
            $this -> renderer -> setVariable('cms_navigation_add_button', $buttons );
            $this -> renderer -> setVariable('cms_navigation'           , $this -> currNavigationHTML);
            $this -> renderer -> setVariable('current_cms_pages'        , $this -> currCmsPages);
            $this -> renderer -> setVariable('current_nav_empty'        , $currNavEmpty);
            $this -> renderer -> setVariable('item_template'            , $jsTemplate );

            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
        return $this -> renderer -> renderTemplate();
    }

    public function saveNavigationData($navigationData)
    {
        $sqlData   = array();
        $countOk   = 0;
        $countFail = 0;

        $navJSON = json_decode( html_entity_decode($navigationData), true );

        if ( is_array($navJSON) AND count($navJSON) ) {
            $this -> registry -> db -> execute('TRUNCATE TABLE `navigation`;');

            foreach( $navJSON AS $navElement ) {
                $sqlData = array(
                               'item_id'      => $navElement['item-id'],
                               'item_element' => $navElement['id'],
                               'item_title'   => $navElement['title'],
                               'item_class'   => $navElement['class'],
                               'item_pos'     => $navElement['position'],
                               'item_parent'  => $navElement['parent'],
                               'item_enable'  => ( ($navElement['enable'] == "true") ? 1 : 0 ),
                               'item_home'    => ( ($navElement['home']   == "true") ? 1 : 0 ),
                               'item_type'    => $navElement['type'],
                               'item_cms'     => $navElement['cms-id'],
                               'item_url'     => $navElement['url'],
                           );
                $insertResult = $this -> registry -> db -> insertRow($sqlData, 'navigation');

                if ( $insertResult == false ) {
                    $countFail++;
                }
                else {
                    $countOk++;
                }
            }

            if ( $countOk == count($navJSON) ) {
                // success
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                    $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['navigation_message_successfuly_saved']);
                    $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_navigation');
                return $this -> renderer -> renderTemplate();
            }
            else {
                // insert-errors
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['navigation_message_save_error']);
                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            // JSON-parse-error
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['navigation_message_json_error']);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function getFrontendNavigation()
    {
        $html = array();

        $data = $this -> _getNavigationData();
        $this -> _renderFrontendNavigation($data, $html, 1);

        return implode("\n", $html);
    }


    private function _renderFrontendNavigation($data, &$html, $level)
    {
        $spacer   = str_repeat(' ', 4);
        $spacerUL = str_repeat(' ', ($level * 4) );
        $spacerLI = $spacerUL . $spacer;

        if ( $level == 1 ) {
            $html[] = $spacerLI . '<input type="checkbox" id="sidebar-active" />';
            $html[] = $spacerLI . '<label for="sidebar-active" id="btn-sidebar-open"><span id="icon-sidebar-open" class="icons"></span></label>';
            $html[] = $spacerLI . '<label for="sidebar-active" id="sidebar-overlay"></label>';
            $html[] = $spacerLI . '<div class="links-container">';
            $html[] = $spacerLI . '    <label for="sidebar-active" id="btn-sidebar-close"><span id="icon-sidebar-close" class="icons"></span></label>';
        }

        $html[] = $spacerUL . '<ul>';

        foreach( $data AS $navItem ) {
            $subItems = ( array_key_exists('sub', $navItem) AND is_array($navItem['sub']) );

            $html[] = $spacerLI . '<li' . ($navItem['home'] ? ' id="home-link"' : '') . ($subItems ? ' class="sub-items"' : '' ) . '>';

            if ( !strlen($navItem['url']) ) {
                $html[] = $spacerLI . $spacer . '<span>' . $navItem['title'] . '</span>';
            }
            else {
                if ( strpos($navItem['url'], $this -> registry -> baseurl) === false ) {
                    $external = ' target="_blank"';
                }
                else {
                    $external = '';
                }

                $html[] = $spacerLI . $spacer . '<a href="' . $navItem['url'] . '"' . $external . '>' . $navItem['title'] . '</a>';
            }

            if ( $subItems ) {
                $html[] = $spacerLI . $spacer . '<input type="checkbox" id="' . $navItem['id'] . '" />';
                $html[] = $spacerLI . $spacer . '<label for="' . $navItem['id'] . '"><span class="icons"></span></label>';
                $this -> _renderFrontendNavigation($navItem['sub'], $html, $level + 2);
            }

            $html[] = $spacerLI . '</li>';
        }

        $html[] = $spacerUL . '</ul>';

        if ( $level == 1 ) {
            $html[] = $spacerLI . '</div>';
        }
    }

    private function _getNavigationData()
    {
        $query = 'SELECT `navigation`.*, `pages`.`seo_code` FROM `navigation` ' .
                     'LEFT JOIN `pages` ON (`navigation`.`item_cms` = `pages`.`page_id` AND `navigation`.`item_type` = 0) ' .
                 'WHERE `navigation`.`item_enable` = 1;';
        $data  = $this -> registry -> db -> queryObjectArray($query);

        if ( is_array($data) AND count($data[0]) ) {
            return $this -> _moveResultToMultiLevelArray($data);
        }
        else {
            return false;
        }
    }

    private function _moveResultToMultiLevelArray($data)
    {
        $return = array();

        foreach($data AS $navItem) {
            $isHome = ( ($navItem['item_home'] === 1) ? true : false );

            switch ($navItem['item_type']) {
                case 0 : $link =  $this -> registry -> baseurl . $navItem['seo_code'];
                         break;
                case 1 : $link = '';
                         break;
                default: $link = $navItem['item_url'];
                         break;
            }

            $insert = array(
                          'title' => $navItem['item_title'],
                          'id'    => 'sub-nav-' . $navItem['item_id'],
                          'home'  => $isHome,
                          'url'   => $link,
                      );

            if ( strlen($navItem['item_parent']) ) {
                if ( !array_key_exists($navItem['item_parent'], $return) ) {
                    $return[$navItem['item_parent']]['sub'] = array();
                }

                $return[$navItem['item_parent']]['sub'][$navItem['item_element']] = $insert;
            }
            else {
                $return[$navItem['item_element']] = $insert;
            }
        }

        return $return;
    }

    private function _loadNavigationFromDataBase()
    {
        $this -> currNavigationData = $this -> registry -> db -> queryObjectArray('SELECT * FROM `navigation` ORDER BY `nav_id` ASC;');
    }

    private function _renderDefaultButton()
    {
        // Default-Button
        $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'default_button.htm');
        return $this -> renderer -> renderTemplate();
    }

    private function _createJsTemplateForNewItems()
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'item.htm');
            $this -> renderer -> setVariable('item_element'   , '{{item_element}}');
            $this -> renderer -> setVariable('item_home'      , '{{item_home}}');
            $this -> renderer -> setVariable('item_class'     , '{{item_class}}');
            $this -> renderer -> setVariable('item_title'     , '{{item_title}}');
            $this -> renderer -> setVariable('item_id'        , '{{item_id}}');
            $this -> renderer -> setVariable('item_pos'       , '{{item_pos}}');
            $this -> renderer -> setVariable('item_parent'    , '{{item_parent}}');
            $this -> renderer -> setVariable('item_enable'    , '{{item_enable}}');
            $this -> renderer -> setVariable('item_type'      , '{{item_type}}');
            $this -> renderer -> setVariable('item_cms_id'    , '{{item_cms}}');
            $this -> renderer -> setVariable('item_url'       , '{{item_url}}');
            $this -> renderer -> setVariable('item_decription', '{{item_decription}}');
        return $this -> renderer -> renderTemplate();
    }

    private function _renderCurrentNavigation()
    {
        $elements = array();

        // Render Navigation
        foreach ($this -> currNavigationData AS $navElement) {
            if ( $navElement['item_enable'] != true ) {
                $navElement['item_class'] .= ' item-disable';
            }

            $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'item.htm');
                $this -> renderer -> setVariable('item_element'   , $navElement['item_element']);
                $this -> renderer -> setVariable('item_home'      , ( ($navElement['item_home'] == true) ? 'true' : 'false' ) );
                $this -> renderer -> setVariable('item_class'     , $navElement['item_class']);
                $this -> renderer -> setVariable('item_title'     , $navElement['item_title']);
                $this -> renderer -> setVariable('item_id'        , $navElement['item_id']);
                $this -> renderer -> setVariable('item_pos'       , $navElement['item_pos']);
                $this -> renderer -> setVariable('item_parent'    , $navElement['item_parent']);
                $this -> renderer -> setVariable('item_enable'    , ( ($navElement['item_enable'] == true) ? 'true' : 'false' ) );
                $this -> renderer -> setVariable('item_type'      , $navElement['item_type']);
                $this -> renderer -> setVariable('item_cms_id'    , $navElement['item_cms']);
                $this -> renderer -> setVariable('item_url'       , $navElement['item_url']);
                $this -> renderer -> setVariable('item_decription', ( ($navElement['item_type'] == 0) ? $this -> currCmsArray[$navElement['item_cms']] : $navElement['item_url'] )  );
            $elements[] = $this -> renderer -> renderTemplate();
        }

        $this -> currNavigationHTML = implode("\n", $elements);
    }

    private function _loadCmsFromDatabase()
    {
        $cmsPages = $this -> registry -> db -> queryObjectArray('SELECT `page_id`, `page_title`, `is_home`, `page_enable` FROM `pages` ORDER BY `page_id` ASC;');
        $tmpHtml   = array();
        $tmpHtml[] = '<option value="-1">' . $this -> registry -> user_lang['global']['option_actions_select'] . '</option>';

        $this -> currCmsArray[-1] = '';

        if ( is_array($cmsPages) AND count($cmsPages) ) {
            foreach($cmsPages AS $cmsPage) {
                $this -> currCmsArray[$cmsPage['page_id']] = $cmsPage['page_title'];
                $tmpHtml[] = '<option class="' . ($cmsPage['is_home'] ? 'cms-home ' : '') . ($cmsPage['page_enable'] ? '' : 'cms-disable') . '" value="' . $cmsPage['page_id'] . '">' . $cmsPage['page_title'] . '</option>';
            }
        }

        $this -> currCmsPages = implode("\n", $tmpHtml);
    }
}