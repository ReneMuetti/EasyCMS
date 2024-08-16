<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'ajax_contact_form');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// ########################### INIT VARIABLES ############################
$result = array(
              'error'   => false,
              'message' => null,
              'data'    => null
          );

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array(
                                              'action'  => TYPE_NOHTML,
                                              'name'    => TYPE_NOHTML,    // customer name
                                              'phone'   => TYPE_NOHTML,    // customer phone
                                              'email'   => TYPE_NOHTML,    // customer email
                                              'message' => TYPE_NOHTML,    // customer message
                                              'copy'    => TYPE_BOOL,      // send copy to customer
                                          )
                                    );

if ( ($website -> GPC['action'] == 'new_message') ) {
    $contact = new ContactForm();

    $result = $contact -> sendMessage(
                              $website -> GPC['name'],
                              $website -> GPC['phone'],
                              $website -> GPC['email'],
                              $website -> GPC['message'],
                              $website -> GPC['copy'],
                          );
}
else {
    $result['error']   = true;
    $result['message'] = $website -> user_lang['global']['error_sending_data'];
}

echo json_encode($result);