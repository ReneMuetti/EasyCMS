<?php
class LoginLogout
{
    private $registry = null;
    private $db       = null;
    private $usr      = null;

    private $cookie = null;

    private $error    = array();
    private $userdata = array();
    private $account  = array();

    private $secure   = '';
    private $shash    = '';
    private $baseurl  = '';
    private $curDate  = '';
    private $basePath = '';

    private $secLength = 20;

    private $ok = false;

    private $_SecureHash = 'OELUH3X4LWFIBPSL0V1DST01Q1S3PUKT8G7T6YZ6AJAVLY2ULWCQ9BBIMXKNA2YUR';

    public function __construct()
    {
        global $website;

        $this -> registry = $website;

        $this -> usr = $website -> userinfo;
        $this -> db  = $website -> db;

        if ( empty($website -> baseurl) ) {
            $this -> baseurl = "https://empty.host";
        }

        $this -> curDate  = date("Y-m-d H:i:s");
        $this -> basePath = $path = str_replace(array('/include', '/classes', '//'), '', dirname(__FILE__));

        $this -> setSecureCookie();
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> db);
        unset($this -> usr);
        unset($this -> cookie);
        unset($this -> userdata);
    }

    public function decodeErrors($errors)
    {
        $errors .= ',0'; // Dummy, um zu zerlegen
        $html = array();

        $this -> error = explode(',', $errors);

        foreach( $this -> error AS $error ) {
            if ( isset($this -> registry -> user_lang['login_page']['error_' . $error]) ) {
                $html[] = '<li class="status status-fail">' . $this -> registry -> user_lang['login_page']['error_' . $error] . '</li>';
            }
        }

        $this -> error = array();
        return $html;
    }

    public function checkUserIfLogedin()
    {
        global $website, $renderer;

        $cookie_data = $this -> cookie -> GetObject();

        if ( count($cookie_data) )
        {
            $userid = intval($this -> cookie -> Get("uid"));
            $secure = $this -> cookie -> Get("sid");
            $this -> getUserData("`id` = '" . $userid . "' AND `session` = '" . $secure . "'");

            if ( $this -> userdata['passhash'] != $secure )
            {
                $this -> error[] = '12';
                $this -> returnToLogin();
            }

            if ( !count($this -> userdata) OR ($this -> userdata['enabled'] != 'yes') OR ($this -> userdata['status'] != 'confirmed') )
            {
                if ( !count($this -> userdata) )
                {
                    $this -> error[] = '9';
                }
                if ( $this -> userdata['enabled'] != 'yes' )
                {
                    $this -> error[] = '10';
                }
                if ( $this -> userdata['status'] != 'confirmed' )
                {
                    $this -> error[] = '11';
                }

                $this -> returnToLogin();
            }
        }

        if ( isset($_SESSION[SESSION]) AND isset($_SESSION[SESSION]["username"]) )
        {
            $this -> getUserData("`username` = '" . $_SESSION[SESSION]["username"] . "'");
        }

        if ( is_array($this -> userdata) AND count($this -> userdata) ) {
            $website -> userinfo = $this -> userdata;

            $this -> usr   = $this -> userdata;
            $this -> shash = $this -> userdata['session'];

            $_SESSION[SESSION]       = $this -> userdata;
            $_SESSION[SESSION]["ip"] = $this -> getIP();

            if ( !$this -> getSecure() ) {
                header("HTTP/1.0 403 Forbidden");
                print("<html><body><h1>403 Forbidden</h1>No valid security data available<br /><br />" .
                      "Delete your cookies in the browser and try again.</body></html>\n");
                die();
            }

            $this -> getAccountData();
            $this -> updateUserInformation();

            if ( is_array($this -> account) AND count($this -> account) ) {
                $data = array(
                            'lastaccess' => $this -> curDate
                        );
                $this -> db -> updateRow($data, 'accounts', "`userid` = " . $this -> userdata['id']);
            }
            else {
                $data = array(
                            'lastaccess' => $this -> curDate,
                            'userid'     => $this -> userdata['id'],
                            'chash'      => $this -> userdata['passhash'],
                            'username'   => $this -> userdata['username'],
                            'email'      => $this -> userdata['email']
                        );
                $this -> db -> insertRow($data, 'accounts');
            }

            if ( isset($this -> userdata['language']) AND strlen($this -> userdata['language']) ) {
                $website -> change_language($this -> userdata['language']);
                $renderer -> updateLanguage();
            }
        }
        else {
            // kein angemeldeter User
            $this -> returnToLogin();
        }
    }

    public function login($username = null, $password = null)
    {
        $this -> secure = $this -> getRandomString($this -> secLength);
        $this -> shash  = $this -> getSHA512();

        if ( strlen($username) AND strlen($password) ) {
            $this -> getUserData("`username` = '" . $username . "'");

            if ( strlen($this -> userdata["pass"]) ) {
                $hasher = new PasswordHash(8, FALSE);
                $this -> ok = $hasher -> CheckPassword($password, $this -> userdata["pass"]);
            }
            else {
                $check = md5($this -> userdata["secret"] . $password . $this -> userdata["secret"]);
                $this -> ok = ( ($this -> userdata["passhash"] == $check) ? TRUE : FALSE );
            }

            if ( $this -> ok ) {
                $data = array(
                            'last_login' => $this -> curDate,
                        );
                $this -> db -> updateRow($data, 'users', "`id` = " . $this -> userdata['id']);
            }

            $this -> createLoginInformation();
            $this -> returnToIndex();
        }
        else {
            if ( !strlen($username) ) {
                $this -> error[] = '2';
            }
            if ( !strlen($password) ) {
                $this -> error[] = '3';
            }

            $this -> returnToLogin();
        }
    }

    public function logout()
    {
        if ( is_array($this -> usr) AND isset($this -> usr['id']) ) {
            $data = array(
                        'session' => ''
                    );
            $this -> db -> updateRow($data, 'users', 'id = ' . $this -> usr['id']);

            $data = array(
                        'secure' => '',
                        'hash'   => ''
                    );
            $this -> db -> updateRow($data, 'secure', 'id = ' . $this -> usr['id']);

            $this -> cookie -> RemoveCookie();
        }

        session_unset();
        session_destroy();

        $this -> returnToIndex();
    }

    public function getSecure()
    {
        if ( strlen($this -> shash) )
        {
            $query = "SELECT `secure`, `hash` FROM `secure` WHERE `hash` = '" . $this -> shash . "' AND `id` = " . $this -> usr['id'] . " LIMIT 1";
            $data  = $this -> db -> querySingleArray($query);

            $this -> secure = trim($data["secure"]);
            $check = $this -> getSHA512();

            if ( $check == $data['hash'] )
            {
                return TRUE;
            }
            else
            {
                $this -> error[] = '4';
                return FALSE;
            }
        }
        else
        {
            $this -> error[] = '5';
        }
    }

    public function setSecure()
    {
        $data = array(
                    'secure' => $this -> secure,
                    'hash'   => $this -> shash
                );

        if ( strlen($this -> secure) AND strlen($this -> shash) AND count($this -> userdata) )
        {
            $anzahl = $this -> db -> tableCount('secure', 'WHERE `id` = ' . $this -> userdata['id']);

            if ( $anzahl == 0 )
            {
                $data['id'] = $this -> userdata['id'];
                $this -> db -> insertRow($data, 'secure');
            }
            elseif( $anzahl == 1 )
            {
                $this -> db -> updateRow($data, 'secure', '`id` = ' . $this -> userdata['id']);
            }
            else
            {
                $this -> error[] = '6';
            }
        }
        else
        {
            $this -> error[] = '7';
        }
    }

    public function getSecrete()
    {
        $this -> secure = '';
        for ($i = 0; $i < $this -> secLength; $i++) {
            $this -> secure .= chr(mt_rand(0, 255));
        }
    }

    private function getUserData($query_filter)
    {
        $this -> userdata = array();

        $query = "SELECT * FROM `users` WHERE " . $query_filter . " AND `status` = 'confirmed' LIMIT 1";
        $this -> userdata = $this -> db -> querySingleArray($query);
    }

    private function getAccountData()
    {
        $this -> account = array();

        $query = "SELECT * FROM `accounts` WHERE `username` = '" . $this -> userdata['username'] . "' AND `chash` = '" . $this -> userdata['passhash'] . "' LIMIT 1";
        $this -> account = $this -> db -> querySingleArray($query);
    }

    private function setSecureCookie()
    {
        $this -> cookie = new SecureCookie($this -> _SecureHash, SESSION . "_cookie", time() + 8600, '/', substr($this -> baseurl, 7));
    }

    private function getIP()
    {
        return $this -> registry -> ipaddress;
    }

    private function getHasPad()
    {
        return str_pad($this -> shash, $this -> secLength);
    }

    private function getRandomString($max = 8)
    {
        $max = intval($max);

        $zufall = random_int(PHP_INT_MIN, PHP_INT_MAX);
        $result = substr(md5($zufall), 0, $max);

        return $result;
    }

    private function getSHA512()
    {
        return SHA512($this -> secure);
    }

    private function getErrors()
    {
        return (count($this -> error) ? 'error=' . implode(',', $this -> error) : '');
    }

    private function openUrl($url)
    {
        header('Location: ' . $this -> baseurl . $url);
        die();
    }

    private function returnToIndex()
    {
        $this -> openUrl('/admin/index.php');
        die();
    }

    private function returnToLogin()
    {
        if ( strlen($this -> registry -> GPC['lang']) ) {
            $_language = 'lang=' . $this -> registry -> GPC['lang'] . '&';
        }
        else {
            $_language = 'lang=de&';
        }

        $this -> openUrl('/admin/login.php?' . $_language . $this -> getErrors() );
        die();
    }

    private function updateUserInformation()
    {
        $data = array(
                    'session'     => $this -> shash,
                    'last_access' => $this -> curDate,
                    'ip'          => $this -> getIP()
                );
        $this -> db -> updateRow($data, 'users', "`id` = " . $this -> userdata['id']);
    }

    private function createLoginInformation()
    {
        if ( $this -> ok )
        {
            $this -> setSecure();
            $this -> updateUserInformation();

            $_SESSION[SESSION]       = $this -> userdata;
            $_SESSION[SESSION]["ip"] = $this -> getIP();
        }
        else
        {
            $this -> error[] = '8';
            $this -> returnToLogin();
        }
    }
}
