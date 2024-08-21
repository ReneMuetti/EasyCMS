<?php
class Website_Pdo
{
    /**
	 * The registry object
	 *
	 * @var	Registry
	 */
	private $registry = null;

	/**
	 * Ausgabe von Fehlern
	 */
	private $errorPrint = null;

    /**
     * letzter Fehler eines Statements
     */
	private $lastError  = null;

	/**
	 * alle in der aktuellen Session abgesetzten SQL-Abfragen
	 */
	private $queryCache = array();

	/**
	 * letzte mit prepare erstellte SQL-Abfrage
	 */
	private $lastQuery = null;

	/**
	 * ID des zuletzt eingefügten Datensatzes
	 */
	private $lastInsertID = null;

	/**
	 * Connection-String für DB-Initialisierung
	 */
	private $dsn = null;

	/**
	 * Optionen für DB-Verbindung
	 */
	private $options = null;

	private $backup_path    = '';
	private $backup_prefix  = '';
	private $backup_postfix = '';

	/**
	 * DB-Host
	 */
	protected $dbType = null;

	/**
	 * DB-Host
	 */
	protected $dbHost = null;

	/**
	 * DB-Port
	 */
	protected $dbPort = null;

	/**
	 * DB-User
	 */
	protected $dbUser = null;

	/**
	 * DB-Passwort für den aktuellen User
	 */
	protected $dbPassword = null;

	/**
	 * DB-Name
	 */
	protected $dbDatabase = null;

	/**
	 * DB-Zeichensatzz für die Verbindung
	 */
	protected $dbCharset = null;

	/**
	 * Verbindungs-Opbect zur Datenbank
	 */
	protected $connection = null;

	/**
	 * PDO-Fetch-Mode
	 */
	protected $fetchMode = null;

	/**
	 * PDO-Error-Mode
	 */
	protected $errorMode = null;

	/**
	 * Zähler für Errors
	 */
	protected $errorCount = 0;

	/**
	 * Debug-Mode
	 * true: Error-Meldungen werden angezeigt
	 * false: Error-Meldungen werden nur ins LOG-File geschrieben
	 */
	protected $debugMode = false;

	/**
	 * soll jede Query in ein LOG-File geschrieben werden
	 */
	protected $writeQueryCache = false;

	/**
     * gobale Definition.
     *
     * @access protected
     * @var    string
     */
	protected $stmt = null;

    /**
     * Zähler für die SQL-Abfragen.
     *
     * @access protected
     * @var    integer
     */
    protected $sqlcounter = 0;

    /**
     * Anzahl der Zeilen innerhalb der Abfrage.
     *
     * @access protected
     * @var    integer
     */
    protected $rowcount = 0;

    /**
     * Zeitpsanne der SQL-Abfragen.
     *
     * @access protected
     * @var    integer
     */
    protected $dbtime = 0;

    /**
     * Gesamtzeit aller SQL-Abfragen.
     *
     * @access protected
     * @var    float
     */
    protected $starttime = 0;

    /**
     * Startzeit für eine einzelen SQL-Abfrage.
     *
     * @access protected
     * @var    float
     */
    protected $startTimeQuery = 0;

    /**
     * Klasse Erzeugen und Defaults anlegen
     *
     * @access public
     * @return null
     */
    public function __construct($registry = NULL)
    {
        if (is_object($registry)) {
            $this -> registry = $registry;

            $this -> dbType     = $registry -> config['Database']['dbtype'];
            $this -> dbHost     = $registry -> config['Database']['servername'];
            $this -> dbPort     = $registry -> config['Database']['port'];
            $this -> dbUser     = $registry -> config['Database']['username'];
            $this -> dbPassword = $registry -> config['Database']['password'];
            $this -> dbDatabase = $registry -> config['Database']['dbname'];
            $this -> dbCharset  = $registry -> config['Database']['charset'];

            $this -> backup_path    = $registry -> config['Database']['backup_path'];
            $this -> backup_prefix  = $registry -> config['Database']['backup_prefix'];
            $this -> backup_postfix = $registry -> config['Database']['backup_postfix'];
		}
		else {
		    $errorMessage = 'Registry is not available!';

		    new Logging('pdo_exception', $errorMessage);
		    trigger_error($errorMessage, E_USER_ERROR);
		    die();
		}

		// Error-Printer
		$this -> errorPrint = new Website_Pdo_Exception();

		// Fehlerzähler zurücksetzen
		$this -> resetErrorCount();

		$this -> starttime = $this -> microtime_float();
		$this -> _setModes();
		$this -> _connect();
    }

