<?php
class ContactForm
{
    private $moduleName = 'contact_form';
    private $moduleId   = 10001;

    private $active = false;

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
            $this -> renderer -> loadTemplate('frontend' . DS . 'module' . DS . 'contact_form.htm');
            return $this -> renderer -> renderTemplate();
        }
    }
}