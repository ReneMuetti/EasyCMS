<?php
class Navigation
{
    private $registry;
    private $renderer;

    private $currNavigationData = null;
    private $currNavigationHTML = '';
    private $currCmsPages       = '';

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

    public function editCurrentNavigation()
    {
        $this -> currNavigationData = null;
        $this -> currNavigationHTML = '';
        $this -> currCmsPages       = '';

        $this -> _loadNavigationFromDataBase();
        $this -> _loadCmsFromDatabase();

        if ( is_array($this -> currNavigationData) AND count($this -> currNavigationData) ) {
            $this -> _renderCurrentNavigation();
            $currNavEmpty = 'false';
        }
        else {
            $this -> _renderDefaultButton();
            $currNavEmpty = 'true';
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'admin.htm');
            $this -> renderer -> setVariable('cms_navigation'   , $this -> currNavigationHTML);
            $this -> renderer -> setVariable('current_cms_pages', $this -> currCmsPages);
            $this -> renderer -> setVariable('current_nav_empty', $currNavEmpty);
        return $this -> renderer -> renderTemplate();
    }

    public function saveNavigationItem($navId, $navParentId, $navTitle, $navEnable, $navType, $navCms, $navUrl, $navPosition)
    {
        $sqlData = array();
        $result  = array();

        $sqlData = array(
                       'title'    => $navTitle,
                       'parent'   => $navParentId,
                       'type'     => $navType,
                       'cms_page' => $navCms,
                       'link'     => $navUrl,
                       'enable'   => $navEnable,
                       'position' => $navPosition,
                   );

        if ( $navId === 0 ) {
            $result = $this -> registry -> db -> insertRow($sqlData, 'navigation');
        }
        else {
            $result = $this -> registry -> db -> updateRow($sqlData, 'navigation', 'WHERE `nav_id` = ' . $navId);
        }

        return array(
                   'error'   => false,
                   'message' => '',
                   'data'    => $result,
               );
    }





    private function _loadNavigationFromDataBase()
    {
        $this -> currNavigationData = $this -> registry -> db -> queryObjectArray('SELECT * FROM `navigation` ORDER BY `nav_id` ASC;');
    }

    private function _renderDefaultButton()
    {
        // Default-Button
        $this -> renderer -> loadTemplate('admin' . DS . 'navigation' . DS . 'default_button.htm');
        $this -> currNavigationHTML = $this -> renderer -> renderTemplate();
    }

    private function _renderCurrentNavigation()
    {
        // Render Navigation
    }

    private function _loadCmsFromDatabase()
    {
        //$cmsPages = $this -> registry -> db -> queryObjectArray('SELECT * FROM `cms_page` ORDER BY `page_id` ASC;');
        $tmpHtml   = array();
        $tmpHtml[] = '<option value="-1">' . $this -> registry -> user_lang['global']['option_actions_select'] . '</option>';

        //if ( is_array($cmsPages) AND count($cmsPages) ) {
            //
        //}

        $this -> currCmsPages = implode("\n", $tmpHtml);
    }
}