<?php
// #############################################################################
/**
 * Returns the contents of a file
 *
 * @param	string	Path to file (including file name)
 *
 * @return	string	If file does not exist, returns an empty string
 */
function file_read($path)
{
    // On some versions of PHP under IIS, file_exists returns false for uploaded files,
    // even though the file exists and is readable. http://bugs.php.net/bug.php?id=38308
    if(!file_exists($path) AND !is_uploaded_file($path))
    {
        return false;
    }
    else
    {
        return @ file_get_contents($path);
    }
}

// #############################################################################
/**
 * Write the contents in a file
 *
 * @param	string	Path to file (including file name)
 * @param	string	Filecontent
 *
 * @return	string	If file does not exist, returns an empty string
 */
function file_write($path, $content)
{
    // On some versions of PHP under IIS, file_exists returns false for uploaded files,
    // even though the file exists and is readable. http://bugs.php.net/bug.php?id=38308
    if( !file_exists($path) )
    {
        return false;
    }
    else
    {
        return @ file_put_contents($path, $content, LOCK_EX);
    }
}

// #############################################################################
/**
* Converts shorthand string version of a size to bytes, 8M = 8388608
*
* @param	string			The value from ini_get that needs converted to bytes
*
* @return	integer			Value expanded to bytes
*/
function ini_size_to_bytes($value)
{
    $value  = trim($value);
    $retval = intval($value);

    switch(strtolower($value[strlen($value) - 1]))
    {
        case 'g': $retval *= 1024;
                  /* break missing intentionally */
        case 'm': $retval *= 1024;
                  /* break missing intentionally */
        case 'k': $retval *= 1024;
                  break;
    }

    return $retval;
}

// #############################################################################
/**
* Konvertiert Byte-Angaben in menschenlesbare Daten
*
* @param	integer			The value that needs converted from bytes
* @param    boolean         Convert Float to Integer
*
* @return	integer			Value expanded to bytes
*/
function makeSize($bytes, $isInt = TRUE)
{
    if ( $isInt == TRUE ) {
        $thisArray = array('',' KB',' MB',' GB',' TB',' PB');
    }
    else {
        $thisArray = array(' B',' KB',' MB',' GB',' TB',' PB');
        $bytes = max(0, $bytes);
    }

    foreach ($thisArray AS $i => $k)
    {
        if ($bytes < 1024) break;
        $bytes /= 1024;
    }
    return number_format($bytes, 2, ",", ".") . $k;
}

// #############################################################################
/**
* Finishes off the current page (using templates), prints it out to the browser
* and halts execution
*
* @param	string	The HTML of the page to be printed
* @param	boolean	Send the content length header?
*/
function print_output($vartext)
{
    header("Content-Type: text/html; charset=" . CHARSET);
    header("Expires: : Mon, 1 Jan 2010 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Pragma: No-cache");
    header("Expires: 0");
    header("Content-Length: " . strlen($vartext));

	// make sure headers sent returns correctly
	if (ob_get_level() AND ob_get_length()) {
	    ob_end_flush();
    }

	// show regular page
	echo $vartext;

	// broken if zlib.output_compression is on with Apache 2
	if (SAPI_NAME != 'apache2handler' AND SAPI_NAME != 'apache2filter') {
        flush();
    }
    exit;
}
?>