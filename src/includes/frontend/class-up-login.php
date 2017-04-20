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
     * Checks if user exits, if so: check if provided password matches the one in the database
     * @return bool User login success status
     */
    private function checkPasswordCorrectnessAndLogin() {

        global $wpdb;

        $user_id = null;

        $email = is_email( $_POST['user_email'] ) ? $_POST['user_email'] : false;
        if( ! $email ) {
            $this->feedback = __( "Invalid email", "upstream" );
            return false;
        }

        // select the client user with the matching email
        $users = $wpdb->get_results (
            "SELECT * FROM `" . $wpdb->postmeta .
            "` WHERE `meta_key` = '_upstream_client_users' AND
            `meta_value` REGEXP '.*\"email\";s:[0-9]+:\"". esc_html( $email ) ."\".*'"
        );


        if ( is_array ( $users ) && count ( $users ) > 0) {

            // this is in case we have multiple emails the same
            foreach ( $users as $key => $client ) {
                $status = get_post_status( $client->post_id );
                if( $status != 'publish' )
                    unset( $users[$key] ); // unset any thet aren't published
            }

            // if we still have multiple emails, throw the error
            if( isset( $users[1] ) ) {
                $this->feedback = __( "Looks like there are multiple users with this email.<br>Please contact your administrator.", "upstream" );
                return false;
            }

            $metavalue = unserialize( $users[0]->meta_value );
            foreach ($metavalue as $key => $user) {
                if( isset( $user['email'] ) && $user['email'] == $email ) {
                    $user_id = $user['id'];
                }
            }

            $client_id = $users[0]->post_id;
            $password = get_post_meta( $client_id, '_upstream_client_password', true );

            if ( ! isset( $password ) ||
                ( isset( $password ) && trim( $password ) == trim( $_POST['user_password'] ) ) ) {
                // write user data into PHP SESSION [a file on your server]
                $_SESSION['client_id']  = (int) $client_id;
                $_SESSION['user_id']    = esc_html( $user_id );
                $_SESSION['user_is_logged_in'] = true;
                $this->user_is_logged_in = true;
                return true;
            } else {
                $this->feedback = __( "Wrong password.", "upstream" );
            }
        } else {
            $this->feedback = __( "This user does not exist.", "upstream" );
        }
        // default return
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
