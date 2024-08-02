<?php
function read_xml($xml_file = 'empty.xml')
{
    if ( is_file($xml_file) ) {
        $xml = file_read($xml_file);

        $xmlobj = new XML_Parser($xml);

        if ( $xmlobj -> error_no == 1 )
        {
            throw new Exception('ReadXML :: Es wurde kein XML-Code eingelesen und die Variable $path war leer.');
        }
        
        $xmlobj -> parse();

        if( is_array($xmlobj -> parseddata) ) {
            return $xmlobj -> parseddata;
        }
        else {
        	echo '<pre>';
                //var_dump($xml_file);
                //var_dump($xml);
                //var_dump($xmlobj);

                throw new Exception( sprintf('ReadXML :: XML-Fehler: <b>%1$s</b> in Zeile <b>%2$s</b>',
                                             $xmlobj -> error_string(),
                                             $xmlobj -> error_line()
                                            )
	                                 );
            echo '</pre>';
            return false;
        }
    }
    else {
        throw new Exception( 'ReadXML :: Datei <' . $xml_file . '> wurde nicht gefunden!' );
    }
}

function write_xml($xml_file = 'empty.xml', $xml_data = null)
{
    if ( is_file($xml_file) ) {
        $xml_data = "<?xml version=\"1.0\"?>\n" . $xml_data;
        return file_write($xml_file, $xml_data);
    }
    else {
        throw new Exception( 'WriteXML :: Datei ' . $xml_file . ' wurde nicht gefunden!' );
    }
}

function buildXmlConfig($xml_data = null, $rootElem = '', $rootParam = null)
{
    global $site;

    if ( !count($xml_data) ) {
        return false;
    }
    else {
         $xml = new XML_Builder($site, CHARSET);

         if ( strlen($rootElem) ) {
            $xml -> add_group($rootElem, $rootParam);
         }
         makeXmlContent($xml, $xml_data);
         if ( strlen($rootElem) ) {
            $xml -> close_group();
         }

         $output = $xml -> output();
         unset($xml);
         return $output;
    }
}

function makeXmlContent( &$Builder, $xml_data )
{
    foreach( $xml_data AS $key => $value ) {
        if ( is_array($value) ) {
            $Builder -> add_group($key);

            foreach( $value AS $value_key => $value_param ) {
                if ( is_array($value_param) ) {
                    $Builder -> add_group($value_key);
                    makeXmlContent($Builder, $value_param);
                    $Builder -> close_group();
                }
                else {
                    $Builder -> add_tag($value_key, $value_param);
                }
            }
            $Builder -> close_group();
        }
        elseif ( is_string($value) ) {
            $Builder -> add_tag($key, $value);
        }
    }
}