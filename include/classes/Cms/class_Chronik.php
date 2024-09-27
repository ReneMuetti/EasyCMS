<?php
class Chronik
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

    public function showChronikList()
    {
        $data = array();

        $lastId = 0;

        $query = 'SELECT * FROM `chronik` ORDER BY `chronik_position` ASC;';
        $chronikData = $this -> registry -> db -> queryObjectArray($query);

        if ( is_array($chronikData) AND count($chronikData[0]) ) {
            foreach( $chronikData AS $chronikElement ) {
                $chronikStringLine = str_replace(
                                         array('<br />\r', '<br />',"\\n", '\n', '&amp;amp;'),
                                         array('{{rn}}'  , '{{rn}}', ''  , ''  , '&amp;'),
                                         $chronikElement['chronik_text']
                                     );

                $chronikElement['chronik_text'] = str_replace(
                                                      array('<br />\r', '<br />',"\\n", '\n', '&amp;amp;'),
                                                      array(''        , ''      , ''  , ''  , '&amp;'),
                                                      $chronikElement['chronik_text']
                                                  );

                $this -> renderer -> loadTemplate('admin' . DS . 'chronik' . DS . 'item.htm');
                    $this -> renderer -> setVariable('chronik_class'     , ($chronikElement['chronik_enable'] ? '' : ' item-disable') );
                    $this -> renderer -> setVariable('chronik_id'        , $chronikElement['chronik_id']);
                    $this -> renderer -> setVariable('chronik_position'  , $chronikElement['chronik_position']);
                    $this -> renderer -> setVariable('chronik_title'     , $chronikElement['chronik_title']);
                    $this -> renderer -> setVariable('chronik_hint'      , $chronikStringLine);
                    $this -> renderer -> setVariable('chronik_content'   , $chronikStringLine);
                    $this -> renderer -> setVariable('chronik_enable'    , ($chronikElement['chronik_enable'] ? 'true' : 'false') );
                $data[] = $this -> renderer -> renderTemplate();

                $lastId = $chronikElement['chronik_id'];
            }
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'chronik' . DS . 'item.htm');
            $this -> renderer -> setVariable('chronik_class'   , '');
            $this -> renderer -> setVariable('chronik_id'      , '{{id}}');
            $this -> renderer -> setVariable('chronik_position', '{{position}}');
            $this -> renderer -> setVariable('chronik_title'   , '{{title}}');
            $this -> renderer -> setVariable('chronik_content' , '{{content}}');
            $this -> renderer -> setVariable('chronik_hint'    , '');
            $this -> renderer -> setVariable('chronik_enable'  , '{{enable}}');
        $template = $this -> renderer -> renderTemplate();

        $this -> renderer -> loadTemplate('admin' . DS . 'chronik' . DS . 'page.htm');
            $this -> renderer -> setVariable('chronik_count'   , count($data));
            $this -> renderer -> setVariable('chronik_last'    , $lastId);
            $this -> renderer -> setVariable('chronik_list'    , implode("\n                ", $data));
            $this -> renderer -> setVariable('chronik_template', $template);

            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
        return $this -> renderer -> renderTemplate();
    }

    public function saveChronikElements($chronikData)
    {
        $countOk   = 0;
        $countFail = 0;

        $this -> registry -> db -> execute('TRUNCATE TABLE `chronik`;');

        if ( is_array($chronikData) AND count($chronikData) ) {
            foreach( $chronikData AS $chronikElement ) {
                $insertResult = $this -> registry -> db -> insertRow($chronikElement, 'chronik');

                if ( $insertResult == false ) {
                    $countFail++;
                }
                else {
                    $countOk++;
                }
            }

            if ( $countOk == count($chronikData) ) {
                // success
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                    $this -> renderer -> setVariable('success_message' , $this -> registry -> user_lang['admin']['cms_chronik_message_successfuly_saved']);
                    $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_chronik');
                return $this -> renderer -> renderTemplate();
            }
            else {
                // insert-errors
                $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
                    $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_chronik_message_save_error']);
                return $this -> renderer -> renderTemplate();
            }
        }
        else {
            // empoty data or all elements deletet
            $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
                $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_chronik_message_data_empty']);
                $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_chronik');
            return $this -> renderer -> renderTemplate();
        }
    }
}