<?php
class Dashboard
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

    public function createUserWelcomeBlock()
    {
        $formater  = new DateTimeFormater();
        $lastLogin = $formater -> convertDateTime($this -> registry -> userinfo['last_login'], "LONG");

        $this -> renderer -> loadTemplate('admin' . DS . 'dashboard' . DS . 'userblock.htm');
            $this -> renderer -> setVariable('user_color'     , $this -> _generateColorFromUserEmail() );
            $this -> renderer -> setVariable('user_initial'   , $this -> _getInitialsFromUser() );
            $this -> renderer -> setVariable('user_greeting'  , $this -> _getGreetingMessage() );
            $this -> renderer -> setVariable('user_name'      , $this -> registry -> userinfo['username'] );
            $this -> renderer -> setVariable('user_last_login', $lastLogin );
            $this -> renderer -> setVariable('user_cur_date'  , date('d.m.Y', TIMENOW) );
            $this -> renderer -> setVariable('user_init_clock', date('H:i:s', TIMENOW) );
        return $this -> renderer -> renderTemplate();
    }

    public function createCmsBlockSection()
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'dashboard' . DS . 'cmsblock.htm');
            $this -> renderer -> setVariable('count_cms_blocks'        , $this -> registry -> db -> tableCount('blocks') );
            $this -> renderer -> setVariable('count_cms_blocks_enable' , $this -> registry -> db -> tableCount('blocks', 'WHERE `block_enable` = 1') );
            $this -> renderer -> setVariable('count_cms_blocks_disable', $this -> registry -> db -> tableCount('blocks', 'WHERE `block_enable` = 0') );
        return $this -> renderer -> renderTemplate();
    }

    public function createCmsPageSection()
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'dashboard' . DS . 'cmspage.htm');
            $this -> renderer -> setVariable('count_cms_page'        , $this -> registry -> db -> tableCount('pages') );
            $this -> renderer -> setVariable('count_cms_page_enable' , $this -> registry -> db -> tableCount('pages', 'WHERE `page_enable` = 1') );
            $this -> renderer -> setVariable('count_cms_page_disable', $this -> registry -> db -> tableCount('pages', 'WHERE `page_enable` = 0') );
        return $this -> renderer -> renderTemplate();
    }

    public function createGallerySection()
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'dashboard' . DS . 'gallery.htm');
            $this -> renderer -> setVariable('count_cms_gallery'        , $this -> registry -> db -> tableCount('gallery') );
            $this -> renderer -> setVariable('count_cms_gallery_enable' , $this -> registry -> db -> tableCount('gallery', 'WHERE `gallery_enable` = 1') );
            $this -> renderer -> setVariable('count_cms_gallery_disable', $this -> registry -> db -> tableCount('gallery', 'WHERE `gallery_enable` = 0') );
        return $this -> renderer -> renderTemplate();
    }




    private function _generateColorFromUserEmail()
    {
        $hash = md5($this -> registry -> userinfo['email']);

        return "#" . substr($hash, 0, 6);
    }

    private function _getInitialsFromUser($useMail = false)
    {
        if ( $useMail == true ) {
            $parts = explode('@', $this -> registry -> userinfo['email']);

            return strtoupper($parts[0][0]) . strtoupper($parts[1][0]);
        }
        else {
            $name = $this -> registry -> userinfo['username'];

            return strtoupper($name[0]) . strtoupper($name[1]);
        }
    }

    private function _getGreetingMessage()
    {
        $hour = date("H");

        switch(true)
        {
            case ( $hour >= 5  AND $hour < 11 ): return $this -> registry -> user_lang['admin']['dashboard_greeting_morning'];
            case ( $hour >= 11 AND $hour < 18 ): return $this -> registry -> user_lang['admin']['dashboard_greeting_day'];
            case ( $hour >= 18 AND $hour < 21 ): return $this -> registry -> user_lang['admin']['dashboard_greeting_evening'];
            default: return $this -> registry -> user_lang['admin']['dashboard_greeting_night'];
        }
    }
}