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
            return $this -> registry -> config[$path];
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
                $sel_config = array(
                                  '<option value="no_select">' . $this -> registry -> user_lang['global']['option_actions_select'] . '</option>'
                              );

                if ( $config['type'] == 'gridster' ) {
                    // fetch blocks for gridster-default-config
                    $element    = ( ($path == 'default/layout/header') ? 'header' : 'footer' );
                    $position   = ( ($path == 'default/layout/header') ? 0        : 2 );
                    $raw_config = html_entity_decode($raw_config);

                    $config['value'] = $this -> _loadGridsterElement($raw_config, $element, $position);
                }

                if ( $config['type'] == 'select' ) {
                    switch($path) {
                        case 'design/theme/skin': // load all skin-directorys
                                                  $this -> _loadAllSkins($sel_config, $config['value']);
                                                  break;

                        default: // no valid section found
                                 $template_name = 'config_block_input.htm';
                                 break;
                    }
                }

                $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . $template_name);
                    $this -> renderer -> setVariable('config_path'     , $path);
                    $this -> renderer -> setVariable('config_path_id'  , str_replace('/', '-', $path));
                    $this -> renderer -> setVariable('config_raw_value', $raw_config);
                    $this -> renderer -> setVariable('config_value'    , $config['value']);
                    $this -> renderer -> setVariable('config_select'   , implode("", $sel_config) );
                $data[] = $this -> renderer -> renderTemplate();
            }
        }
        else {
            // config-error!
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . 'page.htm');
            $this -> renderer -> setVariable('config_blocks', implode("\n", $data));

            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/gridster/jquery.gridster.css'), THIS_SCRIPT);
            $this -> renderer -> addCustonStyle(array('script' => 'skin/js/jquery-ui/jquery-ui.min.css'), THIS_SCRIPT);
        return $this -> renderer -> renderTemplate();
    }

    public function updateCurrentConfig()
    {
        $this -> _getCurrentConfig(true);

        $resultList = array();

        foreach( $this -> config AS $path => $data ) {
            $this -> registry -> input -> clean_array_gpc('p', array($path => TYPE_NOHTML));

            if ( $data['value'] != $this -> registry -> GPC[$path] ) {
                $sqlData = array(
                               'config_value' => $this -> registry -> GPC[$path],
                               'username'     => $this -> registry -> userinfo['username'],
                           );

                $resultList[$path] = $this -> registry -> db -> updateRow($sqlData, 'config', 'WHERE `config_id` = ' . $data['id'] . ' AND `config_path` = "' . $path . '"' );
            }
        }

        if ( is_array($resultList) AND COUNT($resultList) ) {
            $resultTmp = array();

            foreach( $resultList AS $path => $result ) {
                $state = ($result ? 'okay' : 'fail' );
                $resultTmp[] = '<li class="status status-' . $state . '">' .
                               $this -> registry -> user_lang['admin']['config_saved_' . $state] .
                               $path . '</li>';
            }

            $result = implode("\n", $resultTmp);
        }
        else {
            $result = '<li class="status status-okay">' . $this -> registry -> user_lang['admin']['config_no_save_data'] . '</li>';
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . 'save_result.htm');
            $this -> renderer -> setVariable('config_update_list', $result);
            $this -> renderer -> setVariable('curr_form_script'  , 'admin_index.php?action=system_config');
        return $this -> renderer -> renderTemplate();
    }

    private function _loadGridsterElement($gridsterJSON, $element, $position)
    {
        if ( strlen($gridsterJSON) ) {
            $page = new Pages();
            $gridsterBlocks[$element] = array();
            $page -> getRenderBlocksFromGridsterJson($gridsterJSON, $gridsterBlocks);
        }

        $this -> renderer -> loadTemplate('admin' . DS . 'config' . DS . 'config_block_gridster_layout.htm');
            $this -> renderer -> setVariable('gridster_element' , $element);
            $this -> renderer -> setVariable('gridster_position', $position);
            $this -> renderer -> setVariable('gridster_blocks'  , implode("\n", $gridsterBlocks[$element]));
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

    private function _loadAllSkins(&$sel_options, $curr_select)
    {
        $skinPath = realpath( APP_ROOT . DS . $this -> registry -> config['Misc']['design_directory'] ) . DS;

        $dir = new FileDir();
        $list = $dir -> get_dir_content($skinPath);

        if ( is_array($list) AND count($list) ) {
            foreach( $list AS $item ) {
                if ( $item['type'] == 'dir' ) {
                    $sel_options[] = '<option value="' .
                                         $item['name'] . '"' .
                                         ( ($item['name'] == $curr_select) ? ' selected="select"' : '' ) . '>' .
                                         $item['name'] .
                                     '</option>';
                }
            }
        }
    }
}