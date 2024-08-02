<?php
class UserProfile
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

    /**
     * get all users from database
     *
     * @access    public
     * @return    string
     */
    public function getUserListForAdmin()
    {
        $currentUserList = $this -> _getCurrentUserListFroAdmin();

        $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'list.htm');
            $this -> renderer -> setVariable('admin_table_user_list', $currentUserList);
        return $this -> renderer -> renderTemplate();
    }

    /**
     * get all username from user by id
     *
     * @access    public
     * @return    array
     */
    public function getUsernameFromUserID($userid)
    {
        return $this -> registry -> db -> querySingleItem("SELECT `username` FROM `users` WHERE `id` = " . intval($userid));
    }

    /**
     * get all userinformation for current user from database
     *
     * @access    public
     * @return    string
     */
    public function getCurrentProfile()
    {
        return $this -> getProfileFromUser($this -> registry -> userinfo, false);
    }

    /**
     * get all specific user information from user by username
     *
     * @access    public
     * @return    array|null
     */
    public function getInformationByUsername($userName)
    {
        if ( strlen($userName) ) {
            $_username = $this -> registry -> db -> escapeString($userName);
            return $this -> registry -> db -> querySingleArray('SELECT `email`, `language`, `translation` FROM `users` WHERE `username` = \'' . $_username . '\';');
        }
        else {
            return null;
        }
    }

    /**
     * get form with all user-data
     *
     * @access    public
     * @param     array            User information from database
     * @param     bool             show information for Admin-users
     * @return    string
     */
    public function getProfileFromUser($userProfile, $loadFromAdmin)
    {
        $actionForm = 'accounts';
        $actionDo   = 'updateaccount';

        if ( (is_int($userProfile) AND ($userProfile == $this -> registry -> userinfo['id']))
             OR
             (is_array($userProfile) AND ($userProfile['id'] == $this -> registry -> userinfo['id']))
           ) {
            $actionForm = 'myaccount';
            $actionDo   = 'updateMyAccount';
        }

        if ( is_int($userProfile) ) {
            $userProfile = $this -> registry -> db -> querySingleArray('SELECT * FROM `users` WHERE `id` = ' . intval($userProfile));
        }

        $admin_block = '';

        if ( is_array($userProfile) AND count($userProfile) ) {
            if ( $loadFromAdmin === true ) {
                // Modify Account by Admin
                $currStatusBlock = implode("\n", array(
                                                     '    <input type="hidden" name="accountid" value="' . $userProfile['id'] . '" />',
                                                 )
                                          );

                $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'form_admin.htm');
                    $this -> renderer -> setVariable('checbox_state_enabled', ($userProfile['enabled'] == 'yes'       ? 'checked' : '' ) );
                    $this -> renderer -> setVariable('checbox_state_admin'  , ($userProfile['admin']   == 'yes'       ? 'checked' : '' ) );
                    $this -> renderer -> setVariable('checbox_state_status' , ($userProfile['status']  == 'confirmed' ? 'checked' : '' ) );
                $admin_block = $this -> renderer -> renderTemplate();
            }
            else {
                // Modyfy Account by self
                $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'form_hidden.htm');
                    $this -> renderer -> setVariable('curr_admin'  , $userProfile['admin']);
                    $this -> renderer -> setVariable('curr_enabled', $userProfile['enabled']);
                    $this -> renderer -> setVariable('curr_status' , $userProfile['status']);
                $currStatusBlock = $this -> renderer -> renderTemplate();
            }

            $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'form.htm');
                $this -> renderer -> setVariable('form_action', $actionForm);
                $this -> renderer -> setVariable('do_action'  , $actionDo);

                $this -> renderer -> setVariable('curr_username'       , $userProfile['username']);
                $this -> renderer -> setVariable('curr_email'          , $userProfile['email']);
                $this -> renderer -> setVariable('curr_added'          , $userProfile['added']);
                $this -> renderer -> setVariable('curr_last_login'     , $userProfile['last_login']);
                $this -> renderer -> setVariable('curr_last_access'    , $userProfile['last_access']);
                $this -> renderer -> setVariable('curr_status_block'   , $currStatusBlock);
                $this -> renderer -> setVariable('admin_block'         , $admin_block);
            return $this -> renderer -> renderTemplate();
        }
        else {
            // TODO
        }
    }

    /**
     * Update user-information for current user
     *
     * @access    public
     * @return    array
     */
    public function updateCurrentProfile()
    {
        return $this -> _updateUserProfileByID($this -> registry -> userinfo['id']);
    }

    /**
     * Update user-information for specific user
     *
     * @access    public
     * @param     int       user id
     * @return    array
     */
    public function updateProfileByID($profileID = 0)
    {
        if ( $profileID > 0 ) {
            return $this -> _updateUserProfileByID($profileID, 'admin_index.php?action=accounts');
        }
        else {
            // TODO
        }
    }

    /**
     * delete user by id
     *
     * @access    public
     * @param     int       user id
     * @return    array
     */
    public function deleteProfileById($profileID = 0)
    {
        if ( ($profileID > 0) AND ($profileID != $this -> registry -> userinfo['id']) ) {
            $query = "DELETE FROM `users` WHERE `id` = " . $profileID;
            $this -> registry -> db -> execute($query);

            return $this -> _addSuccessMessage( $this -> registry -> user_lang['profile']['success_profile_deleted'], 'admin_index.php?action=accounts' );
        }
        else {
            return $this -> _addNewChangeErrorMessage( $this -> registry -> user_lang['profile']['error_profile_cannot_deleted'] );
        }
    }




    /**
     * generate all users for Admin
     *
     * @access    private
     * @return    string
     */
    private function _getCurrentUserListFroAdmin()
    {
        $query = 'SELECT `id`, `username`, `added`, `status`, `enabled` from `users` ORDER BY `id` ASC';
        $data  = $this -> registry -> db -> queryObjectArray($query);
        if ( is_array($data) AND count($data[0]) ) {
            $account = array();

            foreach( $data AS $userAccount ) {
                $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'list_line.htm');
                    $this -> renderer -> setVariable('user_id'         , $userAccount['id']);
                    $this -> renderer -> setVariable('user_username'   , $userAccount['username']);
                    $this -> renderer -> setVariable('user_added'      , $userAccount['added']);
                    $this -> renderer -> setVariable('user_raw_status' , ($userAccount['status'] == 'confirmed') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('user_status'     , $this -> registry -> user_lang['admin'][$userAccount['status']]);
                    $this -> renderer -> setVariable('user_raw_enabled', ($userAccount['enabled'] == 'yes') ? 'status-okay' : 'status-fail' );
                    $this -> renderer -> setVariable('user_enabled'    , $this -> registry -> user_lang['global']['status_' . $userAccount['enabled']]);
                $account[] = $this -> renderer -> renderTemplate();
            }

            return implode("\n", $account);
        }
        else {
            $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'list_empty.htm');
            return $this -> renderer -> renderTemplate();
        }
    }

    private function _updateUserProfileByID($profileID = 0, $script = '')
    {
        if ( $profileID > 0 ) {
            $this -> registry -> input -> clean_array_gpc('p', array(
                                                                   'username'         => TYPE_NOHTML,
                                                                   'email'            => TYPE_NOHTML,
                                                                   'password'         => TYPE_NOHTML,
                                                                   'password_confirm' => TYPE_NOHTML,
                                                               )
                                                         );

            $currentProfile = $this -> registry -> db -> querySingleArray('SELECT * FROM `users` WHERE `id` = ' . $profileID);

            $changeData = array();
            $update = array();

            if ( strlen($this -> registry -> GPC['username']) AND ( $this -> registry -> GPC['username'] != $currentProfile['username'] ) ) {
                $update['username'] = $this -> registry -> GPC['username'];
                $changeData[] = $this -> _addNewChangeMessage('username');
            }
            if ( strlen($this -> registry -> GPC['email']) AND ( $this -> registry -> GPC['email'] != $currentProfile['email'] ) ) {
                $update['email'] = $this -> registry -> GPC['email'];
                $changeData[] = $this -> _addNewChangeMessage('email');
            }

            if ( strlen($this -> registry -> GPC['password']) OR strlen($this -> registry -> GPC['password_confirm']) ) {
                if ( strlen($this -> registry -> GPC['password']) AND strlen($this -> registry -> GPC['password_confirm']) ) {
                    $hasher = new PasswordHash(8, FALSE);
                    $pass   = $hasher -> HashPassword( $this -> registry -> GPC['password'] );

                    $secret   = mksecret();
                    $passhash = md5($secret . $this -> registry -> GPC['password'] . $secret);

                    $update['passhash'] = $passhash;
                    $update['pass']     = $pass;
                    $update['secret']   = $secret;

                    $changeData[] = $this -> _addNewChangeMessage('password');
                }
                else {
                    $changeData[] = $this -> _addNewChangeErrorMessage( $this -> registry -> user_lang['profile']['error_password_not_equal'] );
                }
            }

            if ( $currentProfile['id'] != $this -> registry -> userinfo['id'] ) {
                if ( $this -> registry -> userinfo['admin'] == 'yes' ) {
                    $this -> registry -> input -> clean_array_gpc('p', array(
                                                                           'admin'   => TYPE_BOOL,
                                                                           'enabled' => TYPE_BOOL,
                                                                           'status'  => TYPE_BOOL,
                                                                       )
                                                                 );
                    $this -> registry -> GPC['admin']   = ( $this -> registry -> GPC['admin']   ? 'yes'       : 'no' );
                    $this -> registry -> GPC['enabled'] = ( $this -> registry -> GPC['enabled'] ? 'yes'       : 'no' );
                    $this -> registry -> GPC['status']  = ( $this -> registry -> GPC['status']  ? 'confirmed' : 'pending' );

                    if ( isset($this -> registry -> GPC['admin']) AND ( $this -> registry -> GPC['admin'] != $currentProfile['admin'] ) ) {
                        $update['admin'] = $this -> registry -> GPC['admin'];
                        $changeData[] = $this -> _addNewChangeMessage('admin');
                    }
                    if ( isset($this -> registry -> GPC['enabled']) AND ( $this -> registry -> GPC['enabled'] != $currentProfile['enabled'] ) ) {
                        $update['enabled'] = $this -> registry -> GPC['enabled'];
                        $changeData[] = $this -> _addNewChangeMessage('enabled');
                    }
                    if ( isset($this -> registry -> GPC['status']) AND ( $this -> registry -> GPC['status'] != $currentProfile['status'] ) ) {
                        $update['status'] = $this -> registry -> GPC['status'];
                        $changeData[] = $this -> _addNewChangeMessage('status');
                    }
                }
            }

            if ( count($update) ) {
                $result = $this -> registry -> db -> updateRow($update, 'users', '`id` = ' . $profileID);
                if ( $result === false ) {
                    $changeData[] = $this -> _addNewChangeErrorMessage( $this -> registry -> user_lang['profile']['error_profile_update'] );
                }
                else {
                    $changeData[] = $this -> _addSuccessMessage( $this -> registry -> user_lang['profile']['success_profile_update'], $script );
                }
            }
            else {
                $changeData[] = $this -> _addNewChangeErrorMessage( $this -> registry -> user_lang['profile']['error_no_change_data'] );
            }

            return implode("\n", $changeData);
        }
        else {
            // TODO :: no ID
        }
    }

    private function _addSuccessMessage($message, $script)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'success.htm');
            $this -> renderer -> setVariable('success_message', $message);
            $this -> renderer -> setVariable('curr_form_script' , $script);
        return $this -> renderer -> renderTemplate();
    }

    private function _addNewChangeErrorMessage($message)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'messages' . DS . 'error.htm');
            $this -> renderer -> setVariable('error_message', $message);
        return $this -> renderer -> renderTemplate();
    }

    private function _addNewChangeMessage($fieldName)
    {
        $this -> renderer -> loadTemplate('admin' . DS . 'account' . DS . 'change_field.htm');
            $this -> renderer -> setVariable('change_fieldname', $this -> registry -> user_lang['profile'][$fieldName]);
        return $this -> renderer -> renderTemplate();
    }
}