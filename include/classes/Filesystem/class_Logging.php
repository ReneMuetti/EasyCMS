<?php
/**
 * automatisches Loggen von Inhalten und Meldungen
 */
class Logging
{
    private $basePath = '';
    private $logFile  = '';
    private $length   = 100;
    private $nl       = "\n";

    private $logFileFull = '';

    /**
     * Klassen-Konstruktor
     *
     * @access   public
     * @param    $logType      string     Name der Log-Datei ohne Endung
     * @param    $logMessage   string     Inhalt der Log-Meldung
     */
    public function __construct($logType = 'error', $logMessage = '', $initOnly = false)
    {
        global $website;

        $this -> basePath    = $website -> config['Misc']['path'] . DIRECTORY_SEPARATOR . $website -> config['Misc']['log_path'] . DIRECTORY_SEPARATOR;
        $this -> logFile     = trim($logType) . '.log';
        $this -> logFileFull = $this -> basePath . $this -> logFile;

        if ( $initOnly === false ) {
            $this -> logMessage($logMessage);
        }
    }

    /**
     * Log-Message schreiben
     *
     * @access   public
     * @param    $logMessage   string     Inhalt der Log-Meldung
     */
    public function logMessage($logMessage = '')
    {
        if ( !empty($logMessage) AND strlen($logMessage) )
        {
            $logMessage = $this -> _getPre() . $logMessage . $this -> _getPost();

            file_put_contents($this -> logFileFull, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Daten aus der Announce-Unpack einlesen und als Array zurückgeben
     *
     * @access   public
     * @return   arrey|string     Inhalt der Log-Datei - oder Meldung
     */
    public function getSortArrayDataFromAnnmounceUnpack()
    {
        if ( is_file($this -> logFileFull) ) {
            $count = 0;
            $data  = array();
            $key   = '';
            $sha   = '';

            $handle = fopen($this -> logFileFull, 'r');
            if ( $handle ) {
                while (($line = fgets($handle)) !== false) {
                    if ( strpos($line, 'Passkey') !== false ) {
                        $tmp = explode(' ', $line);
                        $key = trim($tmp[1]);

                        if ( !array_key_exists($key, $data) ) {
                            $data[$key] = array();
                        }
                        $count++;
                    }

                    if ( strlen($key) ) {
                        if ( strpos($line, '[1]') !== false ) {
                            $tmp = explode(' ',  trim($line));
                            $sha = trim($tmp[2]);

                            if ( !array_key_exists($sha, $data[$key]) ) {
                                $data[$key][$sha] = 1;
                            }
                            else {
                                $data[$key][$sha] += 1;
                            }

                            $key = '';
                            $sha = '';
                        }
                    }
                }
                fclose($handle);

                return $data;
            }
            else {
                return 'LOG-File kann nicht geöffnet werden!';
            }
        }
        else {
            return 'LOG-File nicht vorhanden!';
        }
    }


    /**
     * Zeile mit Dateum und Script erzeugen
     *
     * @access   private
     */
    private function _getPre()
    {
        return date('d.m.Y H:i:s') . ' :: ' . THIS_SCRIPT . ' :' . $this -> nl;
    }

    /**
     * Trenn-Zeile erstellen
     *
     * @access   private
     */
    private function _getPost()
    {
        return $this -> nl . $this -> _getTrenner() . $this -> nl;
    }

    private function _getTrenner()
    {
        return str_repeat('-', $this -> length);
    }
}
?>