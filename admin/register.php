<?php
// #################### DEFINE IMPORTANT CONSTANTS #######################
define('THIS_SCRIPT', 'admin_register');
define('THIS_TEMPLATE', 'admin/register');

// ######################### REQUIRE BACK-END ############################
chdir('../');
require_once( realpath('include/global.php') );

// #######################################################################
// ######################## START MAIN SCRIPT ############################
// #######################################################################
$website -> input -> clean_array_gpc('p', array('action' => TYPE_NOHTML));

if ($website -> userinfo) {
    header('Location: ' . $website -> baseurl . 'admin/index.php');
}
else {
    if ( isset($website -> GPC['action']) AND ($website -> GPC['action'] == 'doregister') ) {
        $website -> input -> clean_array_gpc('p', array(
                                                      'username'         => TYPE_NOHTML,
                                                      'password'         => TYPE_NOHTML,
                                                      'password_confirm' => TYPE_NOHTML,
                                                      'email'            => TYPE_NOHTML,
                                                  )
                                            );

        $errors = array();
        if ( empty($website -> GPC['username']) ) {
            $errors[] = '<li>' . $website -> user_lang['login_page']['error_register_username_empty'] . '</li>';
        }
        if ( empty($website -> GPC['password']) OR empty($website -> GPC['password_confirm']) OR ($website -> GPC['password'] != $website -> GPC['password_confirm']) ) {
            $errors[] = '<li>' . $website -> user_lang['login_page']['error_register_passwort_not_equal'] . '</li>';
        }
        if ( empty($website -> GPC['email']) ) {
            $errors[] = '<li>' . $website -> user_lang['login_page']['error_register_email_empty'] . '</li>';
        }

        if ( count($errors) ) {
            // any Errors
            $errors = implode("\n        ", $errors);

            $renderer -> loadTemplate(THIS_TEMPLATE . DS . 'failed.htm');
            $renderer -> setVariable('register_errors_list', $errors);
        }
        else {
            // create User-Account
            $hasher = new PasswordHash(8, FALSE);
            $pass   = $hasher -> HashPassword( $website -> GPC['password'] );

            $secret   = mksecret();
            $passhash = md5($secret . $website -> GPC['password'] . $secret);

            $insert = array(
                          'username'    => $website -> GPC['username'],
                          'passhash'    => $passhash,
                          'pass'        => $pass,
                          'secret'      => $secret,
                          'email'       => $website -> GPC['email'],
                          'status'      => 'pending',
                          'added'       => date("Y-m-d H:i:s"),
                          'last_login'  => '0000-00-00 00:00:00',
                          'last_access' => '0000-00-00 00:00:00',
                          'ip'          => '',
                          'enabled'     => 'yes',
                          'session'     => '',
                      );

            $result = $website -> db -> insertRow($insert, 'users');
            if ( $result === false ) {
                // Failed to inster into Database
                $renderer -> loadTemplate(THIS_TEMPLATE . DS . 'failed.htm');
                $renderer -> setVariable('register_errors_list', '<li>' . $website -> user_lang['login_page']['error_save_to_database'] . '</li>');
            }
            else {
                // Success
                $renderer -> loadTemplate(THIS_TEMPLATE . DS . 'success.htm');
            }
        }
    }
    else {
        $renderer -> loadTemplate('admin' . DS . 'default' . DS . 'header.htm');
        $header = $renderer -> renderTemplate();

        $renderer -> loadTemplate('admin' . DS . 'default' . DS . 'footer.htm');
            $renderer -> setVariable('footer_current_date'  , date("d.m.Y", TIMENOW) );
            $renderer -> setVariable('footer_current_time'  , date("H:i:s", TIMENOW) );
            $renderer -> setVariable('footer_db_performance', '');
        $footer = $renderer -> renderTemplate();

        $renderer -> loadTemplate(THIS_TEMPLATE . '.htm');

        $renderer -> setVariable('global_header', $header);
        $renderer -> setVariable('global_footer', $footer);

        $renderer -> addCustonStyle(array('script' => 'skin/css/login-register.css'), THIS_SCRIPT);
    }

    print_output($renderer -> renderTemplate());
}