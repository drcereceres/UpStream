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
        // @todo
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
                    <h3>Credentials</h3>
                    <div class="up-form-group">
                        <label for="new-user-email">Email *</label>
                        <input type="email" name="email" id="new-user-email" required />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-username">Username *</label>
                        <input type="text" name="username" id="new-user-username" required />
                        <p>
                            Rules:
                            <ul>
                                <li>Must be between 3 and 60 characters long;</li>
                                <li>You may use <code>letters (a-z)</code>, <code>numbers (0-9)</code>, <code>-</code> and <code>_</code> symbols;</li>
                                <li>The first character must be a <code>letter</code>;</li>
                                <li>Everything will be lowercased.</li>
                            </ul>
                        </p>
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-password">Password *</label>
                        <input type="password" name="password" id="new-user-password" required />
                        <p>
                            @todo: password confirmation?
                            Rules:
                            <ul>
                                <li>Must be at least between 6 characters long.</li>
                            </ul>
                        </p>
                    </div>
                </div>
                <hr />
                <div>
                    <h3>Details</h3>
                    <div class="up-form-group">
                        <label for="new-user-first_name">First Name</label>
                        <input type="text" name="first_name" id="new-user-first_name" />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-last_name">Last Name</label>
                        <input type="text" name="last_name" id="new-user-last_name" />
                    </div>
                    <div class="up-form-group">
                        <label>Send User Notification</label>
                        <label for="new-user-notification">
                            Send user info via email
                            <input type="checkbox" name="notification" id="new-user-notification" value="1" checked />
                        </label>
                    </div>
                </div>
                <button type="submit">Add New User</button>
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
            <p>@todo: info message</p>
            <table>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" value="1" />
                        </th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Current Roles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($unassignedUsers) > 0): ?>
                    <?php foreach ($unassignedUsers as $user): ?>
                        <tr data-id="<?php echo $user->id; ?>">
                            <td>
                                <input type="checkbox" value="1" />
                            </td>
                            <td><?php echo $user->name; ?></td>
                            <td><?php echo $user->username; ?></td>
                            <td><?php echo $user->email; ?></td>
                            <td><?php echo count($user->roles) > 0 ? implode(', ', $user->roles) : "<i>none</i>"; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="5">All users seems to be assigned to this client.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="button">Add X user(s)</button>
        </div>
        <?php
    }

    public static function renderUsersMetabox()
    {
        $client_id = get_the_id();

        $usersList = self::getUsersFromClient($client_id);
        ?>

        <?php // @todo: create js/css to make Thickbox responsive. ?>
        <a name="Add New User" href="#TB_inline?width=600&height=400&inlineId=modal-add-new-user" class="thickbox">Add New User</a>
        <a name="Add Existent User" href="#TB_inline?width=600&height=300&inlineId=modal-add-existent-user" class="thickbox">Add Existent User</a>

        <table id="table-users">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Assigned at</th>
                    <th>Assigned by</th>
                    <th>Remove?</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($usersList) > 0): ?>
                <?php foreach ($usersList as $user): ?>
                <tr data-id="<?php echo $user->id; ?>">
                    <td><?php echo $user->name; ?></td>
                    <td><?php echo $user->username; ?></td>
                    <td><?php echo $user->email; ?></td>
                    <td><?php echo $user->assigned_at; ?></td>
                    <td><?php echo $user->assigned_by; ?></td>
                    <td><a href="#" onclick="javascript:void(0);">x</a></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr data-empty>
                    <td colspan="7">There's no users assigned yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

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

        // @todo: description/notes field

        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($phoneField, $websiteField, $addressField));
        /*
        $metaboxGridRow->addColumns(array(
            array($phoneField, 'class' => 'col-md-4'),
            array($websiteField, 'class' => 'col-md-4')
        ));
        */
        //$metaboxGridRow->addColumns(array($phoneField, $websiteField, $addressField));
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
        // @todo : nonce
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
                'role'          => 'upstream_user'
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
                'assigned_at' => current_time('Y-m-d H:i:s'), // convert to user's timezone
                'assigned_by' => $currentUser->display_name,
                'name'        => empty($data['first_name'] . ' '. $data['last_name']) ? $data['first_name'] . ' ' . $data['last_name'] : $data['username'],
                'username'    => $userDataUsername,
                'email'       => $data['email']
            );

            // @todo: change the meta-value key
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
}
