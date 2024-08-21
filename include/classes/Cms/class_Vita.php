<?php
class Vita
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

    public function showVitaList()
    {
        $data = array();

        $lastId = 0;

        $query = 'SELECT * FROM `vita` ORDER BY `vita_position` ASC;';
        $vitaData = $this -> registry -> db -> queryObjectArray($query);

        if ( is_array($vitaData) AND count($vitaData[0]) ) {
            foreach( $vitaData AS $vitaElement ) {
                $this -> renderer -> loadTemplate('admin' . DS . 'vita' . DS . 'item.htm');
                    $this -> renderer -> setVariable('vita_class'     , ($vitaElement['vita_enable'] ? '' : ' item-disable') );
                    $this -> renderer -> setVariable('vita_id'        , $vitaElement['vita_id']);
                    $this -> renderer -> setVariable('vita_position'  , $vitaElement['vita_position']);
                    $this -> renderer -> setVariable('vita_title'     , $vitaElement['vita_title']);
                    $this -> renderer -> setVariable('vita_decription', $vitaElement['vita_text']);
                    $this -> renderer -> setVariable('vita_enable'    , ($vitaElement['vita_enable'] ? 'true' : 'false') );
                    $this -> renderer -> setVariable('vita_image'     , $vitaElement['vita_image']);
                $data[] = $this -> renderer -> renderTemplate();

                $lastId = $vitaElement['vita_id'];
            }
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'vita' . DS . 'item.htm');
            $this -> renderer -> setVariable('vita_class'     , '');
            $this -> renderer -> setVariable('vita_id'        , '{{id}}');
            $this -> renderer -> setVariable('vita_position'  , '{{position}}');
            $this -> renderer -> setVariable('vita_title'     , '{{title}}');
            $this -> renderer -> setVariable('vita_decription', '{{decription}}');
            $this -> renderer -> setVariable('vita_enable'    , '{{enable}}');
            $this -> renderer -> setVariable('vita_image'     , '{{image}}');
        $template = $this -> renderer -> renderTemplate();

        $this -> renderer -> loadTemplate('admin' . DS . 'vita' . DS . 'page.htm');
            $this -> renderer -> setVariable('vita_count'   , count($data));
            $this -> renderer -> setVariable('vita_last'    , $lastId);
            $this -> renderer -> setVariable('vita_list'    , implode("\n                ", $data));
            $this -> renderer -> setVariable('vita_template', $template);

            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
        return $this -> renderer -> renderTemplate();
    }

    public function saveVitaElements($vitaData)
    {
        $countOk   = 0;
        $countFail = 0;

        $this -> registry -> db -> execute('TRUNCATE TABLE `vita`;');

        if ( is_array($vitaData) AND count($vitaData) ) {
            foreach( $vitaData AS $vitaElement ) {
                $insertResult = $this -> registry -> db -> insertRow($vitaElement, 'vita');

                if ( $insertResult == false ) {
                    $countFail++;
                }
                else {
                    $countOk++;
                }
            }

            if ( $countOk == count($vitaData) ) {
                // success
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                    $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_vita_message_successfuly_saved']);
                    $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_vita');
                return $this -> renderer -> renderTemplate();
            }
            else {
                // insert-errors
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_vita_message_save_error']);
                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            // empoty data or all elements deletet
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_vita_message_data_empty']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_vita');
            return $this -> renderer -> renderTemplate();
        }
    }
}