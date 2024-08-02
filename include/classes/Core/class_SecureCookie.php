<?php
class SecureCookie
{
    var $_CookieObject;
    var $_CookieID;
    var $_Expire;
    var $_EncryptionPassword;
    var $_Path;
    var $_Domain;
    var $_Secure;
    var $_DefaultError = 'INVALID COOKIE NAME. YOU MAY NOT USE "____ENCRYPTIONPASSWORD" AS YOUR COOKIE NAME';


    /***
     * Create Object. 
     * EncryptionPassword: (required) The password to encrypt the cookie.
     *        - NOTE: Changing this password after a cookie has been set will make the cookie fail to be read.
     * CookieID: (required) A unique name for the cookie. This is the ACTUAL cookie name. Do not use the name of a cookie
     *                         already in use on your website.
     * expire, domain, path, secure: Standard Cookie Paramaters.
     *        - NOTE: This applies to all values in the object!
     *                       You will need multiple objects for different parameters.
     ***/
    function __construct($EncryptionPassword, $CookieID, $expire=false, $path=false, $domain=false, $secure=true)
    {
        // Store all our passed parameters.
        $this -> _Expire             = $expire;
        $this -> _EncryptionPassword = $EncryptionPassword;
        $this -> _CookieID           = $CookieID;
        $this -> _Path               = $path;
        $this -> _Domain             = $domain;
        $this -> _Secure             = $secure;

        // Does this cookie ID exists?
        if(isset($_COOKIE[$CookieID]))
        {
            $obj = unserialize($this -> _Decrypt($_COOKIE[$this -> _CookieID], $this -> _EncryptionPassword));

            if($obj['____ENCRYPTIONPASSWORD'] == md5($this -> _EncryptionPassword))
            {
                $this -> _CookieObject = $obj;
            }
            else
            {
                $this -> _CookieObject = array('____ENCRYPTIONPASSWORD' => md5($this -> _EncryptionPassword));
            }
        }
        else
        {
            $this -> _CookieObject = array('____ENCRYPTIONPASSWORD' => md5($this -> _EncryptionPassword));
        }
        unset($obj);
    }

    // Alias: SetCookie()
    function Set($name, $value)
    {
        $this -> SetCookie($name, $value);
    }

    // Alias: GetCookie()
    function Get($name, $default = null)
    {
        return $this -> GetCookie($name, $default);    
    }

    // Alias: DeleteCookie()
    function Del($name)
    {
        $this -> DeleteCookie($name);
    }

    /**
     * Sets the value of the cookie.
     **/
    function SetCookie($name, $value)
    {
        // Check to make sure not using invalid name.
        if($name != '____ENCRYPTIONPASSWORD')
        {
            $obj = $this -> _CookieObject;
            $obj['____ENCRYPTIONPASSWORD'] = md5($this -> _EncryptionPassword);
            $obj[$name] = $value;
            $this -> _CookieObject = $obj;
            $obj = $this -> _Encrypt(serialize($obj),$this -> _EncryptionPassword);
            setcookie($this -> _CookieID, $obj, $this -> _Expire, $this -> _Path, $this -> _Domain, $this -> _Secure);
            $_COOKIE[$this -> _CookieID] = $obj;
            unset($obj);
        }
        else
        {
            die($this -> _DefaultError);
        }
    }
    /**
     * Retrieves the specified name from the object.
     **/
    function GetCookie($name, $default = null)
    {
        // Check to make sure not using invalid name.
        if($name != '____ENCRYPTIONPASSWORD')
        {
            $obj = $this -> _CookieObject;
            return isset($obj[$name]) ? $obj[$name] : $default;
        }
        else
        {
            die($this -> _DefaultError);
        }
    }

    /**
     * Deletes the specified name from the object.
     **/
    function DeleteCookie($name)
    {
        // Check to make sure not using invalid name.
        if($name != '____ENCRYPTIONPASSWORD')
        {
            $obj  = $this->_CookieObject;
            unset($obj[$name]);
            $this -> _CookieObject=$obj;    
            $obj  = $this->_Encrypt(serialize($obj),$this -> _EncryptionPassword);
            setcookie($this -> _CookieID, $obj, $this -> _Expire, $this -> _Path, $this -> _Domain, $this -> _Secure);
            $_COOKIE[$this -> _CookieID] = $obj;
            unset($obj);
        }
        else
        {
            die($this -> _DefaultError);
        }
    }

    // Returns the Cookie Array
    function GetObject()
    {
        $obj = $this -> _CookieObject;
        unset($obj['____ENCRYPTIONPASSWORD']);
        return $obj;
    }

    // Löschen der gesamten Cookie-Daten
    function RemoveCookie()
    {
        setcookie($this -> _CookieID, '', time()-10000, $this -> _Path, $this -> _Domain, $this -> _Secure);
    }

    function _Encrypt($string, $key)
    {
        $result = '';
        $count  = strlen($string);

        for($i = 0; $i < $count; $i++)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key)) - 1, 1);
            $char    = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode(gzdeflate($result,9));
    }

    function _Decrypt($string, $key)
    {
        $result = '';
        $string = base64_decode($string);
        $count  = strlen($string);

        for($i = 0; $i < $count; $i++)
        {
            $char    = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char    = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }
 
}