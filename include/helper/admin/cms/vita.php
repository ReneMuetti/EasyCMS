<?php
$vita = new Vita();

if ( isset($website -> GPC['do']) AND ($website -> GPC['do'] == 'save_vita') ) {
    $website -> input -> clean_array_gpc('p', array(
                                                  'vita_count' => TYPE_UINT,
                                                  'vita_last'  => TYPE_UINT,
                                              )
                                        );

    if ( $website -> GPC['vita_last'] > 0 ) {
        $vitaData = array();

        for( $cnt = 1; $cnt <= $website -> GPC['vita_last']; $cnt++ ) {
            $website -> input -> clean_array_gpc('p', array(
                                                          'vita_item_'       . $cnt => TYPE_UINT,
                                                          'vita_position_'   . $cnt => TYPE_UINT,
                                                          'vita_title_'      . $cnt => TYPE_NOHTML,
                                                          'vita_enable_'     . $cnt => TYPE_BOOL,
                                                          'vita_decription_' . $cnt => TYPE_NOHTML,
                                                          'vita_image_'      . $cnt => TYPE_NOHTML,
                                                      )
                                                );

            // check, if element exists and if it has data
            if ( strlen($website -> GPC['vita_title_' . $cnt]) AND ($website -> GPC['vita_decription_' . $cnt]) ) {
                $vitaData[ $website -> GPC['vita_position_' . $cnt] ] = array(
                                                                            'vita_position' => $website -> GPC['vita_position_' . $cnt],
                                                                            'vita_title'    => $website -> GPC['vita_title_' . $cnt],
                                                                            'vita_text'     => $website -> GPC['vita_decription_' . $cnt],
                                                                            'vita_image'    => $website -> GPC['vita_image_' . $cnt],
                                                                            'vita_enable'   => $website -> GPC['vita_enable_' . $cnt],
                                                                            'username'      => $website -> userinfo['username'],
                                                                        );
            }
        }

        $pageContent = $vita -> saveVitaElements($vitaData);
    }
    else {
        $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
            $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_vita_message_data_empty']);
            $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_vita');
        $pageContent = $this -> renderer -> renderTemplate();
    }
}
else {
    $pageContent = $vita -> showVitaList();
}

$pageIdentifier = 'cms-vita';