    /**
     * DB-Verbindung schließen und löschen
     *
     * @access    public
     */
    public function __destruct()
    {
        unset($this -> stmt);
        unset($this -> connection);
    }

    /**
     * Debug-Modus der Klasse ändern
     *
     * @access    public
     * @param     boolean      $newMode
     */
    public function setDebugMode($newMode)
    {
        $boolval = ( is_string($newMode) ? filter_var($newMode, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool)$newMode );

        $this -> errorMode =  $boolval;
    }

    /**
     * Fehlerzähler auf 0 setzen
     *
     * @access    public
     */
    public function resetErrorCount()
    {
        $this -> errorCount = 0;
    }

    /**
     * Fehlerzähler abfragen
     *
     * @access    public
     * @returrn   integer
     */
    public function getErrorCount()
    {
        return $this -> errorCount;
    }

    /**
     * Sicherstellen, das alle Transaktionen und Daten korrekt gespeichert werden
     *
     * @access    public
     */
    public function __sleep()
    {
        return array(
                   'dsn',
                   'dbUser',
                   'dbPassword',
                   'options'
               );
    }

    /**
     * @access    public
     */
    public function __toString()
    {
        // TODO
        return null;
    }

    /**
     * Wiederherstellen einer DB-Verbindung nach Warte- / Ruhemodus
     *
     * @access    public
     */
    public function __wakeup()
    {
        $this -> _setModes();

		$this -> starttime = $this -> microtime_float();
		$this -> _connect();
    }

    /**
     * Default-Modes für DB setzen
     *
     * @access    private
     */
    private function _setModes()
    {
        $this -> fetchMode = PDO::FETCH_ASSOC;

        if ( $this -> debugMode ) {
            $this -> errorMode = PDO::ERRMODE_WARNING;
        }
        else {
            $this -> errorMode = PDO::ERRMODE_EXCEPTION;
        }
    }

    /**
     * Verbindung zur Datenbank aufbauen
     *
     * @access  private
     * @return  null
     */
    private function _connect()
    {
        $this -> dsn = $this -> dbType . ':host=' . $this -> dbHost .
                       ';dbname='  . $this -> dbDatabase .
                       ';port='    . $this -> dbPort .
                       ';charset=' . $this -> dbCharset;

        $this -> options = array(
                               PDO::ATTR_EMULATE_PREPARES   => false,
                               PDO::ATTR_DEFAULT_FETCH_MODE => $this -> fetchMode,
                               PDO::ATTR_ERRMODE            => $this -> errorMode,
                           );

        try{
            $this -> connection = new PDO ($this -> dsn, $this -> dbUser, $this -> dbPassword, $this -> options);
        }
        catch ( PDOException $e ) {
            $this -> errorCount++;
            $this -> lastError = $e -> getMessage();
	        $logMessage = $this -> _generateLogMessage($e, null, true);
            new Logging('pdo_exception', $logMessage);

            if ( $this -> debugMode ) {
                echo $this -> errorPrint -> printErrorPage($this -> registry -> user_lang['database']['connection_error'],
                                                           $this -> registry -> user_lang['database']['connection_not_available'],
                                                           $logMessage);
                die();
            }
        }
    }

    /**
     * Erzeugen der Daten für eine Log-Meldung
     *
     * @access  private
     * @param   $exception    PDOException
     * @param   $result       Array
     * @param   $full         Boolean
     * @return  string
     */
    private function _generateLogMessage($exception = null, $result = null, $full = false)
    {
        $default = array();

        if ( !is_null($this -> connection) ) {
            if ($full) {
                $default = array(
                               'Error: '       . $this -> lastError,
                               'Code: '        . $exception -> getCode(),
                               'File: '        . $exception -> getFile(),
                               'Line: '        . $exception -> getLine(),
                               "Stack \n"      . $exception -> getTraceAsString(),
                               'Data: '        . print_r($this -> connection -> errorInfo(), true),
                               "\nPDO-Daten: " . $this -> _getPdoDump(),
                           );
            }
            else {
                $default = array(
                               'Query: '       . $this -> lastQuery,
                               'Result: '      . var_export($result, true),
                               'Data: '        . print_r($this -> connection -> errorInfo(), true),
                               "\nPDO-Daten: " . $this -> _getPdoDump(),
                           );
            }
        }
        else {
            $default = array(
                           'Error: '       . $this -> lastError,
                           'Code: '        . $exception -> getCode(),
                           'File: '        . $exception -> getFile(),
                           'Line: '        . $exception -> getLine(),
                           "Stack \n"      . $exception -> getTraceAsString(),
                       );
        }

        return implode("\n", $default);
    }

