<?php
class Gallery
{
    private $registry;
    private $renderer;

    private $types = array();

    public function __construct()
    {
        global $website, $renderer;

        $this -> registry = $website;
        $this -> renderer = $renderer;

        $this -> types = array(
                             1 => array(
                                      'name' => $this -> registry -> user_lang['admin']['cms_gallery_type_blocks'],
                                      'type' => 'blocks'
                                  ),
                             2 => array(
                                      'name' => $this -> registry -> user_lang['admin']['cms_gallery_type_splide'],
                                      'type' => 'splide'
                                  ),
                         );
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> renderer);
    }

    public function getGalleryOptionTemplate($selectIndex, $configData = null)
    {
        $return = array(
                      'error'   => false,
                      'message' => '',
                      'data'    => null
                  );

        if ( array_key_exists($selectIndex, $this -> types) ) {
            if ( is_null($configData) OR !strlen($configData) ) {
                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_option_' . $this -> types[$selectIndex]['type'] . '.htm');
                    $this -> renderer -> setVariable('gallery_blocks_show_title'    , '');
                    $this -> renderer -> setVariable('gallery_blocks_show_descr'    , '');
                    $this -> renderer -> setVariable('gallery_option_type'          , $this -> types[$selectIndex]['type']);
                    $this -> renderer -> setVariable('gallery_blocks_items_per_line', '');
                $return['data'] = $this -> renderer -> renderTemplate();
            }
            else {
                $jsonData = json_decode( html_entity_decode($configData), true );

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_option_' . $this -> types[$selectIndex]['type'] . '.htm');
                    $this -> renderer -> setVariable('gallery_option_type', $this -> types[$selectIndex]['type']);

                if ( $selectIndex == 1 ) {
                    // blocks
                    $this -> renderer -> setVariable('gallery_blocks_show_title'    , $jsonData['showTitle'] ? 'checked' : '' );
                    $this -> renderer -> setVariable('gallery_blocks_show_descr'    , $jsonData['showDescr'] ? 'checked' : '' );
                    $this -> renderer -> setVariable('gallery_option_type'          , $this -> types[$selectIndex]['type']);
                    $this -> renderer -> setVariable('gallery_blocks_items_per_line', ' data-items-per-line="' . $jsonData['perLine'] . '"');
                }
                elseif( $selectIndex == 2 ) {
                    //splide
                }

                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            $return['error']   = true;
            $return['message'] = $this -> registry -> user_lang['admin']['cms_gallery_type_not_present'];
        }

        return $return;
    }

    public function getCurrentGalleryList()
    {
        $query = 'SELECT `gallery_id`, `gallery_title`, `gallery_type`, `gallery_enable`, `username`, `datetime` FROM `gallery` ORDER BY `gallery_id` ASC;';
        $galleryData = $this -> registry -> db -> queryObjectArray($query);

        $data = array();
        if ( is_array($galleryData) AND count($galleryData[0]) ) {
            foreach($galleryData AS $item) {
                $enable = ($item['gallery_enable'] ? 'yes' : 'no');
                $type   = $this -> types[$item['gallery_type']]['type'];

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_list_line.htm');
                    $this -> renderer -> setVariable('cms_gallery_id'           , $item['gallery_id']);
                    $this -> renderer -> setVariable('cms_gallery_title'        , $item['gallery_title']);
                    $this -> renderer -> setVariable('cms_gallery_type'         , $type);
                    $this -> renderer -> setVariable('cms_gallery_edit_username', $item['username']);
                    $this -> renderer -> setVariable('cms_gallery_edit_time'    , $item['datetime']);
                    $this -> renderer -> setVariable('cms_gallery_raw_enabled'  , ($enable == 'yes') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('cms_gallery_enabled'      , $this -> registry -> user_lang['global']['status_' . $enable]);
                $data[] = $this -> renderer -> renderTemplate();
            }
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_list_empty.htm');
            $data[] = $this -> renderer -> renderTemplate();
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_list.htm');
            $this -> renderer -> setVariable('admin_table_cms_gallery_list', implode("\n", $data));
        return $this -> renderer -> renderTemplate();
    }

    public function loadGalleryForEdit($galleryId = 0, $formAction = 'savegallery')
    {
        $manager = new MediaManager();

        if ( ($formAction == 'updategallery') AND ($galleryId >= 1) ) {
            // edit existing gallery
            $galleryData = $this -> registry -> db -> querySingleArray('SELECT * FROM `gallery` WHERE `gallery_id` = ' . $galleryId);

            if ( is_array($galleryData) AND count($galleryData) ) {
                if ( strlen($galleryData['gallery_options']) ) {
                    $galleryOptionFields = $this -> getGalleryOptionTemplate($galleryData['gallery_type'], $galleryData['gallery_options']);
                }
                else {
                    $galleryOptionFields = '';
                }

                if ( strlen($galleryData['gallery_images']) ) {
                    $imageLoader = new MediaManager();
                    $loaderData  = $imageLoader -> addImagesToGallery($galleryData['gallery_images']);
                    $galleryImageList = $loaderData['data'];
                }
                else {
                    $galleryImageList = '';
                }

                // load gallery for edit
                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_editor.htm');
                    $this -> renderer -> setVariable('cms_form_action'           , $formAction);
                    $this -> renderer -> setVariable('cms_gallery_id'            , $galleryId);
                    $this -> renderer -> setVariable('cms_gallery_default_path'  , $this -> registry -> config['Misc']['media_directory']);
                    $this -> renderer -> setVariable('cms_gallery_config'        , html_entity_decode($galleryData['gallery_options']) );
                    $this -> renderer -> setVariable('cms_gallery_image_data'    , html_entity_decode($galleryData['gallery_images']) );
                    $this -> renderer -> setVariable('cms_gallery_title'         , $galleryData['gallery_title']);
                    $this -> renderer -> setVariable('cms_gallery_types'         , $this -> _getGalleryTypes($galleryData['gallery_type']));
                    $this -> renderer -> setVariable('cms_gallery_enable'        , $galleryData['gallery_enable'] ? 'checked' : '');
                    $this -> renderer -> setVariable('cms_gallery_option_fields' , $galleryOptionFields);
                    $this -> renderer -> setVariable('cms_gallery_image_list'    , $galleryImageList);
                    $this -> renderer -> setVariable('cms_gallery_loading_action', 'finishGalleryConfig();');

                    $this -> renderer -> setVariable('cms_gallery_path_mask', $manager -> getPathMask());

                    $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
                return $this -> renderer -> renderTemplate();
            }
            else {
                // gallery not loaded
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_gallery_load_fail']);
                return $this -> renderer -> renderTemplate();
            }
;
        }
        else {
            // new gallery
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'gallery_editor.htm');
                $this -> renderer -> setVariable('cms_form_action'           , $formAction);
                $this -> renderer -> setVariable('cms_gallery_id'            , $galleryId);
                $this -> renderer -> setVariable('cms_gallery_default_path'  , $this -> registry -> config['Misc']['media_directory']);
                $this -> renderer -> setVariable('cms_gallery_config'        , '');
                $this -> renderer -> setVariable('cms_gallery_image_data'    , '');
                $this -> renderer -> setVariable('cms_gallery_title'         , '');
                $this -> renderer -> setVariable('cms_gallery_types'         , $this -> _getGalleryTypes(0));
                $this -> renderer -> setVariable('cms_gallery_enable'        , 'checked');
                $this -> renderer -> setVariable('cms_gallery_option_fields' , '');
                $this -> renderer -> setVariable('cms_gallery_image_list'    , '');
                $this -> renderer -> setVariable('cms_gallery_loading_action', '');

                $this -> renderer -> setVariable('cms_gallery_path_mask', $manager -> getPathMask());

                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function saveGallery($method, $galleryId, $galleryTitle, $galleryConfig, $galleryImages, $galleryType, $isEnable)
    {
        $sqlData = array(
                       "gallery_title"   => $galleryTitle,
                       "gallery_type"    => $galleryType,
                       "gallery_options" => $galleryConfig,
                       "gallery_images"  => $galleryImages,
                       "gallery_enable"  => $isEnable,
                       "username"        => $this -> registry -> userinfo['username'],
                   );

        if ( ($method == 'new') AND ($galleryId == 0) ) {
            // save new gallery
            $result = $this -> registry -> db -> insertRow($sqlData, 'gallery');
        }
        else {
            // update existing gallery
            $result = $this -> registry -> db -> updateRow($sqlData, 'gallery', 'WHERE `gallery_id` = ' . $galleryId);
        }

        if ( $result == true ) {
            // success
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_gallery_save_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=content_gallery');
            return $this -> renderer -> renderTemplate();
        }
        else {
            // Error
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_gallery_save_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function deleteGalleryById($galleryId = 0)
    {
        if ( $galleryId >= 1 ) {
            $query  = 'DELETE FROM `gallery` WHERE `gallery_id` = ' . $galleryId;
            $this -> registry -> db -> execute($query);

            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_page_delete_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=content_gallery');
            return $this -> renderer -> renderTemplate();
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_page_delete_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }


    private function _getGalleryTypes($selectType)
    {
        $_selected = ' selected="selected"';

        $options = array();

        $options[] = '<option value="0"' . ($selectType == 0 ? $_selected : '') . '>' . $this -> registry -> user_lang['global']['option_actions_select'] . '</option>';

        foreach( $this -> types AS $id => $type ) {
            $options[] = '<option value="' . $id . '"' . ($selectType == $id ? $_selected : '') . '>' . $type['name'] . '</option>';
        }

        return implode("\n", $options);
    }
}