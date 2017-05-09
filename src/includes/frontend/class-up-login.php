<?php


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


class UpStream_Login{

    /**
     * @var bool Login status of user
     */
    private $user_is_logged_in = false;

    /**
     * @var string System messages, likes errors, notices, etc.
     */
    public $feedback = "";

    /**
     * Does necessary checks for PHP version and PHP password compatibility library and runs the application
     */
    public function __construct() {
        // start the session, always needed!
        $this->doStartSession();
        // check for possible user interactions (login with session/post data or logout)
        $this->performUserLoginAction();
    }

    /**
     * Handles the flow of the login/logout process. According to the circumstances, a logout, a login with session
     * data or a login with post data will be performed
     */
    private function performUserLoginAction() {
        if (isset($_GET["action"]) && $_GET["action"] == "logout" && !isset($_POST["login"]) ) {
            $this->doLogout();
        } elseif (!empty($_SESSION['user_id']) && ($_SESSION['user_is_logged_in'])) {
            $this->doLoginWithSessionData();
        } elseif (isset($_POST["login"])) {
            $this->doLoginWithPostData();
        }
    }

    /**
     * Simply starts the session.
     * It's cleaner to put this into a method than writing it directly into runApplication()
     */
    private function doStartSession() {
        if(session_status() == PHP_SESSION_NONE) session_start();
    }

    /**
     * Set a marker (NOTE: is this method necessary ?)
     */
    private function doLoginWithSessionData() {
        $this->user_is_logged_in = true; // ?
    }

    /**
     * Process flow of login with POST data
     */
    private function doLoginWithPostData() {
        if ( $this->checkLoginFormDataNotEmpty() ) {
            $this->checkPasswordCorrectnessAndLogin();
        }
    }

    /**
     * Logs the user out
     */
    public function doLogout() {
        $_SESSION = array();
        session_destroy();
        $this->user_is_logged_in = false;
        $this->feedback = __( "You were just logged out.", "upstream" );
    }

    /**
     * Validates the login form data, checks if username and password are provided
     * @return bool Login form data check success state
     */
    private function checkLoginFormDataNotEmpty() {

        if( ! isset( $_POST['upstream_login_nonce'] ) ){
            return false;
        }

        if( ! wp_verify_nonce( $_POST['upstream_login_nonce'], 'upstream-login-nonce' ) ) {
            return false;
        }

        if ( ! empty( $_POST['user_email'] ) && ! empty( $_POST['user_password'] ) ) {
            return true;
        } elseif ( empty( $_POST['user_email'] ) ) {
            $this->feedback = __( "Username field was empty.", "upstream" );
        } elseif ( empty( $_POST['user_password'] ) ) {
            $this->feedback = __( "Password field was empty.", "upstream" );
        }
        // default return
        return false;
    }

    /**
     * Method that attempt to authenticate a user against the open project.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  bool
     */
    private function checkPasswordCorrectnessAndLogin()
    {
        global $wpdb;

        $userCanLogIn = false;

        $postData = array(
            'email'    => isset($_POST['user_email']) && is_email($_POST['user_email']) ? $_POST['user_email'] : false,
            'password' => isset($_POST['user_password']) ? $_POST['user_password'] : false
        );

        if (!empty($postData['email'])) {
            if (!empty($postData['password'])) {
                $clientsRowset = $wpdb->get_results (
                    "SELECT * FROM `" . $wpdb->postmeta .
                    "` WHERE `meta_key` = '_upstream_client_users' AND
                    `meta_value` REGEXP '.*\"email\";s:[0-9]+:\"". esc_html($postData['email']) ."\".*'"
                );
                $clientsRowsetCount = count($clientsRowset);

                if (is_array($clientsRowset) && $clientsRowsetCount > 0)  {
                    foreach ($clientsRowset as $clientIndex => $client) {
                        $status = get_post_status($client->post_id);
                        if ($status !== 'publish') {
                            unset($clientsRowset[$clientIndex]); // unset any thet aren't published
                        }
                    }

                    if (!isset($clientsRowset[1])) {
                        $client = &$clientsRowset[0];
                        $user_id = null;

                        $clientUsers = unserialize($client->meta_value);
                        foreach ($clientUsers as $clientUser) {
                            if (isset($clientUser['email']) && $clientUser['email'] === $postData['email']) {
                                $user_id = $clientUser['id'];
                                break;
                            }
                        }

                        if (!empty($user_id)) {
                            $projectPwd = get_post_meta($client->post_id, '_upstream_client_password', true);
                            if ($postData['password'] === $projectPwd) {
                                $client_id = (int)$client->post_id;

                                $userCanLogIn = true;
                            } else {
                                $this->feedback = __("Wrong password.", 'upstream');
                            }

                            unset($projectPwd);
                        } else {
                            $this->feedback = __("This user does not exist.", 'upstream');
                        }
                    } else {
                        $this->feedback = __("Looks like there are multiple users with this email.<br>Please contact your administrator.", 'upstream');
                    }
                } else {
                    $upstreamUsersQueryParams = array(
                        'role__in'       => array('upstream_manager', 'upstream_user'),
                        'search'         => esc_html($postData['email']),
                        'search_columns' => array('user_email')
                    );
                    $upstreamUsersQuery = new WP_User_Query($upstreamUsersQueryParams);

                    $usersFoundCount = count($upstreamUsersQuery->results);
                    if ($usersFoundCount > 1) {
                        $this->feedback = __("Looks like there are multiple users with this email.<br>Please contact your administrator.", 'upstream');
                    } else if ($usersFoundCount === 1) {
                        $clientRowset = $wpdb->get_results(
                            'SELECT * '.
                            'FROM `'. $wpdb->postmeta .'` '.
                            'WHERE `meta_key` = "_upstream_project_client" '.
                            '  AND `post_id` = '. upstream_post_id()
                        );

                        if (is_array($clientRowset) && count($clientRowset) === 1) {
                            $user = &$upstreamUsersQuery->results[0];
                            $client_id = (int)$clientRowset[0]->meta_value;

                            $projectPwd = get_post_meta($client_id, '_upstream_client_password', true);
                            if ($postData['password'] === $projectPwd) {
                                $user_id = $user->id;
                                $userCanLogIn = true;
                            } else {
                                $this->feedback = __("Wrong password.", 'upstream');
                            }

                            unset($projectPwd);
                        } else {
                            $this->feedback = __("Looks like something went wrong with the authentication.<br>Please contact your administrator.", 'upstream');
                        }
                    } else {
                        $this->feedback = __("This user does not exist.", 'upstream');
                    }
                }
            }
            else {
                $this->feedback = __("Wrong password.", 'upstream');
            }
        } else {
            $this->feedback = __("Invalid email", 'upstream');
        }

        if ($userCanLogIn && !empty($client_id) && !empty($user_id)) {
            $_SESSION['client_id'] = $client_id;
            $_SESSION['user_id'] = esc_html($user_id);
            $_SESSION['user_is_logged_in'] = true;

            $this->user_is_logged_in = true;

            return true;
        }

        return false;
    }

    /**
     * Simply returns the current status of the user's login
     * @return bool User's login status
     */
    public function getUserLoginStatus() {
        return $this->user_is_logged_in;
    }



}
