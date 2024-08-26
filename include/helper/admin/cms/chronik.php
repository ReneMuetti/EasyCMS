<?php
$chronik = new Chronik();

if ( isset($website -> GPC['do']) AND ($website -> GPC['do'] == 'save_chronik') ) {
    $website -> input -> clean_array_gpc('p', array(
                                                  'chronik_count' => TYPE_UINT,
                                                  'chronik_last'  => TYPE_UINT,
                                              )
                                        );

    if ( $website -> GPC['chronik_last'] > 0 ) {
        $chronikData = array();

        for( $cnt = 1; $cnt <= $website -> GPC['chronik_last']; $cnt++ ) {
            $website -> input -> clean_array_gpc('p', array(
                                                          'chronik_item_'     . $cnt => TYPE_UINT,
                                                          'chronik_position_' . $cnt => TYPE_UINT,
                                                          'chronik_title_'    . $cnt => TYPE_NOHTML,
                                                          'chronik_enable_'   . $cnt => TYPE_BOOL,
                                                          'chronik_content_'  . $cnt => TYPE_NOHTML,
                                                      )
                                                );

            // check, if element exists and if it has data
            if ( strlen($website -> GPC['chronik_title_' . $cnt]) AND ($website -> GPC['chronik_content_' . $cnt]) ) {
                $chronik_content = nl2br($website -> GPC['chronik_content_' . $cnt]);
                $chronik_content = str_replace(
                                       array("\n", '{{rn}}'),
                                       array(''  , '<br />'),
                                       $chronik_content
                                   );

                $chronikData[ $website -> GPC['chronik_position_' . $cnt] ] = array(
                                                                                  'chronik_position' => $website -> GPC['chronik_position_' . $cnt],
                                                                                  'chronik_title'    => $website -> GPC['chronik_title_'    . $cnt],
                                                                                  'chronik_text'     => $chronik_content,
                                                                                  'chronik_enable'   => $website -> GPC['chronik_enable_'   . $cnt],
                                                                                  'username'         => $website -> userinfo['username'],
                                                                              );
            }
        }

        ksort($chronikData);

        $pageContent = $chronik -> saveChronikElements($chronikData);
    }
    else {
        $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
            $this -> renderer -> setVariable('error_message', $this -> registry -> user_lang['admin']['cms_chronik_message_data_empty']);
            $this -> renderer -> setVariable('curr_form_script', 'admin_index.php?action=cms_chronik');
        $pageContent = $this -> renderer -> renderTemplate();
    }
}
else {
    $pageContent = $chronik -> showChronikList();
}

$pageIdentifier = 'cms-chronik';