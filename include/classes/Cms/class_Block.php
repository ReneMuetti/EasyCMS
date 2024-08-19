<?php
class Block
{
    private $registry;
    private $renderer;

    public function __construct()
    {
        global $website, $renderer;

        $this -> registry = $website;
        $this -> renderer = $renderer;
    }

    public function __destruct()
    {
        unset($this -> registry);
        unset($this -> renderer);
    }

    public function getCurrentBlocks()
    {
        $query = 'SELECT * FROM `blocks` ORDER BY `block_id` ASC;';
        $blockData = $this -> registry -> db -> queryObjectArray($query);

        $data = array();
        if ( is_array($blockData) AND count($blockData[0]) ) {
            foreach($blockData AS $item) {
                $enable = ($item['block_enable'] ? 'yes' : 'no');

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_list_line.htm');
                    $this -> renderer -> setVariable('cms_block_id'           , $item['block_id']);
                    $this -> renderer -> setVariable('cms_block_title'        , $item['block_title']);
                    $this -> renderer -> setVariable('cms_block_edit_username', $item['username']);
                    $this -> renderer -> setVariable('cms_block_edit_time'    , $item['datetime']);
                    $this -> renderer -> setVariable('cms_block_raw_enabled'  , ($enable == 'yes') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('cms_block_enabled'      , $this -> registry -> user_lang['global']['status_' . $enable]);
                $data[] = $this -> renderer -> renderTemplate();
            }
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_list_empty.htm');
            $data[] = $this -> renderer -> renderTemplate();
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_list.htm');
            $this -> renderer -> setVariable('admin_table_cms_block_list', implode("\n", $data));
        return $this -> renderer -> renderTemplate();
    }

    public function loadBlockForEdit($blockId = 0, $formAction = 'saveblock')
    {
        $manager = new MediaManager();

        if ( ($formAction == 'updateblock') AND ($blockId >= 1) ) {
            // load block for edit
            $blockData = $this -> registry -> db -> querySingleArray('SELECT * FROM `blocks` WHERE `block_id` = ' . $blockId);

            if ( is_array($blockData) AND count($blockData) ) {
                $blockData['block_content'] = $this -> _removeEscapeFromContent($blockData['block_content']);

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_editor.htm');
                    $this -> renderer -> setVariable('cms_block_id'       , $blockId);
                    $this -> renderer -> setVariable('cms_form_action'    , $formAction);
                    $this -> renderer -> setVariable('cms_block_title'    , $blockData['block_title']);
                    $this -> renderer -> setVariable('cms_block_content'  , stripcslashes($blockData['block_content']));
                    $this -> renderer -> setVariable('cms_block_enable'   , $blockData['block_enable'] ? 'checked' : '' );
                    $this -> renderer -> setVariable('popup_current_path' , $this -> registry -> config['Misc']['media_directory']);
                    $this -> renderer -> setVariable('image_path_mask'    , $manager -> getPathMask());
                    $this -> renderer -> setVariable('summernote_language', $this -> registry -> user_config['language_code']);

                    $this -> renderer -> addCustonStyle(array('script' => 'skin/css/bootstrap.min.css'), THIS_SCRIPT);
                    $this -> renderer -> addCustonStyle(array('script' => 'skin/js/summernote/summernote.min.css'), THIS_SCRIPT);
                    $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
                return $this -> renderer -> renderTemplate();
            }
            else {
                // block not loaded
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_block_load_fail']);
                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            // create new block
            $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_editor.htm');
                $this -> renderer -> setVariable('cms_block_id'       , $blockId);
                $this -> renderer -> setVariable('cms_form_action'    , $formAction);
                $this -> renderer -> setVariable('cms_block_title'    , '');
                $this -> renderer -> setVariable('cms_block_content'  , '');
                $this -> renderer -> setVariable('cms_block_enable'   , '');
                $this -> renderer -> setVariable('popup_current_path' , $this -> registry -> config['Misc']['media_directory']);
                $this -> renderer -> setVariable('image_path_mask'    , $manager -> getPathMask());
                $this -> renderer -> setVariable('summernote_language', $this -> registry -> user_config['language_code']);

                $this -> renderer -> addCustonStyle(array('script' => 'skin/css/bootstrap.min.css'), THIS_SCRIPT);
                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/summernote/summernote.min.css'), THIS_SCRIPT);
                $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function saveBlock($method, $blockId, $blockTitle, $blockContent, $blockEnable)
    {
        $sqlData = array(
                       'block_title'   => $blockTitle,
                       'block_content' => $blockContent,
                       'block_enable'  => $blockEnable,
                       'username'      => $this -> registry -> userinfo['username'],
                   );
        if ( ($method == 'new') AND ($blockId == 0) ) {
            // save new block
            $result = $this -> registry -> db -> insertRow($sqlData, 'blocks');
        }
        else {
            // update existing block
            $result = $this -> registry -> db -> updateRow($sqlData, 'blocks', 'WHERE `block_id` = ' . $blockId);
        }

        if ( $result == true ) {
            // success
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_block_save_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_blocks');
            return $this -> renderer -> renderTemplate();
        }
        else {
            // Error
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_block_save_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function deleteBlockById($blockId = 0)
    {
        if ( $blockId >= 1 ) {
            $query  = 'DELETE FROM `blocks` WHERE `block_id` = ' . $blockId;
            $this -> registry -> db -> execute($query);

            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_block_delete_success']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_blocks');
            return $this -> renderer -> renderTemplate();
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_block_delete_fail']);
            return $this -> renderer -> renderTemplate();
        }
    }

    public function getNewBlockForCmsPage($blockPrefix, $blockNumber)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_template.htm');
            $this -> renderer -> setVariable('block_prefix', $blockPrefix);
            $this -> renderer -> setVariable('block_number', $blockNumber);
        return $this -> renderer -> renderTemplate();
    }

    public function getBlockForCmsPageWithConfigData($blockData)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_template_full.htm');
            $this -> renderer -> setVariable('block_id'           , $blockData['id']);
            $this -> renderer -> setVariable('block_number'       , $blockData['b_id']);
            $this -> renderer -> setVariable('block_content_type' , $blockData['c_type']);
            $this -> renderer -> setVariable('block_content_id'   , $blockData['c_id']);
            $this -> renderer -> setVariable('block_col'          , $blockData['col']);
            $this -> renderer -> setVariable('block_row'          , $blockData['row']);
            $this -> renderer -> setVariable('block_size_x'       , $blockData['size_x']);
            $this -> renderer -> setVariable('block_size_y'       , $blockData['size_y']);
            $this -> renderer -> setVariable('block_content_title', $blockData['c_title']);
        return $this -> renderer -> renderTemplate();
    }

    public function getAllBlocksForLayout()
    {
        $html = array();

        // load all Modules
        $modulePath = realpath('include/classes/Modules/');
        $files = new FileDir($modulePath);
        $modules = $files -> getFileList('Modules', false);

        if ( is_array($modules) AND count($modules) ) {
            $html[] = '<h2>' . $this -> registry -> user_lang['admin']['cms_modules_section'] . '</h2>';

            foreach( $modules AS $classFile ) {
                $fileName  = basename($classFile);
                $className = substr($fileName, 6, -4);
                $moduleClass = new $className();
                $moduleIdent = $moduleClass -> getModuleName();

                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_template_popup.htm');
                    $this -> renderer -> setVariable('cms_block_number', $moduleClass -> getModuleId());
                    $this -> renderer -> setVariable('cms_block_title' , $this -> registry -> user_lang['admin']['cms_modules_' . $moduleIdent]);
                    $this -> renderer -> setVariable('cms_block_type'  , 'module');
                $html[] = $this -> renderer -> renderTemplate();
            }
        }


        // load all enabled CMS-Blocks
        $query = 'SELECT `block_id`, `block_title` FROM `blocks` WHERE `block_enable` = 1 ORDER BY `block_title` ASC;';
        $data  = $this -> registry -> db -> queryObjectArray($query);

        if ( is_array($data) AND count($data[0]) ) {
            $html[] = '<h2>' . $this -> registry -> user_lang['admin']['cms_block_section'] . '</h2>';

            foreach( $data AS $block ) {
                $this -> renderer -> loadTemplate('admin' . DS . 'cms' . DS . 'block_template_popup.htm');
                    $this -> renderer -> setVariable('cms_block_number', $block['block_id']);
                    $this -> renderer -> setVariable('cms_block_title' , $block['block_title']);
                    $this -> renderer -> setVariable('cms_block_type'  , 'block');
                $html[] = $this -> renderer -> renderTemplate();
            }
        }

        if ( count($html) ) {
            return array(
                       'error'   => false,
                       'message' => '',
                       'data'    => implode("\n", $html),
                   );

        }
        else {
            return array(
                       'error'   => true,
                       'message' => $this -> registry -> user_lang['admin']['table_cms_block_empty'],
                       'data'    => null,
                   );
        }
    }



    private function _removeEscapeFromContent($string)
    {
        $string = str_replace("\\r", "", $string);
        $string = stripslashes($string);
        $string = str_replace(
                      array('font-family: &quot;', '&quot;;'),
                      array('font-family: \'', '\';'),
                      $string
                  );

        return $string;
    }
}