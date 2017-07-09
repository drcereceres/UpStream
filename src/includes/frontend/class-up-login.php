<?php
if (!defined('ABSPATH')) exit;

final class UpStream_Login
{
    /**
     * Represent the feedback message for the current action.
     *
     * @since   1.0.0
     * @access  private
     *
     * @var     string  $feedbackMessage
     */
    private $feedbackMessage = "";

    /**
     * Check if there's a feedback message for the current action.
     *
     * @since   1.9.0
     *
     * @return  bool
     */
    public function hasFeedbackMessage()
    {
        $hasFeedbackMessage = !empty($this->feedbackMessage);

        return $hasFeedbackMessage;
    }

    /**
     * Retrieve the feedback message for the current action.
     *
     * @since   1.9.0
     *
     * @return  string
     */
    public function getFeedbackMessage()
    {
        $feedbackMessage = (string) $this->feedbackMessage;

        $this->feedbackMessage = "";

        return $feedbackMessage;
    }

    /**
     * Class constructor.
     *
     * @since   1.0.0
     */
    public function __construct()
    {
        $this->performUserLoginAction();
    }

    /**
     * Handles the flow of the login/logout process.
     *
     * @since   1.0.0
     * @access  private
     */
    private function performUserLoginAction()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : null;
        $userIsTryingToLogin = isset($_POST['login']);

