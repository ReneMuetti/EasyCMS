<?php
class Config
{
    private $registry;
    private $renderer;

    private $config = array();

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
        unset($this -> config);
    }

    public function getConfigValue($path)
    {
        if ( array_key_exists($path, $this -> registry -> config) ) {
            return $this -> registry -> config[$path]['value'];
        }
        else {
            return $this -> registry -> user_lang['global']['config_error'];
        }
    }

    public function loadCurrentConfigData($full = true, $return = false)
    {
        // config => Registry
        $this -> _getCurrentConfig($full);

        if ( $return === true ) {
            return $this -> config;
        }
    }

    public function getCurrentConfig()
    {
        // config for edit
        $this -> _getCurrentConfig(true);

        $data = array();

        if ( is_array($this -> config) ) {
            foreach( $this -> config AS $path => $config ) {
                if ( !isset($config['type']) OR empty($config['type']) ) {
                    $config['type'] = 'input';
                }

                $template_name = 'config_block_' . $config['type'] . '.htm';
                $raw_config = $config['value'];

                if ( $config['type'] == 'gridster' ) {
                    // fetch blocks for gridster
                    $layout   = $config['value'];
                    $element  = ( ($path == 'default/layout/header') ? 'header' : 'footer' );
                    $position = ( ($path == 'default/layout/header') ? 0        : 2 );

                    $config['value'] = $this -> _loadGridsterElement($layout, $element, $position);
                }

                $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . $template_name);
                    $this -> renderer -> setVariable('config_path'     , $path);
                    $this -> renderer -> setVariable('config_path_id'  , str_replace('/', '-', $path));
                    $this -> renderer -> setVariable('config_raw_value', $raw_config);
                    $this -> renderer -> setVariable('config_value'    , $config['value']);
                $data[] = $this -> renderer -> renderTemplate();
            }
        }
        else {
            // config-error!
        }

        //$data[] = '<pre>' . print_r($this -> registry -> config, true) . '</pre>';

        $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . 'page.htm');
            $this -> renderer -> setVariable('config_blocks', implode("\n", $data));

            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/gridster/jquery.gridster.css'), THIS_SCRIPT);
            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
        return $this -> renderer -> renderTemplate();
    }

    private function _loadGridsterElement($gridsterJSON, $element, $position)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . 'config_block_gridster_layout.htm');
            $this -> renderer -> setVariable('gridster_element' , $element);
            $this -> renderer -> setVariable('gridster_position', $position);
        return $this -> renderer -> renderTemplate();
    }

    private function _getCurrentConfig($full = false)
    {
        $query = 'SELECT * FROM `config` ORDER BY `config_id` ASC;';
        $data  = $this -> registry -> db -> queryObjectArray($query);

        $this -> config = array();

        if ( is_array($data) AND count($data[0]) ) {
            foreach($data AS $key => $value) {
                if ( $full === true ) {
                    $this -> config[$value['config_path']] = array(
                                                                 'id'    => $value['config_id'],
                                                                 'value' => $value['config_value'],
                                                                 'type'  => $value['config_type'],
                                                             );
                }
                else {
                    $this -> config[$value['config_path']] = $value['config_value'];
                }
            }

            ksort($this -> config);
        }
    }
}