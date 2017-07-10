<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

use \UpStream\Traits\Singleton;
use \Cmb2Grid\Grid\Cmb2Grid;

// @todo
class UpStream_Metaboxes_Clients
{
    use Singleton;

    /**
     * @var string
     * @todo
     */
    protected static $postType = 'client';

    /**
     * @var string
     * @todo
     */
    protected static $postTypeLabelSingular = null;

    /**
     * @var string
     * @todo
     */
    protected static $postTypeLabelPlural = null;

    /**
     * @todo
     */
    protected static $prefix = '_upstream_client_';

    public function __construct()
    {
        self::$postTypeLabelSingular = upstream_client_label();
        self::$postTypeLabelPlural = upstream_client_label_plural();

        $namespace = get_class(self::$instance);
        add_action('wp_ajax_upstream:client.add_new_user', array($namespace, 'addNewUser'));
        add_action('wp_ajax_upstream:client.remove_user', array($namespace, 'removeUser'));
        add_action('wp_ajax_upstream:client.fetch_unassigned_users', array($namespace, 'fetchUnassignedUsers'));
        add_action('wp_ajax_upstream:client.add_existent_users', array($namespace, 'addExistentUsers'));

        // Enqueues the default ThickBox assets.
        add_thickbox();

        self::renderMetaboxes();
    }

    private static function renderMetaboxes()
    {
        self::renderDetailsMetabox();
        self::renderLogoMetabox();

        $namespace = get_class(self::$instance);

        add_action('add_meta_boxes', array($namespace, 'createUsersMetabox'));
    }

    private static function getUsersFromClient($client_id)
    {
        if ((int)$client_id <= 0) {
            return array();
        }

        // Let's cache all users basic info so we don't have to query each one of them later.
        global $wpdb;
        $rowset = $wpdb->get_results(sprintf('
            SELECT `ID`, `display_name`, `user_login`, `user_email`
            FROM `%s`',
            $wpdb->prefix . 'users'
        ));

        // Create our users hash map.
        $users = array();
        foreach ($rowset as $row) {
            $users[(int)$row->ID] = array(
                'id'       => (int)$row->ID,
                'name'     => $row->display_name,
                'username' => $row->user_login,
                'email'    => $row->user_email
            );
        }
        unset($rowset);

        $clientUsersList = array();

        // Retrieve all client users.
        $meta = (array)get_post_meta($client_id, '_upstream_new_client_users');
        if (!empty($meta)) {
            foreach ($meta[0] as $clientUser) {
                if (!empty($clientUser) && is_array($clientUser) && isset($users[$clientUser['user_id']])) {
                    $user = $users[$clientUser['user_id']];

                    $user['assigned_at'] = $clientUser['assigned_at'];
                    $user['assigned_by'] = $users[$clientUser['assigned_by']]['name'];

                    array_push($clientUsersList, (object)$user);
                }
            }
        }

        return $clientUsersList;
    }

    private static function getUnassignedUsersFromClient($client_id)
    {
        // @todo
    }

