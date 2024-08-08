<?php
class UnderConstruction
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

    public function getBlockContent()
    {
        $this -> renderer -> loadTemplate('frontend' . DS . 'under_construction.htm');
        return $this -> renderer -> renderTemplate();
    }
}