    /**
     * zusätzliche Verarbeitung von Strings
     *
     * @access  private
     * @param   $string     string      zu verarbeitende Zeichenkette
     * @return  null
     */
    private function _fixedStringForStatement(&$string)
    {
        if ( strlen($string) ) {
            // Quoted string for Statement
            $string = $this -> connection -> quote($string);

            // removed Double-Quotes
            $string = substr($string, 1, -1);

            // check, if string not UTF-8-Encoded
            if ( !mb_detect_encoding($string, $this -> dbCharset) ) {
                $string = mb_convert_encoding($string, $this -> dbCharset);
            }

            // FIX, damit Zeilenumbrüche korrekt übernommen werden
            $string = str_replace(
                          array("\\n", "\\t"),
                          array("\n" , "\t" ),
                          $string
                      );
        }
    }

    /**
     * Alle Daten des PDO-Statements ermitteln und als Return erfassen
     *
     * @access  private
     * @return  string
     */
    private function _getPdoDump()
    {
        ob_start();
            $this -> stmt -> debugDumpParams();
            $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Klassenzeitmessung
     *
     * @access    private
     * @return    float
     */
    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return (floatval($usec) + floatval($sec));
    }



    /**
     * Backup der Datenbank anlegen
     *
     * @access    public
     * @return    Command-Result
     */
    public function doFullBackup()
    {
        $currentTimeStamp = date('Y-m-d_H-i-s') . '_';
        $backupFullName   = $this -> backup_path . '/' . $currentTimeStamp . $this -> backup_prefix .
                            $this -> dbDatabase . $this -> backup_postfix . '.gz';

        system(
            sprintf(
                'mysqldump --add-drop-table --opt --host=%s --port=%s --user=%s --password=%s %s | gzip -9c > %s',
                $this -> dbHost,
                $this -> dbPort,
                $this -> dbUser,
                $this -> dbPassword,
                $this -> dbDatabase,
                $backupFullName),
            $result);

        return $result;
    }

    /**
     * Anzeige der Laufzeitinformationen
     *
     * @access    public
     * @return    string
     */
    public function showStatistics()
    {
        $totalTime = $this -> microtime_float() - $this -> starttime;

        return $this -> rowcount    . $this -> registry -> user_lang['database']['line']    . ( ($this -> rowcount > 1)   ? 'n' : '' ) . " / " .
               $this -> sqlcounter  . $this -> registry -> user_lang['database']['query']   . ( ($this -> sqlcounter > 1) ? 'n' : '' ) . " - " .
               round($totalTime, 4) . $this -> registry -> user_lang['database']['secound'] . " (" .
               round(($totalTime - $this -> dbtime), 4) . " " . $this -> registry -> user_lang['database']['secound'] . " PHP / " .
               round($this -> dbtime, 4)                . " " . $this -> registry -> user_lang['database']['secound'] . " SQL)";
    }

    /**
     * Vorbereitung zum Erstellen eines SQL-Statement
     *
     * @access  public
     * @param   $query   string     auszuführendes SQL-Statement
     */
    public function query($query)
    {
        if ( strlen($query) ) {
            $query = str_replace('``', '`', $query);

            $this -> startTimeQuery  = $this -> microtime_float();
            $this -> sqlcounter++;

    		$this -> stmt = $this -> connection -> prepare($query);

    		if ( !$this -> stmt ) {
    		    $logMessage = $this -> registry -> user_lang['database']['error_create_statement'] . ' ' . $this -> connection -> errorInfo()[2];
    		    new Logging('pdo_error', $logMessage);
    		}

    		$this -> queryCache[] = $this -> stmt -> queryString;
        }
	}