    private static function renderAddNewUserModal()
    {
        ?>
        <div id="modal-add-new-user" style="display: none;">
            <div id="form-add-new-user">
                <div>
                    <h3><?php echo __('Credentials', 'upstream'); ?></h3>
                    <div class="up-form-group">
                        <label for="new-user-email"><?php echo __('Email', 'upstream') .' *'; ?></label>
                        <input type="email" name="email" id="new-user-email" required />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-username"><?php echo __('Username', 'upstream') .' *'; ?></label>
                        <input type="text" name="username" id="new-user-username" required />
                        <div class="up-help-block">
                            <ul>
                                <li><?php echo __('Must be between 3 and 60 characters long;', 'upstream'); ?></li>
                                <li><?php echo __('You may use <code>letters (a-z)</code>, <code>numbers (0-9)</code>, <code>-</code> and <code>_</code> symbols;', 'upstream'); ?></li>
                                <li><?php echo __('The first character must be a <code>letter</code>;', 'upstream'); ?></li>
                                <li><?php echo __('Everything will be lowercased.', 'upstream'); ?></li>
                            </ul>
                        </div>
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-password"><?php echo __('Password', 'upstream') .' *'; ?></label>
                        <input type="password" name="password" id="new-user-password" required />
                        <div class="up-help-block">
                            <ul>
                                <li><?php echo __('Must be at least between 6 characters long.', 'upstream'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="up-form-group label-default">
                        <label>
                            <input type="checkbox" name="notification" id="new-user-notification" value="1" checked />
                            <span><?php echo __('Send user info via email', 'upstream'); ?></span>
                        </label>
                    </div>
                    <div class="up-form-group">
                        <button type="submit" class="button button-primary"><?php echo __('Add New User', 'upstream'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private static function renderAddExistentUserModal()
    {
        $client_id = get_the_id();
        $unassignedUsers = self::getUnassignedUsersFromClient($client_id);
        ?>
        <div id="modal-add-existent-user" style="display: none;">
            <div class="upstream-row">
                <p><?php echo sprintf(__('These are all the users assigned with the role <code>%s</code> and not related to this client yet.', 'upstream'), sprintf(__('%s Client User', 'upstream'), upstream_project_label())); ?></p>
            </div>
            <div class="upstream-row">
                <table id="table-add-existent-users" class="wp-list-table widefat fixed striped posts upstream-table">
                    <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" />
                            </th>
                            <th><?php echo __('Name', 'upstream'); ?></th>
                            <th><?php echo __('Username', 'upstream'); ?></th>
                            <th><?php echo __('Email', 'upstream'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($unassignedUsers) > 0): ?>
                        <?php foreach ($unassignedUsers as $user): ?>
                            <tr data-id="<?php echo $user->id; ?>">
                                <td class="text-center">
                                    <input type="checkbox" value="1" />
                                </td>
                                <td><?php echo $user->name; ?></td>
                                <td><?php echo $user->username; ?></td>
                                <td><?php echo $user->email; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4"><?php echo __('All users seems to be assigned to this client.', 'upstream'); ?></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="upstream-row submit"></div>
        </div>
        <?php
    }

    public static function renderUsersMetabox()
    {
        $client_id = get_the_id();

        $usersList = self::getUsersFromClient($client_id);
        ?>

        <?php // @todo: create js/css to make Thickbox responsive. ?>
        <div class="upstream-row">
            <a name="Add New User" href="#TB_inline?width=600&height=400&inlineId=modal-add-new-user" class="thickbox button"><?php echo __('Add New User', 'upstream'); ?></a>
            <a id="add-existent-user" name="Add Existent User" href="#TB_inline?width=600&height=300&inlineId=modal-add-existent-user" class="thickbox button"><?php echo __('Add Existent User', 'upstream'); ?></a>
        </div>
        <div class="upstream-row">
        <table id="table-users" class="wp-list-table widefat fixed striped posts upstream-table">
            <thead>
                <tr>
                    <th><?php echo __('Name', 'upstream'); ?></th>
                    <th><?php echo __('Username', 'upstream'); ?></th>
                    <th><?php echo __('Email', 'upstream'); ?></th>
                    <th class="text-center"><?php echo __('Assigned at', 'upstream'); ?></th>
                    <th><?php echo __('Assigned by', 'upstream'); ?></th>
                    <th class="text-center"><?php echo __('Remove?', 'upstream'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usersList) > 0): ?>
                <?php foreach ($usersList as $user): ?>
                <tr data-id="<?php echo $user->id; ?>">
                    <td><?php echo $user->name; ?></td>
                    <td><?php echo $user->username; ?></td>
                    <td><?php echo $user->email; ?></td>
                    <td class="text-center"><?php echo $user->assigned_at; ?></td>
                    <td><?php echo $user->assigned_by; ?></td>
                    <td class="text-center">
                        <a href="#" onclick="javascript:void(0);" data-remove-user>
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr data-empty>
                    <td colspan="7"><?php echo __("There's no users assigned yet.", 'upstream'); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        <?php
        self::renderAddNewUserModal();
        self::renderAddExistentUserModal();
    }

    public static function createUsersMetabox()
    {
        add_meta_box(
            self::$prefix . 'users',
            '<span class="dashicons dashicons-groups"></span>' . __("Users", 'upstream'),
            array(get_class(self::$instance), 'renderUsersMetabox'),
            self::$postType,
            'normal'
        );
    }

    public static function renderDetailsMetabox()
    {
        $metabox = new_cmb2_box(array(
            'id'           => self::$prefix . 'details',
            'title'        => '<span class="dashicons dashicons-admin-generic"></span>' . __('Details', 'upstream'),
            'object_types' => array(self::$postType),
            'context'      => 'side',
            'priority'     => 'high'
        ));

        $phoneField = $metabox->add_field(array(
            'name' => __('Phone Number', 'upstream'),
            'id'   => self::$prefix . 'phone',
            'type' => 'text'
        ));

        $websiteField = $metabox->add_field(array(
            'name' => __('Website', 'upstream'),
            'id'   => self::$prefix . 'website',
            'type' => 'text_url'
        ));

        // @todo: may we should use tinymce?
        $addressField = $metabox->add_field(array(
            'name' => __('Address', 'upstream'),
            'id'   => self::$prefix . 'address',
            'type' => 'textarea_small'
        ));

        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($phoneField, $websiteField, $addressField));
    }

    public static function renderLogoMetabox()
    {
        $metabox = new_cmb2_box(array(
            'id'           => self::$prefix . 'logo',
            'title'        => '<span class="dashicons dashicons-format-image"></span>' . __("Logo", 'upstream'),
            'object_types' => array(self::$postType),
            'context'      => 'side',
            'priority'     => 'core'
        ));

        $logoField = $metabox->add_field(array(
            'name' => __('Logo', 'upstream'),
            'id'   => self::$prefix . 'logo',
            'type' => 'file'
        ));


        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($logoField));
    }

