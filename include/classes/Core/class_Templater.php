<?php
class Templater
{
    /**
     * instance
     *
     * Statische Variable, um die aktuelle (einzige!) Instanz dieser Klasse zu halten
     *
     * @var Singleton
     */
    protected static $_instance = null;

    private $registry = null;
    private $navRender = null;

    private $vars = array();

    private $template = null;
    private $templatecache = array();

    private $debugOut = array();

    /**
    * get instance
    *
    * Falls die einzige Instanz noch nicht existiert, erstelle sie
    * Gebe die einzige Instanz dann zurück
    *
    * @return   Singleton
    */
    public static function getInstance()
    {
        if ( self::$_instance === null )
        {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * clone
     *
     * Kopieren der Instanz von aussen ebenfalls verbieten
     */
    protected function __clone() {}

    /**
     * Class Constructor
     * externe Instanzierung verbieten
     *
     * @access    public
     */
    protected function __construct()
    {
    	global $website;

        $this -> registry  = $website;

        $this -> _fetchLocalVars();
        $this -> _fetchLanguage();
    }

    /**
     * Class Finished
     *
     * @access    public
     */
    public function __destruct()
    {
        unset($this -> template);
        unset($this -> vars);
        unset($this -> registry);
        unset($this -> templatecache);
    }

    /**
     * Add Variable for Rendering
     *
     * @access    public
     * @param     string         Key for Creation
     * @param     string         Value for Key
     */
    public function setVariable($key = null, $value = null)
    {
        if ( strlen($key) ) {
            $this -> vars['<var ' . $key . ' />'] = $value;
        }
    }

    /**
     * append Data for Rendering
     *
     * @access    public
     * @param     string         Key for Creation
     * @param     string         Value for Key
     */
    public function addContent($key = null, $value = null)
    {
        if ( strlen($key) ) {
            $this -> vars['<var ' . $key . ' />'] .= "\n" . $value . "\n";
        }
    }

    /**
     * CSS-Dateien an den Header anhängen
     *
     * @access    public
     * @param     array         2-Data-Array with Data
     * @param     string        ID from Page to be load
     */
    public function addCustonStyle($entry, $pageKey)
    {
        if ( is_array($entry) AND isset($entry['script']) ) {
            $this -> _addCssItem($entry, $pageKey);
        }
    }

    public function appendToFooter($newContent)
    {
        if ( strlen($newContent) ) {
            $new = $this -> vars['<var global_footer />'] .
                   ( strlen($this -> vars['<var global_footer />']) ? "\n" : '') .
                   $newContent;

            $this -> setVariable('global_footer', $new);
        }
    }

    public function addJavascriptToHeader($newScript, $currentScript)
    {
        if ( strlen($newScript) ) {
            $this -> _addJsItem(array('script' => $newScript), $currentScript);
        }
    }


    /**
     * set new Language-Data
     *
     * @access    public
     */
    public function updateLanguage()
    {
        $this -> _fetchLanguage();
    }

    /**
     * loading Template
     *
     * @access    public
     * @param     string         Name from Template
     * @param     bool           Save Content in Variable or direct return
     * @return    none|string
     */
    public function loadTemplate($templateName = '', $saveForRendering = true)
    {
        $_templateString = '';

        if ( array_key_exists('skin', $this -> registry -> user_config) ) {
            $full_base_path = $this -> registry -> config['Misc']['path'] . $this -> registry -> config['Templates'] . '/' . $this -> registry -> user_config['skin'] . '/' . $templateName;
        }
        else {
            $full_base_path = null;
        }

        $full_default_path = $this -> registry -> config['Misc']['path'] . $this -> registry -> config['Templates'] . '/default/' . $templateName;

        if ( array_key_exists($templateName, $this -> templatecache) ) {
            // Load from Templater-Cache
            $_templateString = $this -> templatecache[$templateName];
        }
        elseif ( array_key_exists($templateName, $this -> registry -> templatecache) ) {
            // TODO :: Load from Registry-Cache
        }
        elseif ( !is_null($full_base_path) AND is_file($full_base_path) ) {
            // File not found => search in "UserSkin"
            $_templateString = file_get_contents($full_base_path);
        }
        elseif ( !array_key_exists('skin', $this -> registry -> user_config) OR $this -> registry -> user_config['skin'] == 'default' ) {
            // File not found => search in "Default"
            $_templateString = file_get_contents($full_default_path);
        }
        else {
            throw new Exception( __CLASS__ . '::' . __FUNCTION__ . ' : Datei <' . $templateName . '> wurde nicht gefunden!' );
        }

        if ( !array_key_exists($templateName, $this -> templatecache) ) {
            // Cacheing
            $this -> templatecache[$templateName] = $_templateString;
        }

        if ($saveForRendering) {
            $this -> setTemplateString( $_templateString );
        }
        else {
            return $_templateString;
        }
    }

    /**
     * neuen Template-String setzen
     *
     * @access    public
     * @param     string         HTML-Template
     */
    public function setTemplateString($string = null)
    {
        if ( strlen($string) ) {
            $_search  = array();
            $_replace = array();

            $this -> template = str_replace($_search, $_replace, $string);
        }
    }

    /**
     * Replate all Variable-Keys in Template
     *
     * @access    public
     * @return    string
     */
    public function renderTemplate()
    {
        if ( is_array($this -> vars) AND count($this -> vars) ) {
            return str_replace(
                       array_keys  ($this -> vars),
                       array_values($this -> vars),
                       $this -> template
                   );
        }
        else {
            return $this -> template;
        }
    }

    /**
     * Append Debug-Output to MainPage
     *
     * @access    public
     */
    public function renderDebugOutput()
    {
        if( count($this -> debugOut) ) {
            foreach( $this -> debugOut AS $class ) {
                $classListing = print_r($class::getInstance(), true);
                $this -> vars['<var before_finished />'] .= $this -> _addDebugOutput( $classListing );
            }
        }
    }

    /**
     * Check if a User-Option is set
     *
     * @access    public
     * @param     string|bool    Section in Registry; If FALSE then if Key a concrete Value
     * @param     string         Key in Registry
     * @return    string
     */
    public function getCheckboxState($section = null, $key = null)
    {
        if ( is_null($section) OR is_null($key) ) {
            return 'Section-Key-ERROR!';
        }
        else {
            if ( is_string($section) ) {
                if ( isset($this -> registry -> $section) ) {
                    $tmp = $this -> registry -> $section;
                    if ( isset($tmp[$key]) ) {
                        if ( $tmp[$key] == true ) {
                            return ' checked="checked"';
                        }
                        else {
                            return '';
                        }
                    }
                    else {
                        return 'Key not exists!';
                    }
                }
                else {
                    return 'Section not exists!';
                }
            }
            else {
                if ( is_null($key) ) {
                    return 'Key not set!';
                }
                else {
                    if ( $key == true ) {
                        return ' checked="checked"';
                    }
                    else {
                        return '';
                    }
                }
            }
        }
    }

    /*************************************************************************************/
    /********************************  Private Functions  ********************************/
    /*************************************************************************************/


    /**
     * rendering Debug-Output
     *
     * @access    private
     * @param     string         Content from Variable
     * @return    string
     */
    private function _addDebugOutput($debugVar = null)
    {
        $debugVar = "\n" . htmlentities(trim($debugVar), ENT_QUOTES | ENT_XHTML | ENT_IGNORE, "UTF-8") . "\n";

        return str_replace(
                   '<var debug_out />',
                   str_replace('=&amp;gt;', '=&gt;', $debugVar),
                   $this -> loadTemplate('debug.htm', '', false)
               );
    }

    /**
     * add local Vars for Rendering
     *
     * @access    private
     */
    private function _fetchLocalVars()
    {
        $this -> vars['<var header_js />']             = '';
        $this -> vars['<var header_css />']            = '';
        $this -> vars['<var global_header />']         = '';
        $this -> vars['<var global_jquery_version />'] = '';
        $this -> vars['<var global_navbar />']         = '';
        $this -> vars['<var global_footer />']         = '';
        $this -> vars['<var before_finished />']       = '';
        $this -> vars['<var debug_out />']             = '';

        $this -> vars['<var ' . THIS_SCRIPT . '_content />'] = '';

        $this -> vars['<var_config host />']         = $this -> registry -> config['Host']['host'];
        $this -> vars['<var_config protocol />']     = $this -> registry -> config['Host']['protocol'];
        $this -> vars['<var_config script />']       = $this -> registry -> config['Host']['script'];
        $this -> vars['<var_config baseurl />']      = $this -> registry -> config['Misc']['baseurl'];

        $this -> vars['<var global_jquery_version />'] = $this -> registry -> config['Misc']['jquery_version'];

        $this -> vars['<var cms_version />'] = $this -> registry -> config['Cms']['version'];

        if ( is_array($this -> registry -> user_config) ) {
            foreach( $this -> registry -> user_config AS $key => $value ) {
                $this -> vars['<var_user_config ' . $key . ' />'] = $value;
            }
        }
    }

    /**
     * add Language for Rendering
     *
     * @access    private
     */
    private function _fetchLanguage()
    {
        foreach( $this -> registry -> user_lang AS $section => $data ) {
            if ( is_array($data) ) {
                foreach( $data AS $key => $value ) {
                    $this -> vars['<lang ' . $section . '_' . $key . ' />'] = output_string($value);
                }
            }
            else {
                $this -> vars['<lang ' . $section . ' />'] = output_string($data);
            }
        }
    }

    private function _regirsterDebugClassForOutput($debugClass)
    {
        if ( isset($debugClass['class']) ) {
             if( is_array($debugClass['class']) ) {
                foreach( $debugClass['class'] AS $id => $class ) {
                    $this -> debugOut[] = $class;
                }
            }
            else {
                $this -> debugOut[] = $debugClass['class'];
            }
        }
    }

    /**
     * Alle Layout-Aktionen auf einer Seite ausführen
     *
     * @access    private
     * @param     string       Modul-Name
     * @param     array        Aktionen, welche aufgerufen werden sollen
     */
    private function _getAllPageActions($module, $entry)
    {
        if ( is_array($entry) AND count($entry) ) {
            $_actions = $entry[THIS_SCRIPT];
            if( !empty($_actions) AND count($_actions) ) {
                if ( !empty($_actions['class']) AND !empty($_actions['action']) ) {
                    $this -> _runAction($_actions['class'], $_actions['action'], $_actions['block']);
                }
                else {
                    foreach( $_actions AS $key => $_action ) {
                        $this -> _runAction($_action['class'], $_action['action'], $_action['block']);
                    }
                }
            }
        }
    }

    /**
     * Aufrufen einer konfigurierten Klasse
     *
     * @access    private
     * @param     string       Klassen-Name
     * @param     string       Aktion
     * @param     string       Block, welcher geladen werden soll
     */
    private function _runAction($class = null, $action = null, $block = null)
    {
        $_blockAction = new $class($this, $action, $block);
        $_blockAction -> $action();
    }


    /**
     * Einlesen aller Templates
     *
     * @access    private
     * @param     string       Module-Name
     * @param     string       Block-Identifirer
     */
    private function _fetchTemplate($module, $entry)
    {
        $tiles = explode('_', $entry['block_type']);

        if ( $tiles[0] == THIS_SCRIPT ) {
            $_tplSpace = $this -> registry -> modules[$module]['templates'][THIS_SCRIPT];
            $this -> _getTemplateData($module, $_tplSpace);
        }
    }

    /**
     * Template-Datei in den Cache einlesen
     *
     * @access    private
     * @param     string       Module-Name
     * @param     string       Block-Informationen
     */
    private function _getTemplateData($module, $entry)
    {
        if ( count($entry) ) {
            if( !empty($entry['template']) ) {
                // Template cachen
                $this -> loadTemplate($entry['template'], $module);
            }
            else {
                foreach( $entry AS $key => $value ) {
                    $this -> _getTemplateData($module, $value);
                }
            }
        }
    }

    /**
     * JavaScripte in den Header laden
     *
     * @access    private
     * @param     array         2-Data-Array with Data
     * @param     string        ID from Page to be load
     */
    private function _addJsItem($entry, $pageKey)
    {
        if ( $pageKey == THIS_SCRIPT ) {

            if( strpos($entry['script'], 'jquery-') ) {
                // Core-jQuery vor allen anderen laden
                $new = '<script type="text/javascript" src="' . $this -> registry -> baseurl . $entry['script'] . '"></script>' .
                       ( strlen($this -> vars['<var header_js />']) ? "\n        " : '') .
                       $this -> vars['<var header_js />'];
            }
            else {
                $new = $this -> vars['<var header_js />'] .
                       ( strlen($this -> vars['<var header_js />']) ? "\n        " : '') .
                       '<script type="text/javascript" src="' . $this -> registry -> baseurl . $entry['script'] . '"></script>';
            }

            $this -> setVariable('header_js', $new);
        }
    }

    /**
     * CSS-Dateien in den Header laden
     *
     * @access    private
     * @param     array         2-Data-Array with Data
     * @param     string        ID from Page to be load
     */
    private function _addCssItem($entry, $pageKey)
    {
        if ( $pageKey == THIS_SCRIPT ) {
            $new = $this -> vars['<var header_css />'] .
                   ( strlen($this -> vars['<var header_css />']) ? "\n        " : '') .
                   '<link rel="stylesheet" type="text/css" href="' . $this -> registry -> baseurl . $entry['script'] . '" media="screen" />';

            $this -> setVariable('header_css', $new);
        }
    }
}
?>