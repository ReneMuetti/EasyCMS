<?php
class CssLoader
{
    private $registry = null;

    private $cssData     = array();
    private $defaultPath = '';
    private $customPath  = '';
    private $faLocalPath = '';

    private $doMinimize = true;

    /**
     * Class Constructor
     *
     * @access    public
     */
    public function __construct()
    {
    	global $website;

        $this -> registry = $website;

        $this -> defaultPath = $this -> registry -> config['Misc']['path'] . DS .
                               $this -> registry -> config['Misc']['design_directory'] . DS .
                               'default';

        if ( strlen($this -> registry -> config['design/theme/skin']) ) {
            $_tmp_path = $this -> registry -> config['Misc']['path'] . DS .
                         $this -> registry -> config['Misc']['design_directory'] . DS .
                         $this -> registry -> config['design/theme/skin'];

            if ( is_dir($_tmp_path) ) {
                $this -> customPath = $_tmp_path;
            }
        }

        $this -> faLocalPath = $this -> registry -> config['Misc']['skin_directory'] . DS .
                               'font-awesome' . DS;

        $this -> _setDefaultCssFiles();
        $this -> _loadCssFilesFromPath($this -> defaultPath);
        $this -> _insertFontAwesome();
    }

    /**
     * Class Finished
     *
     * @access    public
     */
    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> cssData);
    }

    public function setMinimize($mode)
    {
        $this -> doMinimize = $mode;
    }

    /**
     * Default-Design rendern
     *
     * @access    public
     */
    public function renderDefaultStyle()
    {
        $cssContent = implode("\n", array_filter($this->cssData));
        $cssContent = $this -> _minimizeCode($cssContent);

        return $cssContent;
    }

    /**
     * Skin-Design rendern (Anpassungen)
     *
     * @access    public
     */
    public function render()
    {
        // 1. Spezifische Skin-Datei suchen (z.B. "<<skinname>>.css")
        $this -> _insertCustomSkinCssFile();

        // 2. Standard-Dateien aus dem Skin-Pfad laden (Überschreibungen)
        $this -> _loadCssFilesFromPath($this -> customPath);

        // 3. Zusammenfügen und Minimieren
        $cssContent = implode("\n", array_filter($this->cssData));
        $cssContent = $this -> _minimizeCode($cssContent);

        return $cssContent;
    }


    /**
     * Ergänzen einer Custom-CSS-Datei, welche den Namen des Skins trägt
     *
     * @access    private
     */
    private function _insertCustomSkinCssFile()
    {
        $_skinFileName = $this->registry->config['design/theme/skin'] . '.css';
        $_definedFile  = $this -> customPath . DS . $_skinFileName;

        if ( is_file($_definedFile) ) {
            // Reserviert den vorletzten Platz (vor responsive.css)
            $_newFile = array( $_skinFileName => '' );

            $this->cssData = array_slice($this->cssData, 0, -1, true) +
                             $_newFile +
                             array_slice($this->cssData, -1, null, true);
        }
    }

    /**
     * CSS-Files in Array eintragen
     *
     * @access    private
     */
    private function _setDefaultCssFiles()
    {
        $this -> cssData = array(
                               'var.css'               => '',
                               'fonts.css'             => '',
                               'page.css'              => '',
                               'page_grid.css'         => '',
                               'navigation.css'        => '',
                               'tabs.css'              => '',
                               'form.css'              => '',
                               'gallery.css'           => '',
                               'messages.css'          => '',
                               'intro.css'             => '',
                               '404.css'               => '',
                               'under_costruction.css' => '',
                               'responsive.css'        => '',
                           );
    }

    /**
     * FontAwesome CSS-Dateien am Anfang des Arrays einfügen
     *
     * @access    private
     */
    private function _insertFontAwesome()
    {
        $_faDir = $this -> faLocalPath . 'css' . DS;

        $_faFixedPath = '"../webfonts/';
        $_faLocalPath = '"/' . $this -> faLocalPath . 'webfonts' . DS;

        // Reihenfolge der Dateien definieren, welche FA empfiehlt
        $_faFiles = array(
            'fontawesome.css' => '',
            'brands.css'      => '',
            'solid.css'       => ''
        );

        $_faData = array();
        foreach ($_faFiles as $fileName => $content) {
            $_fullPath = $_faDir . $fileName;

            if (is_file($_fullPath)) {
                $_content = trim(file_get_contents($_fullPath));

                // generischen Pfad in FA-Content durch Skin-Path ersetzen
                // Erhaltung der Update-Fähigkeit
                if ( strpos($_content, $_faFixedPath) !== false ) {
                    $_content = str_replace($_faFixedPath, $_faLocalPath, $_content);
                }

                $_faData[$fileName] = $_content;
            }
        }

        // Die FA-Daten mit den bestehenden Daten zusammen
        // Durch die Addition (+) werden die FA-Daten VORNE angefügt
        $this->cssData = $_faData + $this->cssData;
    }

    /**
     * Inhalt der CSS-Files in Array einfügen
     *
     * @param     string     Pfadangabe
     * @access    private
     */
    private function _loadCssFilesFromPath($path)
    {
        foreach( $this -> cssData AS $cssFile => $cssContent ) {
            $_fullPath = $path . DS . $cssFile;

            if ( is_file( $_fullPath ) ) {
                $_content = trim(file_get_contents($_fullPath));

                if ( $this -> _isCssFileContentIsSafe($_content) ) {
                    $this -> cssData[$cssFile] = $_content;
                }
            }
        }
    }

    /**
     * Inhalt der Datei wird auf Gültigkeit überprüft
     * Fragwürdige Inhalte werden komplett verworfen
     *
     * @param     string     Inhalt einer CSS-Datei
     * @return    bool
     * @access    private
     */
    private function _isCssFileContentIsSafe($content)
    {
        // Die "No-Go" Liste für CSS-Dateien
        $forbidden = array(
                         '<script'   , 'on\w+\s*=', 'eval\s*\(', 'atob\s*\(',
                         'document\.', 'window\.' , 'String.fromCharCode',
                         '<?php'
                     );

        foreach ($forbidden as $token) {
            if (preg_match('/' . $token . '/i', $content)) {
                return false;
            }
        }

        // Check auf Hex-Verschlüsselung (\x61...)
        if (preg_match('/(\\\x[0-9a-fA-F]{2}){5,}/', $content)) {
            return false;
        }

        return true;
    }

    /**
     * Inhalt der Datei wird minimieren
     *
     * @param     string     Inhalt einer CSS-Datei
     * @return    string     minimierter Code
     * @access    private
     */
    private function _minimizeCode($cssCode = '')
    {
        if ( !$this -> doMinimize ) {
            return $cssCode;
        }

        $search = array(
                      '/\/\*.*?\*\//s',     // Kommentare weg
                      '/\s*([,;{}:])\s*/',  // Leerzeichen um Syntax weg
                      '/^\s+|\s+$/m',       // Zeilen-Trim
                      '/\s{2,}/'            // Doppelte Leerzeichen weg
                  );
        $replace = array('$1', '$1', '', ' ');

        return preg_replace($search, $replace, $cssCode);
    }
}