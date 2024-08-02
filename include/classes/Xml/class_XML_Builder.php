<?php
// #############################################################################
// xml builder

class XML_Builder
{
	var $registry     = null;
    var $charset      = 'utf-8';
    var $content_type = 'text/xml';
    var $open_tags    = array();
    var $data         = array();
    var $tabs         = "";
    var $doc          = "";

    function __construct (&$registry, $charset = null, $content_type = null)
    {
        if (is_object($registry)) {
            $this -> registry =& $registry;
        }
        else {
            trigger_error("XML_Builder::Registry object is not an object", E_USER_ERROR);
        }

        if ($content_type) {
            $this -> content_type = $content_type;
        }

        if ($charset == null) {
            $charset = CHARSET;
        }

        $this -> charset = (strtolower($charset) == 'iso-8859-1') ? 'windows-1252' : $charset;
    }

    /**
      * Fetches the content type header with $this->content_type
      */
    function fetch_content_type_header()
    {
        return 'Content-Type: ' . $this -> content_type . ($this -> charset == '' ? '' : '; charset=' . $this -> charset);
    }

    /**
      * Fetches the content length header
      */
    function fetch_content_length_header()
    {
        return 'Content-Length: ' . $this -> fetch_xml_content_length();
    }

    /**
      * Sends the content type header with $this->content_type
      */
    function send_content_type_header()
    {
        @ header( 'Content-Type: ' . $this -> content_type . ($this -> charset == '' ? '' : '; charset=' . $this -> charset) );
    }

    /**
      * Sends the content length header
      */
    function send_content_length_header()
    {
        @ header( 'Content-Length: ' . $this -> fetch_xml_content_length() );
    }

    /**
      * Returns the <?xml tag complete with $this->charset character set defined
      *
      * @return	string	<?xml tag
      */
    function fetch_xml_tag()
    {
        return '<?xml version="1.0" encoding="' . $this -> charset . '"?>' . "\n";
    }

    /**
      *
      * @return	integer	Length of document
      */
    function fetch_xml_content_length()
    {
        if ( $this -> charset == 'utf-8' ) {
            return mb_strlen($this -> doc) + mb_strlen( $this -> fetch_xml_tag() );
        }
        else {
            return strlen($this -> doc) + strlen( $this -> fetch_xml_tag() );
        }
    }

    function add_group($tag, $attr = array())
    {
        $this -> open_tags[] = $tag;
        $this -> doc  .= $this -> tabs . $this -> build_tag($tag, $attr) . "\n";
        $this -> tabs .= "\t";
    }

    function close_group()
    {
        $tag = array_pop($this -> open_tags);

        if ( $this -> charset == 'utf-8' ) {
            $this -> tabs = mb_substr($this -> tabs, 0, -1);
        }
        else {
            $this -> tabs = substr($this -> tabs, 0, -1);
        }
        
        $this -> doc .= $this -> tabs . "</$tag>\n";
    }

    function add_tag($tag, $content = '', $attr = array(), $cdata = false, $htmlspecialchars = false)
    {
        $this -> data[$tag] = $content;
        $this -> doc .= $this -> tabs . $this -> build_tag( $tag, $attr, ($content === '') );

        if ($content !== '') {
            if ($htmlspecialchars) {
                $this -> doc .= htmlspecialchars_uni($content);
            }
            else if ( $cdata OR preg_match('/[\<\>\&\'\"\[\]]/', $content) ) {
                $this -> doc .= '<![CDATA[' . $this -> escape_cdata($content) . ']]>';
            }
            else {
                $this -> doc .= $content;
            }
            $this -> doc .= "</$tag>\n";
        }
    }

    function build_tag($tag, $attr, $closing = false)
    {
        $tmp = "<$tag";

        if (!empty($attr)) {
            foreach ($attr AS $attr_name => $attr_key) {
                if (strpos($attr_key, '"') !== false) {
                    $attr_key = htmlspecialchars_uni($attr_key);
                }
                $tmp .= " $attr_name=\"$attr_key\"";
            }
        }

        $tmp .= ($closing ? " />\n" : '>');

        return $tmp;
    }

    function escape_cdata($xml)
    {
        // strip invalid characters in XML 1.0:  00-08, 11-12 and 14-31
        // I did not find any character sets which use these characters.
        $xml = preg_replace('#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $xml);

        return str_replace(array('<![CDATA[', ']]>'), array('?![CDATA[', ']]?'), $xml);
    }

    function output()
    {
        if ( !empty($this -> open_tags) ) {
            trigger_error("Im Dokument sind keine Open-Tags vorhanden!", E_USER_ERROR);
            return false;
        }

        return $this -> doc;
    }

    /**
      * Prints out the queued XML and then exits.
      *
      * @param	boolean	If not using shut down functions, whether to do a full shutdown
      * (session updates, etc) or to just close the DB
      */
    function print_xml($full_shutdown = false)
    {
        if (defined('NOSHUTDOWNFUNC')) {
            if ($full_shutdown) {
                exec_shut_down();
            }
            else {
                $this -> registry -> db -> close();
            }
        }

        $this -> send_content_type_header();

        if ( $this -> fetch_send_content_length_header() ) {
            // this line is causing problems with mod_gzip/deflate,
            // but is needed for some IIS setups
            $this -> send_content_length_header();
        }

        echo $this -> fetch_xml();
        exit;
    }

    /**
      * Prints XML header, use this if you need to output data that can't be easily queued.
      * It won't work properly if content-length is required
      *
      * @param	boolean	If not using shut down functions, whether to do a full shutdown
      * (session updates, etc) or to just close the DB
      */
    function print_xml_header()
    {
        // Can't use this is we need to send a content length header as we
        // don't know how much bogus data is going to be sent
        if ($this->fetch_send_content_length_header()) {
            if (!defined('SUPPRESS_KEEPALIVE_ECHO')) {
                define('SUPPRESS_KEEPALIVE_ECHO', true);
            }
            return false;
        }

        $this -> send_content_type_header();
        echo $this -> fetch_xml_tag();
    }

    /**
      * Prints out the queued XML and then exits. Use in combination with print_xml_header();
      *
      * @param	boolean	If not using shut down functions, whether to do a full shutdown
      * (session updates, etc) or to just close the DB
      */
    function print_xml_end($full_shutdown = false)
    {
        // Can't use this is we need to send a content length header as we don't
        // know how much bogus data is going to be sent
        if ($this->fetch_send_content_length_header()) {
            return $this -> print_xml();
        }

        if (defined('NOSHUTDOWNFUNC')) {
            if ($full_shutdown) {
                exec_shut_down();
            }
            else {
                $this -> registry -> db -> close();
            }
        }

        echo $this -> output();
    }

    /**
      * Determine if we send the content length header
      *
      * @return boolean
      */
    function fetch_send_content_length_header()
    {
        return (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false);
    }

    /**
      * Fetches the queued XML
      *
      * @return string
      */
    function fetch_xml()
    {
        return $this -> fetch_xml_tag() . $this -> output();
    }
}
?>