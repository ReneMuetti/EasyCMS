<?php
/**
* Class to store commonly-used variables
*/
class Registry
{
    var $db = null;

	// general objects
	/**
	* Input cleaner object.
	*
	* @var	Input_Cleaner
	*/
	var $input;

	// configuration
	/**
	* Array of data from config.php.
	*
	* @var	array
	*/
	var $config;

	// configuration
	/**
	* Array of data from config-Tablep.
	*
	* @var	array
	*/
	var $website;

	// selected User-Language
	/**
	 * Array of data from lang.xml.
	 *
	 * @var	array
	 */
	var $user_lang;

	// User-Configuration
	/**
	 * Array of data from custom_config.xml.
	 *
	 * @var	array
	 */
	var $user_config;

	var $userinfo;

	var $baseurl;
	var $reloadurl;

	// GPC input
	/**
	* Array of data that has been cleaned by the input cleaner.
	*
	* @var	array
	*/
	var $GPC = array();

	/**
	* Array of booleans. When cleaning a variable, you often lose the ability
	* to determine if it was specified in the user's input. Entries in this
	* array are true if the variable existed before cleaning.
	*
	* @var	array
	*/
	var $GPC_exists = array();

	/**
	* The size of the super global arrays.
	*
	* @var	array
	*/
	var $superglobal_size = array();

	// single variables
	/**
	* IP Address of the current browsing user.
	*
	* @var	string
	*/
	var $ipaddress;

	/**
	* Alternate IP for the browsing user. This attempts to use various HTTP headers
	* to find the real IP of a user that may be behind a proxy.
	*
	* @var	string
	*/
	var $alt_ip;

	/**
	* The URL of the currently browsed page.
	*
	* @var	string
	*/
	var $scriptpath;

	/**
	* The URL of the current page, without anything after the '?'.
	*
	* @var	string
	*/
	var $script;

	/**
	* Generally the URL of the referring page if there is one, though it is often
	* set in various places of the code. Used to determine the page to redirect
	* to, if necessary.
	*
	* @var	string
	*/
	var $url;

	/**
	* Results for specific entries in the datastore.
	*
	* @var	mixed	Mixed, though mostly arrays.
	*/
	var $options       = null;
	var $templatecache = array();

	/**
	* Miscellaneous variables
	*
	* @var	mixed
	*/
	var $nozip;
	var $noheader;
	var $shutdown;

    var $styleurl  = '';

    var $defaultLanguage = 'de';
    var $defaultLangCode = 'de-DE';

	/**
	* Constructor - initializes the nozip system,
	* and calls and instance of the Input_Cleaner class
	*/
    public function __construct()
    {
		// variable to allow bypassing of gzip compression
		$this->nozip = defined('NOZIP') ? true : (@ ini_get('zlib.output_compression') ? true : false);
		// variable that controls HTTP header output
		$this->noheader = defined('NOHEADER') ? true : false;

		// initialize the input handler
		$this->input = new Input_Cleaner($this);
    }

	/**
	* Fetches database/system configuration
	*/
	public function fetch_config()
	{
		// parse the config file
		$this -> config = array();

		$this -> _loadConfigFile('misc.php');
		$this -> _loadConfigFile('database.php');
		$this -> _loadConfigFile('template.php');

		$this -> baseurl = $this -> config['Misc']['baseurl'];

		if ( !defined('TIMENOW') ) {
		    define('TIMENOW', time());
		}

		if ( !defined('DIR') ) {
		    define('DIR', $this -> config['Misc']['path'] . '/');
		}

		if ( !defined('BASEDIR') ) {
		    define('BASEDIR', $this -> config['Misc']['path'] . '/');
		}

		if ( !isset($this -> user_config['language']) ) {
			$this -> user_config['language']      = $this -> defaultLanguage;
			$this -> user_config['language_code'] = $this -> defaultLangCode;
		}
		if ( !isset($this -> user_config['output_charset']) ) {
			$this -> user_config['output_charset'] = $this -> config['Misc']['charset'];
		}
		if ( !isset($this -> user_config['output_iso_charset']) ) {
			$this -> user_config['output_iso_charset'] = $this -> config['Misc']['charset'];
		}

		$this -> _loadLanguageXml();
	}

	/**
	* change Language by User
	*/
	public function change_language($newLang)
	{
	    $this -> user_config['language'] = $newLang;
	    $this -> _loadLanguageXml();
	}

	/**
	* load specific Language
	*/
	public function loadLanguage($newLang = null)
	{
	    if ( !is_null($newLang) AND strlen($newLang) ) {
	        $this -> _loadLanguageXml($newLang);
	    }
	}

	/**
	* store database configuration
	*/
	public function fetch_database_config()
	{
	    $data = $this -> db -> queryObjectArray("SELECT * FROM config");

	    foreach($data AS $key => $val)
	    {
          $this -> config[$val["name"]] = $val["wert"];
	    }
	}

	/**
	* Takes the contents of an array and recursively uses each title/data
	* pair to create a new defined constant.
	*/
	public function array_define($array)
	{
		foreach ($array AS $title => $data)
		{
			if (is_array($data))
			{
				Registry::array_define($data);
			}
			else
			{
				define(strtoupper($title), $data);
			}
		}
	}

	/**
	 * load configuration from file
	 */
	private function _loadConfigFile($filename)
	{
		$fullPathName = realpath('./include/configs/' . $filename);

		if ( is_file($fullPathName) ) {
			include_once($fullPathName);
			$this -> config = array_merge($this -> config, $config_data);
		}
		else {
			die('<br /><br /><strong>Konfigurationsfehler</strong>: Die Konfigurationsdatei ' . $filename . ' existiert, ist aber nicht in einem gültigen Format.');
		}
	}

	/**
	 * Fetches XML-Informations for language
     *
     * @access    private
	 */
	private function _loadLanguageXml($newLang = null)
	{
	    if ( !is_null($newLang) AND strlen($newLang) ) {
	        $langCode = $newLang;
	    }
	    else {
	        $langCode = $this -> user_config['language'];
	    }

		$_fullXmlPath = realpath( $this -> config['Misc']['path'] . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $langCode . '.xml' );

	    if ( is_file($_fullXmlPath) ) {
    	    $this -> user_lang = read_xml( $_fullXmlPath );
	    }
	    else {
	        $_shortPath = str_replace($this -> config['Misc']['path'], '', $_fullXmlPath);

	        die('<br /><br /><strong>Fehler</strong>: Die Sprachdatei ' . $_fullXmlPath . ' (' . $langCode . ') nicht existiert!');
	    }
	}
}
?>