	/**
	 * Anbinden von Values an das bereits definierte SQL-Statement
	 *
	 * @access  public
	 * @param   $param    string      Platzhalter innerhalb des Statements
	 * @param   $value    misc        Wert für den aktuellen Schlüssel
	 * @param   $type     PDO_PARAM   PDO-Typ des Values
	 */
	public function bind($param, $value, $type = null)
	{
	    if ( is_null($type) ) {
	        switch(true) {
	            case is_int($value)   : $type = PDO::PARAM_INT;
	                                    break;
	            case is_bool($value)  : $type = PDO::PARAM_BOOL;
	                                    break;
	            case is_null($value)  : $type = PDO::PARAM_NULL;
                                        break;

                default : $type = PDO::PARAM_STR;
	        }
	    }

	    $bindResult = $this -> stmt -> bindValue($param, $value, $type);
	    if ( $bindResult == false ) {
	        $this -> errorCount++;
	        $logMessage = $this -> registry -> user_lang['database']['error_assign_statement_value'] . ' (' . $type . ')' . $param . '::' . $value;
	        new Logging('pdo_error', $logMessage);

	        if ( $this -> debugMode ) {
	            echo $this -> errorPrint -> printErrorBlock('pdo_error :: ' . $this -> registry -> user_lang['database']['error_assign_statement'], $logMessage);
	        }
	    }
	}

	/**
	 * ein Statement ausführen
	 *
	 * @access  public
	 * @return  bool       TRUE bei Erfolg; False bei Fehlern
	 * @thrown  exception  LOG-Meldung im Logfile
	 */
	public function executeQuery()
	{
	    try {
	        $this -> lastQuery = $this -> stmt -> queryString;
	        $result = $this -> stmt -> execute();

	        if ( $this -> writeQueryCache ) {
	            new Logging('pdo_query_cache', "SQL-Query:\n"   . $this -> lastQuery .
	                                           "\nStatement:\n" . var_export($this -> stmt, true) .
	                                           "\nResult: "     . var_export($result, true) .
	                                           "\nPDO-Object: " . $this -> _getPdoDump()
	                       );
	        }

    		if ( ($result === true) OR ($this -> stmt -> errorCode() === '00000') ) {
    		    $this -> dbtime += $this -> microtime_float() - $this -> startTimeQuery;
    		    return $result;
    		}
    		else {
    		    $this -> errorCount++;
    		    $logMessage = $this -> _generateLogMessage(null, $result, false);
                new Logging('pdo_error', $logMessage);

                if ( $this -> debugMode ) {
                    echo $this -> errorPrint -> printErrorBlock('pdo_error :: ' . $this -> registry -> user_lang['database']['query_error'], $logMessage);
                }

                return false;
    		}
	    }
	    catch ( PDOException $e ) {
	        $this -> errorCount++;
	        $this -> lastError = $e -> getMessage();
	        $logMessage = $this -> _generateLogMessage($e, null, true);
            new Logging('pdo_exception', $logMessage);

            if ( $this -> debugMode ) {
                echo $this -> errorPrint -> printErrorBlock('pdo_exception :: ' . $this -> registry -> user_lang['database']['query_error'], $logMessage);
            }

            return false;
	    }
	}

    /**
     * einzelnes SQL-Komando ausführen
     * z.B. DELETE, etc.
     *
     * @access    public
     * @param     string
     *  $sql        string   SQL-Abfrage
     * @return    boolean
     */
    public function execute($query)
    {
        $this -> query($query);
        if ( $this -> executeQuery() ) {
            $rowAffected = $this -> stmt -> rowCount();
            $this -> rowcount += $rowAffected;
            return $rowAffected;
        }
        else {
	        return false;
        }
    }

    /**
     * einzelnen SELECT-MySQL-Datensatz abrufen
     *
     * @access    public
     * @param     string
     *  $sql        string   SQL-Abfrage
     * @return    array oder bei Fehlern boolean
     */
    function querySingleArray($query)
    {
        $this -> query($query);
        if ( $this -> executeQuery() ) {
            $result = $this -> stmt -> fetchAll($this -> fetchMode);

            if ( is_array($result) AND isset($result[0]) AND count($result[0]) ) {
                $this -> rowcount += count($result[0]);
                return $result[0];
            }
            else {
                // no Result
                return null;
            }
        }
        else {
            // Error => show Logging
            if ( $this -> debugMode ) {
                echo $this -> errorPrint -> printErrorBlock($this -> registry -> user_lang['database']['query_error'], $this -> registry -> user_lang['database']['view_log_for_details']);
            }
        }
    }

