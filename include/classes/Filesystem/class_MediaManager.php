<?php
class MediaManager
{
    private $registry;
    private $renderer;
    private $filedir;

    private $basePath   = null;
    private $mediaBase  = null;
    private $protector  = '';
    private $prarentDir = '..';
    private $pathMask   = '#';

    private $symbolExt = array();

    public function __construct()
    {
        global $website, $renderer;

        $this -> registry = $website;
        $this -> renderer = $renderer;

        $this -> filedir  = new FileDir();

        $this -> mediaBase = $this -> registry -> config['Misc']['media_directory'];
        $this -> basePath  = $this -> registry -> config['Misc']['path'] . DS . $this -> mediaBase;

        $this -> protector = 'index.php';

        $this -> _initExtensionSymbols();
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> renderer);
    }

    public function getPathMask()
    {
        return $this -> pathMask;
    }

    public function showMediaManager()
    {
        $pathClick   = $this -> _getcurrentLinkedMediaPath();
        $pathContent = $this -> getContentFromPath($this -> basePath);

        $selectFileFilter = array();
        $jsSwitchArray    = array();

        $this -> _createFormElements($selectFileFilter, $jsSwitchArray);

        $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager.htm');
            $this -> renderer -> setVariable('current_path'           , $pathClick);
            $this -> renderer -> setVariable('current_sub_path'       , $this -> registry -> config['Misc']['media_directory']);
            $this -> renderer -> setVariable('current_path_content'   , $pathContent);
            $this -> renderer -> setVariable('current_select_types'   , $selectFileFilter);
            $this -> renderer -> setVariable('current_file_type_array', $jsSwitchArray);
        return $this -> renderer -> renderTemplate();
    }

    public function canCurrentFileUploaded($filename, $currentFilter)
    {
        $extension = substr($filename, strrpos($filename, '.') + 1);
        $inFilter  = strpos($currentFilter, $extension);

        if ( array_key_exists($extension, $this -> symbolExt) AND ($this -> symbolExt[$extension]['filter'] !== 'none') AND ($inFilter !== FALSE) ) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getContentFromPath($path)
    {
        $pathContent = $this -> _getCurrentDirectoryContent($path);
        $explorer    = array();

        if ( is_array($pathContent) AND count($pathContent) ) {
            foreach( $pathContent AS $item ) {
                if ( $item['type'] == 'file' ) {
                    if ( $item['name'] != $this -> protector ) {
                        // File can be deleted
                        $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_item_delete.htm');
                            $this -> renderer -> setVariable('file_name', $item['name']);
                        $deleteOption = $this -> renderer -> renderTemplate();
                        $specialType  = $this -> _getFontAwesomeSymbolForFile( substr($item['name'], strrpos($item['name'], '.') + 1) );
                        $noClickClass = '';
                    }
                    else {
                        // protected File
                        $deleteOption = '';
                        $specialType  = '-shield';
                        $noClickClass = ' item-no-click';
                    }

                    if ( $specialType == '-image' ) {
                        $relativePath = str_replace(APP_ROOT, '', $path);

                        $addImage = '<img src="' . $relativePath . DS . $item['name'] . '" />';
                    }
                    else {
                        $addImage = '';
                    }

                    $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_item.htm');
                        $this -> renderer -> setVariable('file_name'        , $item['name']);
                        $this -> renderer -> setVariable('file_size'        , $item['size']);
                        $this -> renderer -> setVariable('file_special_type', $specialType);
                        $this -> renderer -> setVariable('file_thumbnail'   , $addImage);
                        $this -> renderer -> setVariable('file_option'      , $deleteOption);
                        $this -> renderer -> setVariable('file_can_click'   , $noClickClass);
                    $explorer[] = $this -> renderer -> renderTemplate();
                }
                else {
                    if ( $item['size'] == 0 ) {
                        // Folder can be deleted
                        $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_folder_delete.htm');
                            $this -> renderer -> setVariable('sub_dir_name', $item['name']);
                        $deleteOption = $this -> renderer -> renderTemplate();
                    }
                    else {
                        $deleteOption = '';
                    }

                    $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_folder.htm');
                        $this -> renderer -> setVariable('folder_name'  , $item['name']);
                        $this -> renderer -> setVariable('folder_count' , $item['size']);
                        $this -> renderer -> setVariable('folder_option', $deleteOption);
                    $explorer[] = $this -> renderer -> renderTemplate();
                }
            }
        }
        else {
            $explorer[] = '<div>' . $this -> registry -> user_lang['admin']['media_manager_empty'] . '</div>';
        }

        return implode("\n", $explorer);
    }

    public function addNewDirectory($sourcePath, $newDirectory)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        $fullNewPath = $this -> _setNewMediaFullPath($sourcePath, $newDirectory);

        if ( is_writeable($this -> basePath . DS) ) {
            if ( !is_dir($fullNewPath) ) {
                $result = mkdir($fullNewPath, 0755, false);

                if ( $result === true ) {
                    $homeDir = str_replace(DS . $newDirectory, '', $fullNewPath);
                    $return['data'] = $this -> getContentFromPath($homeDir);
                }
                else {
                    $return['error']   = true;
                    $return['message'] = $this -> registry -> user_lang['global']['directory_error_2'];
                }
            }
            else {
                $return['error']   = true;
                $return['message'] = $this -> registry -> user_lang['global']['directory_error_1'];
            }
        }
        else {
            $return['error']   = true;
            $return['message'] = $this -> registry -> user_lang['global']['directory_error_0'];
        }

        return $return;
    }

    public function getGalleryPopup($subDirectory, $multiSelect)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null,
                  );

        $fullNewPath = $this -> _setNewMediaFullPath($subDirectory,'', false);
        $fullNewPath = str_replace(DS . DS, DS, $fullNewPath);

        $showParentDirectory = ( ($subDirectory == $this -> mediaBase) ? false : true );

        $pathContent = $this -> _getCurrentDirectoryContent($fullNewPath, $showParentDirectory);
        $explorer    = array();

        if ( $multiSelect == true ) {
            $subTemplate = '_multi';
        }
        else {
            $subTemplate = '_single';
        }

        if ( is_array($pathContent) AND count($pathContent) ) {
            foreach( $pathContent AS $item ) {
                if ( $item['name'] == $this -> protector ) {
                    // don't show index.php in selector
                    continue;
                }

                // set Click-Action
                if ( ($item['type'] == 'dir') AND ($item['name'] == $this -> prarentDir) ) {
                    $onClick = 'changeDirectory(true, \'\');';
                }
                elseif ( ($item['type'] == 'dir') AND ($item['name'] != $this -> prarentDir) ) {
                    $onClick = 'changeDirectory(false, \'' . $item['name'] . '\');';
                }
                else {
                    $onClick = '';
                }

                if ( strrpos($item['name'], '.') !== FALSE ) {
                    $symbol = $this -> _getFontAwesomeSymbolForFile( substr($item['name'], strrpos($item['name'], '.') + 1) );
                }
                else {
                    $symbol = '';
                }

                if ( $item['type'] == 'file' ) {
                    $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_item_popup' . $subTemplate . '.htm');
                        $this -> renderer -> setVariable('file_type'  , $symbol);
                        $this -> renderer -> setVariable('file_name'  , $item['name']);
                        $this -> renderer -> setVariable('file_data'  , $subDirectory . $this -> pathMask . $item['name']);
                    $explorer[] = $this -> renderer -> renderTemplate();
                }
                else {
                    $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_folder_popup.htm');
                        $this -> renderer -> setVariable('folder_name' , $item['name']);
                        $this -> renderer -> setVariable('folder_click', $onClick);
                    $explorer[] = $this -> renderer -> renderTemplate();
                }
            }
        }
        else {
            $explorer[] = '<div>' . $this -> registry -> user_lang['admin']['media_manager_empty'] . '</div>';
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_footer_popup.htm');
        $footer = $this -> renderer -> renderTemplate();

        $return['data']['items']  = implode("\n", $explorer);
        $return['data']['footer'] = $footer;

        return $return;
    }

    public function addImagesToGallery($jsonData)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        $jsonArray = json_decode( html_entity_decode($jsonData), true );
        $itemList  = array();

        if ( is_array($jsonArray) and count($jsonArray) ) {
            foreach( $jsonArray AS $id => $item ) {
                if ( is_string($item) ) {
                    $data = array(
                                'imageId'    => '',
                                'imagePath'  => $item,
                                'imageTitle' => '',
                                'imageDescr' => '',
                            );
                }
                else {
                    $data = $item;
                }

                $fullFileName = $this -> registry -> config['Misc']['path'] . DS . str_replace($this -> pathMask, DS, $data['imagePath']);

                if ( is_file($fullFileName) ) {
                    $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_item.htm');
                        $this -> renderer -> setVariable('gallery_item_position'   , $data['imageId']);
                        $this -> renderer -> setVariable('gallery_item_img_url'    , str_replace($this -> pathMask, DS, $data['imagePath']));
                        $this -> renderer -> setVariable('gallery_item_img_path'   , $data['imagePath']);
                        $this -> renderer -> setVariable('gallery_item_title'      , $data['imageTitle']);
                        $this -> renderer -> setVariable('gallery_item_description', $data['imageDescr']);
                    $itemList[] = $this -> renderer -> renderTemplate();
                }
            }
        }
        else {
            $itemList[] = '<li>' . $this -> registry -> user_lang['admin']['media_manager_empty'] . '</li>';
        }

        $return['data'] = implode("\n", $itemList);

        return $return;
    }

    public function changeDirectory($sourcePath, $subDirectory, $parentDirectory = false)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        $fullNewPath = $this -> _setNewMediaFullPath($sourcePath, $subDirectory, $parentDirectory);

        if ( is_dir($fullNewPath) ) {
            if ( $parentDirectory == true ) {
                $toolbarPath = substr($sourcePath, 0, strpos($sourcePath, $subDirectory) + strlen($subDirectory));
            }
            else {
                $toolbarPath = $sourcePath . $this -> pathMask . $subDirectory;
            }

            $return['data']['html'] = $this -> getContentFromPath($fullNewPath);
            $return['data']['path'] = $toolbarPath;
            $return['data']['nav']  = $this -> _getcurrentLinkedMediaPath($toolbarPath);
        }
        else {
            $return['error']   = true;
            $return['message'] = $this -> registry -> user_lang['global']['directory_error_3'];
        }

        return $return;
    }

    public function deleteDirectory($sourcePath, $deleteDirectory)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        $fullDeletePath = $this -> _setNewMediaFullPath($sourcePath, $deleteDirectory);

        if ( is_writeable($this -> basePath . DS) ) {
            if ( is_dir($fullDeletePath) ) {
                $result = rmdir($fullDeletePath);

                if ( $result === true ) {
                    $homeDir = str_replace(DS . $deleteDirectory, '', $fullDeletePath);
                    $return['data'] = $this -> getContentFromPath($homeDir);
                }
                else {
                    $return['error']   = true;
                    $return['message'] = $this -> registry -> user_lang['global']['directory_error_4'];
                }
            }
            else {
                $return['error']   = true;
                $return['message'] = $this -> registry -> user_lang['global']['directory_error_3'];
            }
        }
        else {
            $return['error']   = true;
            $return['message'] = $this -> registry -> user_lang['global']['directory_error_0'];
        }

        return $return;
    }

    public function deleteFileFromDirectory($sourcePath, $deleteFilename)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        $fullDestinationPath = $this -> getDestinationMediaPath($sourcePath);

        if ( is_writeable($fullDestinationPath) ) {
            $fullDestinationFile = $fullDestinationPath . DS . $deleteFilename;

            if ( is_file($fullDestinationFile) ) {
                if ( is_writable($fullDestinationFile) ) {
                    $result = unlink($fullDestinationFile);

                    if ( $result == true ) {
                        $return['data'] = $this -> getContentFromPath($sourcePath);
                    }
                    else {
                        $return['error']   = true;
                        $return['message'] = $this -> registry -> user_lang['global']['file_error_2'];
                    }
                }
                else {
                    $return['error']   = true;
                    $return['message'] = $this -> registry -> user_lang['global']['file_error_1'];
                }
            }
            else {
                $return['error']   = true;
                $return['message'] = $this -> registry -> user_lang['global']['file_error_0'];
            }
        }
        else {
            $return['error']   = true;
            $return['message'] = $this -> registry -> user_lang['global']['directory_error_0'];
        }


        return $return;
    }

    public function getDestinationMediaPath($subDiretory)
    {
        if ( strpos($subDiretory, $this -> pathMask) !== FALSE ) {
            $subDiretory = str_replace($this -> pathMask, DS, $subDiretory);
        }

        $subDiretory = str_replace(array('.', ' ', '\\'), array('', '_', DS), $subDiretory);
        $subDiretory = str_replace($this -> registry -> config['Misc']['media_directory'], '', $subDiretory);
        $subDiretory = str_replace(DS . DS, '', $subDiretory);

        return $this -> basePath . $subDiretory;
    }



    private function _setNewMediaFullPath($sourcePath, $subDiretory, $isParent = false)
    {
        if ( strpos($sourcePath, $this -> pathMask) !== FALSE ) {
            $sourcePath = str_replace($this -> pathMask, DS, $sourcePath);
        }

        if ( strlen($subDiretory) ) {
            $subDiretory = str_replace(array('.', ' ', '\\'), array('', '_', DS), $subDiretory);
        }

        if ( $isParent == true ) {
            $relativePath = substr($sourcePath, 0, strpos($sourcePath, $subDiretory) + strlen($subDiretory));
        }
        else {
            $relativePath = $sourcePath . DS . $subDiretory;
        }

        $relativePath = str_replace($this -> registry -> config['Misc']['media_directory'], '', $relativePath);
        $relativePath = str_replace(DS . DS, '', $relativePath);

        return $this -> basePath . DS . $relativePath;
    }

    private function _getcurrentLinkedMediaPath($directoryPath = '')
    {
        $html_data = array();

        $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_path_item.htm');
            $this -> renderer -> setVariable('directory_name'  , $this -> registry -> config['Misc']['media_directory']);
            $this -> renderer -> setVariable('directory_parent', (strlen($directoryPath) ? ', true' : ''));
            $this -> renderer -> setVariable('directory_class' , '');
        $html_data[] = $this -> renderer -> renderTemplate();

        if ( strlen($directoryPath) AND ($directoryPath != $this -> registry -> config['Misc']['media_directory']) ) {
            $directoryPath = str_replace($this -> registry -> config['Misc']['media_directory'] . $this -> pathMask, '', $directoryPath);

            if ( strpos($directoryPath, $this -> pathMask) !== FALSE ) {
                $dirList = explode($this -> pathMask, $directoryPath);
                $last = sizeof($dirList);

                foreach( $dirList AS $key => $directory ) {
                    $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_path_item.htm');
                        $this -> renderer -> setVariable('directory_name'  , $directory);
                        $this -> renderer -> setVariable('directory_parent', (($key == $last) ? '' : ', true'));
                        $this -> renderer -> setVariable('directory_class' , '');
                    $html_data[] = $this -> renderer -> renderTemplate();
                }
            }
            else {
                $this -> renderer -> loadTemplate('admin' . DS . 'media' . DS . 'manager_path_item.htm');
                    $this -> renderer -> setVariable('directory_name'  , $directoryPath);
                    $this -> renderer -> setVariable('directory_parent', '');
                    $this -> renderer -> setVariable('directory_class' , '');
                $html_data[] = $this -> renderer -> renderTemplate();
            }
        }

        return implode("\n", $html_data);
    }

    private function _getCurrentDirectoryContent($directoryPath, $ShowParent = false)
    {
        if ( strlen($directoryPath) ) {
            if ( strpos($directoryPath, $this -> pathMask) !== FALSE ) {
                $directoryPath = str_replace($this -> pathMask, DS, $directoryPath);
            }
        }
        else {
            $directoryPath = $this -> basePath;
        }

        if ( $directoryPath[-1] !== DS ) {
            $directoryPath .= DS;
        }

        return $this -> filedir -> get_dir_content($directoryPath, $ShowParent);
    }

    private function _getFontAwesomeSymbolForFile($extension)
    {
        if ( array_key_exists($extension, $this -> symbolExt) ) {
            return $this -> symbolExt[$extension]['symbol'];
        }
        else {
            return '';
        }
    }

    private function _createFormElements(&$selectOptions, &$jsArray)
    {
        $last = '';
        $ext  = array();

        foreach( $this -> symbolExt AS $extension => $details ) {
            if ( ($details['filter'] != 'none') AND ($details['filter'] != $last) ) {
                $title = $this -> registry -> user_lang['admin']['media_manager_filter_' . $details['filter']];

                $selectOptions[] = '<option value="' . $details['filter'] . '">' . $title . '</option>';

                if ( strlen($last) AND count($ext) ) {
                    $jsArray[] = $last . ':"' . implode(',', $ext) . '"';
                }

                $ext  = array();
                $last = $details['filter'];
            }
            if ( $details['filter'] != 'none' ) {
                $ext[] = '.' . $extension;
            }
        }

        if ( strlen($last) AND count($ext) ) {
            $jsArray[] = $last . ':"' . implode(',', $ext) . '"';
        }

        $selectOptions = implode("", $selectOptions);
        $jsArray = '{' . implode(",", $jsArray) . '}';
    }

    private function _initExtensionSymbols()
    {
        $this -> symbolExt = array(
                                 'jpg'  => array('symbol' => '-image', 'filter' => 'image'),
                                 'jepg' => array('symbol' => '-image', 'filter' => 'image'),
                                 'png'  => array('symbol' => '-image', 'filter' => 'image'),
                                 'gif'  => array('symbol' => '-image', 'filter' => 'image'),
                                 'bmp'  => array('symbol' => '-image', 'filter' => 'image'),

                                 'zip'  => array('symbol' => '-zipper', 'filter' => 'archive'),
                                 'rar'  => array('symbol' => '-zipper', 'filter' => 'archive'),
                                 'tar'  => array('symbol' => '-zipper', 'filter' => 'archive'),
                                 'gz'   => array('symbol' => '-zipper', 'filter' => 'archive'),
                                 '7z'   => array('symbol' => '-zipper', 'filter' => 'archive'),

                                 'doc'  => array('symbol' => '-word'      , 'filter' => 'documents'),
                                 'docx' => array('symbol' => '-word'      , 'filter' => 'documents'),
                                 'xls'  => array('symbol' => '-excel'     , 'filter' => 'documents'),
                                 'xlsx' => array('symbol' => '-excel'     , 'filter' => 'documents'),
                                 'ppt'  => array('symbol' => '-powerpoint', 'filter' => 'documents'),
                                 'pptx' => array('symbol' => '-powerpoint', 'filter' => 'documents'),

                                 'csv'  => array('symbol' => '-csv', 'filter' => 'documents'),

                                 'pdf'  => array('symbol' => '-pdf', 'filter' => 'documents'),

                                 'php'  => array('symbol' => '-code', 'filter' => 'none'),
                                 'js'   => array('symbol' => '-code', 'filter' => 'none'),
                                 'htm'  => array('symbol' => '-code', 'filter' => 'none'),
                                 'html' => array('symbol' => '-code', 'filter' => 'none'),
                                 'sh'   => array('symbol' => '-code', 'filter' => 'none'),
                                 'py'   => array('symbol' => '-code', 'filter' => 'none'),
                             );
    }
}