        if ($action === "logout" && !$userIsTryingToLogin) {
            UpStream_Login::doDestroySession();
        } else if ($userIsTryingToLogin) {
            $data = $this->validateLogInPostData();
            if (is_array($data)) {
                $this->authenticateData($data);
            }
        }
    }

    /**
     * Destroy user's session data.
     *
     * @since   1.9.0
     * @static
     */
    public static function doDestroySession()
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['upstream'])) {
            unset($_SESSION['upstream']);
        }
    }

    /**
     * Logs the user out.
     *
     * @since   1.9.0
     * @access  private
     */
    private function doLogOut()
    {
        UpStream_Login::doDestroySession();

        $this->feedbackMessage = __("You were just logged out.", 'upstream');
    }

    /**
     * Validate the login form data by checking if a username and a password were provided.
     * If data is valid, an array will be returned. The return will be FALSE otherwise.
     *
     * @since   1.9.0
     * @access  private
     *
     * @return  array | bool
     */
    private function validateLogInPostData()
    {
        if (!isset($_POST['upstream_login_nonce']) || !wp_verify_nonce($_POST['upstream_login_nonce'], 'upstream-login-nonce')) {
            return false;
        }

        $postData = array(
            'username' => isset($_POST['user_email']) ? sanitize_text_field(trim($_POST['user_email'])) : "",
            'password' => isset($_POST['user_password']) ? $_POST['user_password'] : ""
        );

        if (empty($postData['username'])) {
            $this->feedbackMessage = __("Email address field cannot be empty.", 'upstream');
        } else if (strlen($postData['username']) < 3 || !is_email($postData['username'])) {
            $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
        } else {
            if (empty($postData['password'])) {
                $this->feedbackMessage = __("Password field cannot be empty.", 'upstream');
            } else if (strlen($postData['password']) < 5) {
                $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
            } else {
                return $postData;
            }
        }

        return false;
    }

    /**
     * Method reponsible for verifying if a given password is valid for a given project.
     *
     * @since   1.9.0
     * @access  private
     *
     * @param   string  $subject        The raw password to be tested.
     * @param   int     $project_id     The project id to be used.
     *
     * @return  bool
     */
    private function verifyProjectPassword($subject, $project_id)
    {
        if (strlen((string)$subject) < 5 || (int)$project_id <= 0) {
            return false;
        }

        $passwordHash = get_post_meta($project_id, '_upstream_client_password', true);

        return password_verify($subject, $passwordHash);
    }

    /**
     * Attempt to authenticate a user against the open project given current email address and password.
     *
     * @since   1.9.0
     * @access  private
     *
     * @param   array   $data   An associative array containing an email (already sanitized) and a raw password.
     *
     * @return  bool
     */
    private function authenticateData($data)
    {
        if (!isset($data['username']) || !isset($data['password'])) {
            return false;
        }

        global $wpdb;

        $userCanLogIn = false;
        $user_id = null;
        $client_id = null;

        // Tries to match the email address against a project client users.
        $projectClientsRowset = $wpdb->get_results('
            SELECT *
              FROM `'. $wpdb->postmeta .'`
             WHERE
                `meta_key` = \'_upstream_new_client_users\' AND
                `meta_value` REGEXP \'.*\"email\";s:[0-9]+:\"'. esc_html($data['username']) .'\".*\''
        );

        if (count($projectClientsRowset) > 0) {
            // Unset any Project Client User that might be not published.
            foreach ($projectClientsRowset as $projectClientIndex => $projectClient) {
                $status = get_post_status((int)$projectClient->post_id);
                if ($status !== "publish") {
                    unset($projectClientsRowset[$projectClientIndex]);
                }
            }

            if (count($projectClientsRowset) > 1) {
                $this->feedbackMessage = __("Looks like there are multiple users using this email.<br>Please, contact your administrator as soon as possible.", 'upstream');
            } else {
                $client = array_values($projectClientsRowset)[0];

                $clientUsers = unserialize($client->meta_value);
                foreach ($clientUsers as $clientUser) {
                    if (isset($clientUser['email']) && $clientUser['email'] === $data['username']) {
                        $user_id = $clientUser['id'];
                        break;
                    }
                }

                if (empty($user_id)) {
                    // User does not exist.
                    $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
                } else {
                    if ($this->verifyProjectPassword($data['password'], $client->post_id)) {
                        $client_id = $client->post_id;
                        $userCanLogIn = true;
                    } else {
                        // Invalid password.
                        $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
                     }
                }
            }
        } else {
            $queryParams = array(
                'role__in'       => array('upstream_manager', 'upstream_user'),
                'search'         => esc_html($data['username']),
                'search_columns' => array('user_email')
            );

            $usersQuery = new WP_User_query($queryParams);

            $usersRowsetCount = count($usersQuery->results);
            if ($usersRowsetCount > 1) {
                $this->feedbackMessage = __("Looks like there are multiple users using this email.<br>Please, contact your administrator as soon as possible.", 'upstream');
            } else if ($usersRowsetCount === 0) {
                // User does not exist.
                $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
            } else {
                $clientRowset = $wpdb->get_results('
                    SELECT *
                    FROM `'. $wpdb->postmeta .'`
                    WHERE
                        `meta_key` = "_upstream_project_client" AND
                        `post_id`  = "'. esc_html(upstream_post_id()) .'"'
                );

                if (count($clientRowset) === 1) {
                    $client_id = $clientRowset[0]->meta_value;

                    if ($this->verifyProjectPassword($data['password'], $client_id)) {
                        $user_id = $usersQuery->results[0]->ID;

                        $userCanLogIn = true;
                    } else {
                        // Invalid password.
                        $this->feedbackMessage = __("Invalid email address and/or password.", 'upstream');
                    }
                } else {
                    $this->feedbackMessage = __("Invalid project.", 'upstream');
                }
            }
        }

        if ($userCanLogIn && !empty($client_id) && !empty($user_id)) {
            $_SESSION['upstream'] = array(
                'client_id' => esc_html($client_id),
                'user_id'   => esc_html($user_id)
            );

            wp_redirect(esc_url(get_the_permalink(upstream_post_id())));
        }

        return $userCanLogIn;
    }

    /**
     * Return the current status of the user's login.
     *
     * @since   1.0.0
     * @static
     *
     * @return  bool
     */
    public static function userIsLoggedIn()
    {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }

        $userIsLoggedIn = (
            isset($_SESSION['upstream']) &&
            !empty($_SESSION['upstream']['client_id']) &&
            !empty($_SESSION['upstream']['user_id'])
        );

        return $userIsLoggedIn;
    }
}