    public static function addNewUser()
    {
        // @todo : permission checks?

        header('Content-Type: application/json');

        global $wpdb;

        $response = array(
            'success' => false,
            'data'    => null,
            'err'     => null
        );

        try {
            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception("@todo");
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception("@todo");
            }

            $data = array(
                'username'     => strtolower(trim(@$_POST['username'])),
                'email'        => trim(@$_POST['email']),
                'password'     => @$_POST['password'],
                'first_name'   => trim(@$_POST['first_name']),
                'last_name'    => trim(@$_POST['last_name']),
                'notification' => isset($_POST['notification']) ? (bool)$_POST['notification'] : false // @todo: should be true?
            );

            // Validate `password` field.
            if (strlen($data['password']) < 6) {
                throw new \Exception("Password must be at least 6 characters long.");
            }

            // Validate `username` field.
            $userDataUsername = $data['username'];
            $userDataUsernameLength = strlen($userDataUsername);
            if ($userDataUsernameLength < 3 || $userDataUsernameLength > 60) {
                throw new \Exception("The username must be between 3 and 60 characters long.");
            } else if (!validate_username($data['username']) || !preg_match('/^[a-z]+[a-z0-9\-\_]+$/i', $userDataUsername)) {
                throw new \Exception("Invalid username.");
            } else {
                $usernameExists = (bool)$wpdb->get_var(sprintf('
                    SELECT COUNT(`ID`)
                    FROM `%s`
                    WHERE `user_login` = "%s"',
                    $wpdb->prefix . 'users',
                    $userDataUsername
                ));

                if ($usernameExists) {
                    throw new \Exception("This username is not available.");
                }
            }

            // Validate the `email` field.
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !is_email($data['email'])) {
                throw new \Exception("Invalid email.");
            } else {
                $emailExists = (bool)$wpdb->get_var(sprintf('
                    SELECT COUNT(`ID`)
                    FROM `%s`
                    WHERE `user_email` = "%s"',
                    $wpdb->prefix . 'users',
                    $data['email']
                ));

                if ($emailExists) {
                    throw new \Exception("This email address is not available.");
                }
            }

            $userData = array(
                'user_login'    => $userDataUsername,
                'user_pass'     => $data['password'],
                'user_nicename' => $userDataUsername,
                'user_email'    => $data['email'],
                'display_name'  => $userDataUsername,
                'nickname'      => $userDataUsername,
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'role'          => 'upstream_client_user' // @todo : script to create the role when updating UpStream?
            );

            $userDataId = wp_insert_user($userData);
            if (is_wp_error($userDataId)) {
                throw new \Exception($userDataId->get_error_message());
            }

            if ($data['notification']) {
                // @todo
                //wp_new_user_notification($userDataId);
            }

            $currentUser = get_userdata(get_current_user_id());

            $response['data'] = array(
                'id'          => $userDataId,
                'assigned_at' => current_time('Y-m-d H:i:s'), // @todo : convert to user's timezone
                'assigned_by' => $currentUser->display_name,
                'name'        => empty($data['first_name'] . ' ' . $data['last_name']) ? $data['first_name'] . ' ' . $data['last_name'] : $data['username'],
                'username'    => $userDataUsername,
                'email'       => $data['email']
            );

