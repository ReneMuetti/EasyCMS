<?php
class ChronikFrontend
{
    private $moduleName = 'chronik_frontend';
    private $moduleId   = 10020;

    private $active = true;

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

    public function setActiveState($state)
    {
        if ( $state == true ) {
            $this -> active = true;
        }
        else {
            $this -> active = false;
        }
    }

    public function getModuleName()
    {
        return $this -> moduleName;
    }

    public function getModuleId()
    {
        return $this -> moduleId;
    }

    public function getFrontendBlock()
    {
        if ( !$this -> active ) {
            $block = new UnderConstruction();
            return $block -> getBlockContent();
        }
        else {
            $query = 'SELECT `chronik_id`, `chronik_title`, `chronik_text` FROM `chronik` WHERE `chronik_enable` = 1 ORDER BY `chronik_position` ASC;';
            $data  = $this -> registry -> db -> queryObjectArray($query);

            if ( is_array($data) AND count($data[0]) ) {
                $tabs     = array();
                $elements = array();
                $first    = true;

                foreach($data AS $element) {
                    if ( $first == true ) {
                        $first   = false;
                        $checked = ' checked="checked"';
                    }
                    else {
                        $checked = '';
                    }

                    $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'chronik_item_tab.htm');
                        $this -> renderer -> setVariable('element_id'      , $element['chronik_id']);
                        $this -> renderer -> setVariable('element_internal', ($element['chronik_id'] - 1));
                        $this -> renderer -> setVariable('element_title'   , $element['chronik_title']);
                        $this -> renderer -> setVariable('element_active'  , $checked);
                    $tabs[] = $this -> renderer -> renderTemplate();

                    $element['chronik_text'] = htmlspecialchars_decode($element['chronik_text']);
                    $element['chronik_text'] = str_replace('<br />\r', '<br />', $element['chronik_text']);

                    $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'chronik_item_content.htm');
                        $this -> renderer -> setVariable('element_id'   , $element['chronik_id']);
                        $this -> renderer -> setVariable('element_text' , $element['chronik_text']);
                    $elements[] = $this -> renderer -> renderTemplate();
                }
            }

            $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'chronik_frontend.htm');
                $this -> renderer -> setVariable('chronik_count'   , count($tabs));
                $this -> renderer -> setVariable('chronik_tabs'    , implode("\n        ", $tabs));
                $this -> renderer -> setVariable('chronik_elements', implode("\n        ", $elements));
            return $this -> renderer -> renderTemplate();
        }
    }
}