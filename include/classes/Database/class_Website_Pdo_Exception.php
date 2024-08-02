<?php
class Website_Pdo_Exception
{
    private $charset = null;
    private $baseurl = null;
    
    public function __construct()
    {
        global $website;
        
        $this -> charset = $website -> config['Misc']['charset'];
        $this -> baseurl = $website -> config['Misc']['baseurl'];
    }
    
    public function __destruct()
    {
        unset($this -> charset);
        unset($this -> baseurl);
    }
    
    /**
     * vollständige Fehlerseite erzeugen
     *
     * @access    public
     * @param     $title       string
     * @param     $headline    string
     * @param     $message     string
     * @return    string
     */
    public function printErrorPage($title, $headline, $message)
    {
        $content = $this -> printErrorBlock($headline, $message);
        
        return "<!DOCTYPE html>\n" .
               " <html xmlns=\"http://www.w3.org/1999/xhtml\">\n" .
               "  <head>\n" .
               "    <title>" . $title . "</title>\n" .
               "    <meta http-equiv=\"content-type\" content=\"text/html; charset=" . $this -> charset . "\" />\n" .
               "    <link rel=\"stylesheet\" type=\"text/css\" href=\"" . $this -> baseurl . "/skin/css/error.css\" />\n" .
               "    <base href=\"" . $this -> baseurl . "\" />\n" .
               "  </head>\n" .
               "  <body>\n" .
               $content .
               "  </body>\n" .
               "</html>";
    }
    
    /**
     * einen Fehler-Block erzeugen
     *
     * @access    public
     * @param     $headline    string
     * @param     $message     string
     * @return    string
     */
    public function printErrorBlock($headline, $message)
    {
        return "    <div class=\"error pdo-exception\">\n" .
               "        <h1>" . $headline . "</h1>\n" .
               "        <pre class=\"db-error\">" . $message . "</pre>\n" .
               "    </div>";
    }
}