    /**
     * einzelnes Item aus einem Datensatz abrufen
     *
     * @access    public
     * @param     string
     *  $sql        string   SQL-Abfrage
     * @return    Array-Item oder bei Fehlern boolean
     */
    public function querySingleItem($query)
    {
        $result = $this -> querySingleArray($query);

        if ( is_array($result) AND count($result) ) {
            return reset($result);
        }
        else {
            return $result;
        }
    }

	/**
     * einzelne SELECT-MySQL-Abfrage ausführen und Array zurückliefern
     *
     * @access    public
     * @param     string
     *  $sql        string   SQL-Abfrage
     * @return    array oder bei Fehlern boolean
     */
    public function queryObjectArray($query)
    {
        $this -> query($query);
        if ( $this -> executeQuery() ) {
            $result = $this -> stmt -> fetchAll($this -> fetchMode);

            if ( count($result) ) {
                $this -> rowcount += count($result);
                return $result;
            }
            else {
                // no Result
                return null;
            }
        }
        else {
            // Error => show Logging
            if ( $this -> debugMode ) {
                echo $this -> errorPrint -> printErrorBlock($this -> registry -> user_lang['database']['query_error'], $this -> registry -> user_lang['database']['view_log_for_details']);
            }
        }
    }

    /**
     * Anzeige der Anzahl aller Datensätze in einer Tabelle
     *
     * @access    public
     * @param     string
     *  $table      string   Name der Tabelle
     * @return    mixed      Anzahl der Zeilen; bei Fehlern false
     */
    public function tableCount($table = "", $cond = "")
    {
        if (trim($table) != "") {
            $this -> query( 'SELECT COUNT(*) AS `tableCount` FROM `' . $table . '` ' . ((strlen($cond)) ? " " . trim($cond) : "") . ';' );
            if ( $this -> executeQuery() ) {
                $result = $this -> stmt -> fetch($this -> fetchMode);
                $this -> rowcount++;

                return $result['tableCount'];
            }
            else {
                // no Result
                return null;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * liefert die größte ID innerhalb der Tabelle
     *
     * @access    public
     * @param     string
     *  $field      string   Name des Abfragefeldes
     *  $table      string   Name des Tabelle
     * @return    mixed      ID des letzten Datensatzes; bei Fehlern false
     */
    public function maxID($field = "id", $table = "", $cond = "")
    {
        if ((trim($field) != "") &&  (trim($table) != ""))
        {
            $this -> query( "SELECT MAX(`" . $field . "`) AS `foundMax` FROM `" . $table . '`' . ((strlen($cond)) ? " " . trim($cond) : "") . ';' );
            if ( $this -> executeQuery() ) {
                $result = $this -> stmt -> fetch($this -> fetchMode);
                $this -> rowcount++;

                return ( is_null($result['foundMax']) ? 0 : $result['foundMax'] );
            }
            else {
                // no Result
                return null;
            }
        }
        else {
            return false;
        }
    }

    /**
     * liefert die kleinste ID innerhalb der Tabelle
     *
     * @access    public
     * @param     string
     *  $field      string   Name des Abfragefeldes
     *  $table      string   Name des Tabelle
     * @return    mixed      ID des ersten Datensatzes; bei Fehlern false
     */
    public function minID($field = "id", $table = "", $cond = "")
    {
        if ((trim($field) != "") &&  (trim($table) != ""))
        {
            $this -> query( "SELECT MIN(`" . $field . "`) AS `foundMin` FROM `" . $table . "`" . ((strlen($cond)) ? " " . trim($cond) : "") . ';' );
            if ( $this -> executeQuery() ) {
                $result = $this -> stmt -> fetch($this -> fetchMode);
                $this -> rowcount++;

                return ( is_null($result['foundMin']) ? 0 : $result['foundMin'] );
            }
            else {
                // no Result
                return null;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * einfügen eines neuen Datensatzes
     *
     * @access    public
     * @param     string
     *  $array      array    Datenarray mit den Einfügedaten
     *  $table      string   Name der Tabelle
     *  $array      array    Spalten, welche kein Escape erfahren sollten
     * @return    boolean
     */
    public function insertRow($array = "", $table = "", $escapeKeys = null)
    {
        $CheckKey = array_keys($array);
        $queryValue = array();

        if((count($array) > 0) AND (is_string($CheckKey[0]) == TRUE) AND (trim($table) != "")) {
            foreach( array_keys($array) AS $value ) {
                $queryValue[] = ':' . $value;
            }
            $query = "INSERT INTO `" . $table . "` (`" . implode("`, `", array_keys($array)) . "`) VALUES(" . implode(", ", $queryValue) . ");";
            $this -> query($query);

            foreach( $array AS $key => $value ) {
                if ( is_array($escapeKeys) AND count($escapeKeys) ) {
                    if ( is_string($value) AND !in_array($key, $escapeKeys) ) {
                        $this -> _fixedStringForStatement($value);
                    }
                }
                else {
                    if ( is_string($value) ) {
                        $this -> _fixedStringForStatement($value);
                    }
                }

                $this -> bind($key, $value);
            }

            if ( $result = $this -> executeQuery() ) {
                $this -> lastInsertID = $this -> connection -> lastInsertId();
                $this -> rowcount++;

                return $result;
            }
        }
        else {
            return false;
        }
    }

    /**
     * updaten eines vorhandenen Datensatzes
     *
     * @access    public
     * @param     string
     *  $array      array    Datenarray mit den Einfügedaten
     *  $table      string   Name der Tabelle
     *  $condition  string   Zusätzliche Parameter zum udaten
     * @return    boolean    bei Fehlern false
     */
    public function updateRow($array = "", $table = "", $condition = NULL)
    {
        $CheckKey  = array_keys($array);
        $queryData = array();
        $queryAdds = array();

        if((count($array) > 0) && (is_string($CheckKey[0]) == TRUE) && (trim($table) != "")) {
            foreach($array as $key => $value) {
                if ( is_string($value) ) {
                    $this -> _fixedStringForStatement($value);
                }
                $stmtKey = ':' . $key;
                $queryData[$stmtKey] = $value;
                $queryAdds[] = '`' . $key . '` = ' . $stmtKey;
            }
            $query = 'UPDATE `' . trim($table) . '` SET ' . implode(', ',  $queryAdds);

            if(!is_null($condition)) {
                $condition = trim($condition);

                if ( strtoupper( substr($condition, 0, 5) ) != 'WHERE' ) {
                    $query .= ' WHERE';
                }
                $query .= ' ' . trim($condition) . ';';
            }

            $this -> query($query);

            foreach( $queryData AS $key => $value ) {
                $this -> bind($key, $value);
            }

            if ( $result = $this -> executeQuery() ) {
                $this -> rowcount++;

                return $result;
            }
        }
        else {
            return false;
        }
    }

    /**
     * Escaping einer Zeichenkette für eine SQL-Abfrage
     *
     * @access    public
     * @param     string
     * @return    string
     */
    public function escapeString($string)
    {
        if ( is_string($string) ) {
            $this -> _fixedStringForStatement($string);
        }
        return $string;
    }

    /**
     * Anzeige der ID des zuletzt eingefügten Datensatzes
     *
     * @access    public
     */
    public function insertID()
    {
        return $this -> lastInsertID;
    }

    /**
     * Anzeige der letzten SQL-Abfrage
     *
     * @deprecated since 2022-07-14
     * @access    public
     * @return    string
     */
    public function get_last_sql()
    {
        return $this -> getLastSql();
    }

    /**
     * Anzeige der letzten SQL-Abfrage
     *
     * @access    public
     * @return    string
     */
    public function getLastSql()
    {
        return $this -> stmt -> queryString;
    }

    /**
     * Anzeige aller bisher ausgeführten SQL-Statements
     *
     * @access   public
     * @return   array
     */
    public function getQueryCache()
    {
        return $this -> queryCache;
    }


    /**
     * Rücksetzen der Laufzeitinformationen
     *
     * @access    public
     */
    function resetStatistics()
    {
        $this -> sqlcounter     = 0;
        $this -> rowcount       = 0;
        $this -> dbtime         = 0;
        $this -> starttime      = 0;
        $this -> startTimeQuery = 0;

        $this -> resetErrorCount();
    }
}