            $clientUsersMetaKey = '_upstream_new_client_users';
            $clientUsersList = (array)get_post_meta($clientId, $clientUsersMetaKey, true);
            array_push($clientUsersList, array(
                'user_id'     => $userDataId,
                'assigned_by' => $currentUser->ID,
                'assigned_at' => $response['data']['assigned_at']
            ));
            update_post_meta($clientId, $clientUsersMetaKey, $clientUsersList);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    public static function removeUser()
    {
        // @todo : permission checks?
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'err'     => null
        );

        try {
            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception("@todo");
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception("@todo");
            }

            $userId = (int)@$_POST['user'];
            if ($userId <= 0) {
                throw new \Exception("@todo");
            }

            $clientUsersMetaKey = '_upstream_new_client_users';
            $meta = (array)get_post_meta($clientId, $clientUsersMetaKey);
            if (!empty($meta)) {
                $newClientUsersList = array();
                foreach ($meta[0] as $clientUser) {
                    if (!empty($clientUser) && is_array($clientUser)) {
                        if ((int)$clientUser['user_id'] !== $userId) {
                            array_push($newClientUsersList, $clientUser);
                        }
                    }
                }

                update_post_meta($clientId, $clientUsersMetaKey, $newClientUsersList);
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    public static function fetchUnassignedUsers()
    {
        // @todo : permission checks?
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (empty($_GET) || !isset($_GET['client'])) {
                throw new \Exception("@todo");
            }

            $clientId = (int)$_GET['client'];
            if ($clientId <= 0) {
                throw new \Exception("@todo");
            }

            $clientUsers = self::getUsersFromClient($clientId);
            $excludeTheseIds = array(get_current_user_id());
            if (count($clientUsers) > 0) {
                foreach ($clientUsers as $clientUser) {
                    array_push($excludeTheseIds, $clientUser->id);
                }
            }

            $rowset = get_users(array(
                'exclude'  => $excludeTheseIds,
                'role__in' => array('upstream_client_user'),
                'orderby'  => 'ID'
            ));

            global $wp_roles;

            foreach ($rowset as $row) {
                $user = array(
                    'id'       => $row->ID,
                    'name'     => $row->display_name,
                    'username' => $row->user_login,
                    'email'    => $row->user_email
                );

                array_push($response['data'], $user);
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    public static function addExistentUsers()
    {
        // @todo : permission checks
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception("@todo");
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception("@todo");
            }

            if (!isset($_POST['users']) && empty($_POST['users'])) {
                throw new \Exception("@todo");
            }

            $currentUser = get_userdata(get_current_user_id());
            $now = current_time('Y-m-d H:i:s');

            $clientUsersMetaKey = '_upstream_new_client_users';
            $clientUsersList = (array)get_post_meta($clientId, $clientUsersMetaKey, true);
            $clientNewUsersList = array();

            $usersIdsList = (array)$_POST['users'];
            foreach ($usersIdsList as $user_id) {
                $user_id = (int)$user_id;
                if ($user_id > 0) {
                    array_push($clientUsersList, array(
                        'user_id'     => $user_id,
                        'assigned_by' => $currentUser->ID,
                        'assigned_at' => $now
                    ));
                }
            }

            foreach ($clientUsersList as $clientUser) {
                $clientUser = (array)$clientUser;
                $clientUser['user_id'] = (int)$clientUser['user_id'];

                if (!isset($clientNewUsersList[$clientUser['user_id']])) {
                    $clientNewUsersList[$clientUser['user_id']] = $clientUser;
                }
            }
            update_post_meta($clientId, $clientUsersMetaKey, array_values($clientNewUsersList));

            global $wpdb;

            $rowset = (array)$wpdb->get_results(sprintf('
                SELECT `ID`, `display_name`, `user_login`, `user_email`
                FROM `%s`
                WHERE `ID` IN ("%s")',
                $wpdb->prefix . 'users',
                implode('", "', $usersIdsList)
            ));

            foreach ($rowset as $user) {
                array_push($response['data'], array(
                    'id'          => (int)$user->ID,
                    'name'        => $user->display_name,
                    'email'       => $user->user_email,
                    'username'    => $user->user_login,
                    'assigned_by' => $currentUser->display_name,
                    'assigned_at' => $now
                ));
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }
}
