<?php
class FileDir
{
    protected $file_path = '';

    private $dir_path  = '';
    private $last_dir = '';

    private $file_list = array();

    private $size_dir  = 0;
    private $count_dir = 0;


    public function __construct($subDir = '')
    {
        global $website;

        if ( !is_object($website) )
        {
            trigger_error('FileScanner::Registry object is not an object', E_USER_ERROR);
        }

        $this -> file_path = $website -> config['Misc']['path'] . '/' . (str_replace('.', '', $subDir)) . '/';

        if ( !is_dir($this -> file_path) )
        {
            $this -> file_path = $website -> config['Misc']['path'] . '/';
        }

        $this -> get_all_files();
    }

    public function setDefault()
    {
        $this -> size_dir  = 0;
        $this -> count_dir = 0;
    }

    public function getFileList($filter = '*', $fullInformation = false)
    {
        if ( $filter == '*' ) {
            return $this -> file_list;
        }
        else {
            $f_array = array();

            foreach( $this -> file_list AS $file ) {
                if ( strpos($file, $filter) !== false ) {
                    if ( $fullInformation === false ) {
                        $f_array[] = $file;
                    }
                    else {
                        $f_array[] = array(
                                         'name'  => substr($file, 1),
                                         'size'  => makeSize(filesize($this -> file_path . $file)),
                                         'added' => date( 'Y-m-d H:i:s', filemtime($this -> file_path . $file) ),
                                     );
                    }
                }
            }

            return $f_array;
        }
    }

    public function get_dir_count($path = '')
    {
        if ( $path == '' ) {
            $path = $this -> file_path;
        }

        if ( !is_dir($path) ) {
            return null;
        }

        $handle = @ opendir( $path );

        if( !$handle ) {
            return false;
        }

        $anzahl = 0;

        while ( $file = @ readdir ($handle) ) {
            if ( ($file == '.') OR ($file == '..') ) {
                continue;
            }

            $anzahl++;
        }

        @ closedir( $handle );

        return $anzahl;
    }

    public function get_dir_content($path = '', $showParent = false)
    {
        if ( $path == '' ) {
            $path = $this -> file_path;
        }

        if ( !is_dir($path) ) {
            return null;
        }

        $handle = @ opendir( $path );

        if( !$handle ) {
            return false;
        }

        $dirs  = array();
        $files = array();

        while ( $file = @ readdir ($handle) ) {
            if ( ($file == '.') OR (($file == '..') AND ($showParent == false)) ) {
                continue;
            }

            if ( is_dir($path . $file) ) {
                $dirs[$file] = array(
                              'type' => 'dir',
                              'name' => $file,
                              'size' => $this -> get_dir_count($path . $file)
                          );
            }

            if ( is_file($path . $file) ) {
                $files[$file] = array(
                               'type' => 'file',
                               'name' => $file,
                               'size' => makeSize(filesize($path . $file))
                           );
            }
        }

        @ closedir( $handle );

        ksort($dirs);
        ksort($files);

        return array_merge( $dirs, $files );
    }

    public function get_all_files($path = '')
    {
        if ( $path == '' ) {
            $path = $this -> file_path;
        }

        if ( !is_dir($path) ) {
            return null;
        }

        $handle = @ opendir( $path );

        if( !$handle ) {
            return false;
        }

        while ( $file = @ readdir ($handle) ) {
            if ( $file == '.' OR $file == '..' ) {
                continue;
            }

            if( is_dir($path . $file) ) {
                $this -> last_dir = $file;

                $this -> get_all_files( $path . $file . "/" );
            }
            else {
                if ( !is_dir($path . $file)     AND
                     is_readable($path . $file) AND
                     file_exists($path . $file)
                   )
                {
                    $this -> file_list[] = $this -> last_dir . '/' . $file;
                }
            }
        }

        @ closedir( $handle );

        natcasesort($this -> file_list);
    }

    public function dir_size($path = '')
    {
        if ( $path == '' )
        {
            $path = $this -> dir_path;
        }

        if ( !is_dir( $path ) )
        {
            return false;
        }

        $handle = @ opendir( $path );

        if( !$handle )
        {
            return false;
        }

        while ( $file = @ readdir ($handle) )
        {
            if ( ($file == '.') OR ($file == '..') )
            {
                continue;
            }

            $file_name = $path . '/' . $file;

            if( is_dir($file_name) )
            {
                $this -> dir_size( $file_name . '/' );
            }
            elseif ( is_file($file_name) )
            {
                $this -> size_dir += filesize( $file_name );
                $this -> count_dir ++;
            }
        }

        @ closedir( $handle );

        return array(makeSize($this -> size_dir), $this -> count_dir);
    }
}