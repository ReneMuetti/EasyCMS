<?php
class VitaFrontend
{
    private $moduleName = 'vita_frontend';
    private $moduleId   = 10010;

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
            $query = 'SELECT `vita_title`, `vita_text`, `vita_image` FROM `vita` WHERE `vita_enable` = 1 ORDER BY `vita_position` ASC;';
            $data  = $this -> registry -> db -> queryObjectArray($query);

            if ( is_array($data) AND count($data[0]) ) {
                $elements = array();

                foreach($data AS $vita) {
                    $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'vita_frontend_item.htm');
                        $this -> renderer -> setVariable('vita_element_title', $vita['vita_title']);
                        $this -> renderer -> setVariable('vita_element_text' , $vita['vita_text']);
                        $this -> renderer -> setVariable('vita_element_image', $vita['vita_image']);
                    $elements[] = $this -> renderer -> renderTemplate();
                }
            }


            $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'vita_frontend.htm');
                $this -> renderer -> setVariable('vita_elements', implode("\n    ", $elements));
            return $this -> renderer -> renderTemplate();
        }
    }
}