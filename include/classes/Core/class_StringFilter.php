<?php
class StringFilter
{
    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    public function filter($s = '', $filter = TRUE)
    {
        ##@s: string, string to be filtered, must be encoded utf-8
        ##@filter: boolean, true->filter $s for use as filename and remove
        ###   reserved expressions and signs for Windows, Linux, php, apache

        ## maps German (umlauts) and other European characters onto two characters before just removing diacritics
        $s = preg_replace( '@\x{00c4}@u' , "Ae", $s );      // umlaut � => Ae
        $s = preg_replace( '@\x{00d6}@u' , "Oe", $s );      // umlaut � => Oe
        $s = preg_replace( '@\x{00dc}@u' , "Ue", $s );      // umlaut � => Ue
        $s = preg_replace( '@\x{00e4}@u' , "ae", $s );      // umlaut � => ae
        $s = preg_replace( '@\x{00f6}@u' , "oe", $s );      // umlaut � => oe
        $s = preg_replace( '@\x{00fc}@u' , "ue", $s );      // umlaut � => ue
        $s = preg_replace( '@\x{00f1}@u' , "ny", $s );      // � => ny
        $s = preg_replace( '@\x{00ff}@u' , "yu", $s );      // � => yu

        $s = preg_replace( '@\pM@u'      , "",   $s );     // removes diacritics

        $s = preg_replace( '@\x{00df}@u' , "ss", $s );     // maps German � onto ss
        $s = preg_replace( '@\x{00c6}@u' , "Ae", $s );     // � => Ae
        $s = preg_replace( '@\x{00e6}@u' , "ae", $s );     // � => ae
        $s = preg_replace( '@\x{0132}@u' , "Ij", $s );     // ? => Ij
        $s = preg_replace( '@\x{0133}@u' , "ij", $s );     // ? => ij
        $s = preg_replace( '@\x{0152}@u' , "Oe", $s );     // � => Oe
        $s = preg_replace( '@\x{0153}@u' , "oe", $s );     // � => oe

        $s = preg_replace( '@\x{00d0}@u' , "D",  $s );     // � => D
        $s = preg_replace( '@\x{0110}@u' , "D",  $s );     // � => D
        $s = preg_replace( '@\x{00f0}@u' , "d",  $s );     // � => d
        $s = preg_replace( '@\x{0111}@u' , "d",  $s );     // d => d
        $s = preg_replace( '@\x{0126}@u' , "H",  $s );     // H => H
        $s = preg_replace( '@\x{0127}@u' , "h",  $s );     // h => h
        $s = preg_replace( '@\x{0131}@u' , "i",  $s );     // i => i
        $s = preg_replace( '@\x{0138}@u' , "k",  $s );     // ? => k
        $s = preg_replace( '@\x{013f}@u' , "L",  $s );     // ? => L
        $s = preg_replace( '@\x{0141}@u' , "L",  $s );     // L => L
        $s = preg_replace( '@\x{0140}@u' , "l",  $s );     // ? => l
        $s = preg_replace( '@\x{0142}@u' , "l",  $s );     // l => l
        $s = preg_replace( '@\x{014a}@u' , "N",  $s );     // ? => N
        $s = preg_replace( '@\x{0149}@u' , "n",  $s );     // ? => n
        $s = preg_replace( '@\x{014b}@u' , "n",  $s );     // ? => n
        $s = preg_replace( '@\x{00d8}@u' , "O",  $s );     // � => O
        $s = preg_replace( '@\x{00f8}@u' , "o",  $s );     // � => o
        $s = preg_replace( '@\x{017f}@u' , "s",  $s );     // ? => s
        $s = preg_replace( '@\x{00de}@u' , "T",  $s );     // � => T
        $s = preg_replace( '@\x{0166}@u' , "T",  $s );     // T => T
        $s = preg_replace( '@\x{00fe}@u' , "t",  $s );     // � => t
        $s = preg_replace( '@\x{0167}@u' , "t",  $s );     // t => t

        $s = preg_replace( '@[^\0-\x80]@u', "",  $s );     // remove all non-ASCii characters

        ## remove all reserved expressions and signs for Windows, Linux, php, apache
        if ($filter === true)
        {
            $_search = array(' ', 0x0, '<', '>', '|', '?', '"', ':', '\\', '/', '*');

            $s = str_replace( $_search, '_', $s );

            ## remove leading and trailing dot
            $s = trim($s, '.');

            ## avoid reserved expressions
            $_names = array('CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4',
                            'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3',
                            'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9');

            if (in_array(strtoupper($s), $_names, true))
            {
                return false;
            }
        }

        ## possible errors in UTF8-regular-expressions
        if (empty($s))
        {
            return false;
        }

        return $s;
    }
}
?>