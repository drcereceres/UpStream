<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

use \UpStream\Traits\Singleton;
use \Cmb2Grid\Grid\Cmb2Grid;
use \UpStream\Migrations\ClientUsers as ClientUsersMigration;

/**
 * Clients Metabox Class.
 *
 * @package     UpStream
 * @subpackage  Admin\Metaboxes
 * @author      UpStream <https://upstreamplugin.com>
 * @copyright   Copyright (c) 2017 UpStream Project Management
 * @license     GPL-3
 * @since       1.11.0
 * @final
 */
final class UpStream_Metaboxes_Clients
{
    use Singleton;

    /**
     * The post type where this metabox will be used.
     *
     * @since   1.11.0
     * @access  protected
     * @static
     *
     * @var     string
     */
    protected static $postType = 'client';

    /**
     * String that represents the singular form of the post type's name.
     *
     * @since   1.11.0
     * @access  protected
     * @static
     *
     * @var     string
     */
    protected static $postTypeLabelSingular = null;

    /**
     * String that represents the plural form of the post type's name.
     *
     * @since   1.11.0
     * @access  protected
     * @static
     *
     * @var     string
     */
    protected static $postTypeLabelPlural = null;

    /**
     * Prefix used on form fields.
     *
     * @since   1.11.0
     * @access  protected
     * @static
     *
     * @var     string
     */
    protected static $prefix = '_upstream_client_';

    /**
     * Class constructor.
     *
     * @since   1.11.0
     */
    public function __construct()
    {
        self::$postTypeLabelSingular = upstream_client_label();
        self::$postTypeLabelPlural = upstream_client_label_plural();

        // Define all ajax endpoints.
        $ajaxEndpointsSchema = array(
            'add_new_user'            => 'addNewUser',
            'remove_user'             => 'removeUser',
            'fetch_unassigned_users'  => 'fetchUnassignedUsers',
            'add_existent_users'      => 'addExistentUsers',
            'migrate_legacy_user'     => 'migrateLegacyUser',
            'discard_legacy_user'     => 'discardLegacyUser',
            'fetch_user_permissions'  => 'fetchUserPermissions',
            'update_user_permissions' => 'updateUserPermissions'
        );

        $namespace = get_class(self::$instance);
        foreach ($ajaxEndpointsSchema as $endpoint => $callbackName) {
            add_action('wp_ajax_upstream:client.' . $endpoint, array($namespace, $callbackName));
        }

        // Enqueues the default ThickBox assets.
        add_thickbox();

        // Render all inner metaboxes.
        self::renderMetaboxes();

        global $pagenow;
        if ($pagenow === 'post.php') {
            $user_id = get_current_user_id();
            $disclaimerNoticeMetaName = '_upstream_legacy_users_disclaimer_notice';

            $disclaimerNotice = (array)get_user_meta($user_id, $disclaimerNoticeMetaName);
            $disclaimerNotice = !empty($disclaimerNotice) ? (bool)$disclaimerNotice[0] : false;

            if (!$disclaimerNotice) {
                add_action('admin_notices', array($namespace, 'renderDisclaimerMetabox'));
                update_user_meta($user_id, $disclaimerNoticeMetaName, 1);
            }
        }
    }

    /**
     * Render all inner-metaboxes.
     *
     * @since   1.11.0
     * @access  private
     * @static
     */
    private static function renderMetaboxes()
    {
        self::renderDetailsMetabox();
        self::renderLogoMetabox();

        $namespace = get_class(self::$instance);
        $metaboxesCallbacksList = array('createUsersMetabox', 'createLegacyUsersMetabox');
        foreach ($metaboxesCallbacksList as $callbackName) {
            add_action('add_meta_boxes', array($namespace, $callbackName));
        }
    }

    /**
     * Retrieve all Client Users associated with a given client.
     *
     * @since   1.11.0
     * @access  private
     * @static
     *
     * @param   int     $client_id  The reference id.
     *
     * @return  array
     */
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
                'id'    => (int)$row->ID,
                'name'  => $row->display_name,
                'email' => $row->user_email
            );
        }
        unset($rowset);

        $clientUsersList = array();
        $clientUsersIdsList = array();

        // Retrieve all client users.
        $meta = (array)get_post_meta($client_id, '_upstream_new_client_users');
        if (!empty($meta)) {
            foreach ($meta[0] as $clientUser) {
                if (!empty($clientUser) && is_array($clientUser) && isset($users[$clientUser['user_id']]) && !in_array($clientUser['user_id'], $clientUsersIdsList)) {
                    $user = $users[$clientUser['user_id']];

                    $user['assigned_at'] = $clientUser['assigned_at'];
                    $user['assigned_by'] = $users[$clientUser['assigned_by']]['name'];

                    array_push($clientUsersList, (object)$user);
                    array_push($clientUsersIdsList, $clientUser['user_id']);
                }
            }
        }

        return $clientUsersList;
    }

    /**
     * Renders the modal's html which is used to add new client users.
     *
     * @since   1.11.0
     * @access  private
     * @static
     */
    private static function renderAddNewUserModal()
    {
        ?>
        <div id="modal-add-new-user" style="display: none;">
            <div id="form-add-new-user">
                <div>
                    <div class="up-form-group">
                        <label for="new-user-email"><?php echo __('Email', 'upstream') .' *'; ?></label>
                        <input type="email" name="email" id="new-user-email" size="35" />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-password"><?php echo __('Password', 'upstream') .' *'; ?></label>
                        <input type="password" name="password" id="new-user-password" size="35" />
                        <p class="description up-help-block"><?php echo __('Must be at least 6 characters long.', 'upstream'); ?></p>
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-password_confirmation"><?php echo __('Confirm Password', 'upstream') .' *'; ?></label>
                        <input type="password" name="password_confirmation" id="new-user-password_confirmation" size="35" />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-first_name"><?php echo __('First Name', 'upstream'); ?></label>
                        <input type="text" name="first_name" id="new-user-first_name" size="35" />
                    </div>
                    <div class="up-form-group">
                        <label for="new-user-last_name"><?php echo __('Last Name', 'upstream'); ?></label>
                        <input type="text" name="last_name" id="new-user-last_name" size="35" />
                    </div>
                </div>
                <div>
                    <div class="up-form-group label-default">
                        <label style="margin-left: 13.5em;">
                            <input type="checkbox" name="notification" id="new-user-notification" value="1" checked />
                            <span><?php echo __('Send user info via email', 'upstream'); ?></span>
                        </label>
                    </div>
                    <div class="up-form-group">
                        <button type="submit" class="button button-primary" data-label="<?php echo __('Add New User', 'upstream'); ?>" data-loading-label="<?php echo __('Adding...', 'upstream'); ?>"><?php echo __('Add New User', 'upstream'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the modal's html which is used to associate existent client users with a client.
     *
     * @since   1.11.0
     * @access  private
     * @static
     */
    private static function renderAddExistentUserModal()
    {
        ?>
        <div id="modal-add-existent-user" style="display: none;">
            <div class="upstream-row">
                <p><?php echo sprintf(__('These are all the users assigned with the role <code>%s</code> and not related to this client yet.', 'upstream'), sprintf(__('%s Client User', 'upstream'), upstream_project_label())); ?></p>
            </div>
            <div class="upstream-row">
                <table id="table-add-existent-users" class="wp-list-table widefat fixed striped posts upstream-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 20px;">
                                <input type="checkbox" />
                            </th>
                            <th><?php echo __('Name', 'upstream'); ?></th>
                            <th><?php echo __('Email', 'upstream'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3"><?php echo __('No users found.', 'upstream'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="upstream-row submit"></div>
        </div>
        <?php
    }

    /**
     * Renders the modal's html which is used to migrate legacy client users.
     *
     * @since   1.11.0
     * @access  private
     * @static
     */
    private static function renderMigrateUserModal()
    {
        ?>
        <div id="modal-migrate-user" style="display: none;">
            <div id="form-migrate-user">
                <div>
                    <h3><?php echo __('Credentials', 'upstream'); ?></h3>
                    <div class="up-form-group">
                        <label for="migrate-user-email"><?php echo __('Email', 'upstream') . ' *'; ?></label>
                        <input type="email" name="email" id="migrate-user-email" required size="35" />
                    </div>
                    <div class="up-form-group">
                        <label for="migrate-user-password"><?php echo __('Password', 'upstream') . ' *'; ?></label>
                        <input type="password" name="password" id="migrate-user-password" required size="35" />
                        <p class="description up-help-block"><?php echo __('Must be at least 6 characters long.', 'upstream'); ?></p>
                    </div>
                    <div class="up-form-group">
                        <label for="migrate-user-password_c"><?php echo __('Confirm Password', 'upstream') . ' *'; ?></label>
                        <input type="password" name="password_c" id="migrate-user-password_c" required size="35" />
                    </div>
                    <hr />
                    <h3><?php echo __('Profile', 'upstream'); ?></h3>
                    <div class="up-form-group">
                        <label for="migrate-user-fname"><?php echo __('First Name', 'upstream'); ?></label>
                        <input type="text" name="fname" id="migrate-user-fname" size="35" />
                    </div>
                    <div class="up-form-group">
                        <label for="migrate-user-lname"><?php echo __('Last Name', 'upstream'); ?></label>
                        <input type="text" name="lname" id="migrate-user-lname" size="35" />
                    </div>
                </div>
                <div>
                    <div class="up-form-group">
                        <button type="submit" class="button button-primary" data-label="<?php echo __('Migrate User', 'upstream'); ?>" data-loading-label="<?php echo __('Migrating...', 'upstream'); ?>"><?php echo __('Migrate User', 'upstream'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Renders the users metabox.
     * This is where all client users are listed.
     *
     * @since   1.11.0
     * @static
     */
    public static function renderUsersMetabox()
    {
        $client_id = get_the_id();
        $usersList = self::getUsersFromClient($client_id);
        ?>

        <div class="upstream-row">
            <a
                name="<?php echo __('Add New User', 'upstream'); ?>"
                href="#TB_inline?width=600&height=360&inlineId=modal-add-new-user"
                class="thickbox button"
            ><?php echo __('Add New User', 'upstream'); ?></a>
            <a
                id="add-existent-user"
                name="<?php echo __('Add Existing Users', 'upstream'); ?>"
                href="#TB_inline?width=600&height=300&inlineId=modal-add-existent-user"
                class="thickbox button"
            ><?php echo __('Add Existing Users', 'upstream'); ?></a>
        </div>
        <div class="upstream-row">
            <table id="table-users" class="wp-list-table widefat fixed striped posts upstream-table">
                <thead>
                    <tr>
                        <th><?php echo __('Name', 'upstream'); ?></th>
                        <th><?php echo __('Email', 'upstream'); ?></th>
                        <th><?php echo __('Assigned by', 'upstream'); ?></th>
                        <th class="text-center"><?php echo __('Assigned at', 'upstream'); ?></th>
                        <th class="text-center"><?php echo __('Remove?', 'upstream'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usersList) > 0):
                    $timezone = get_option('timezone_string');
                    $timezone = !empty($timezone) ? $timezone : 'UTC';
                    $instanceTimezone = new DateTimeZone($timezone);
                    $dateFormat = get_option('date_format') . ' ' . get_option('time_format');

                    foreach ($usersList as $user):
                    $assignedAt = new DateTime($user->assigned_at);
                    // Convert the date, which is in UTC, to the instance's timezone.
                    $assignedAt->setTimeZone($instanceTimezone);
                    ?>
                    <tr data-id="<?php echo $user->id; ?>">
                        <td>
                            <a title="<?php echo sprintf(__("Managing %s's Permissions"), $user->name); ?>" href="#TB_inline?width=600&height=425&inlineId=modal-user-permissions" class="thickbox"><?php echo $user->name; ?></a>
                        </td>
                        <td><?php echo $user->email; ?></td>
                        <td><?php echo $user->assigned_by; ?></td>
                        <td class="text-center"><?php echo $assignedAt->format($dateFormat); ?></td>
                        <td class="text-center">
                            <a href="#" onclick="javascript:void(0);" class="up-u-color-red" data-remove-user>
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr data-empty>
                        <td colspan="5"><?php echo __("There are no users assigned yet.", 'upstream'); ?></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <p>
                <span class="dashicons dashicons-info"></span> <?php echo __('Removing a user only means that they will no longer be associated with this client. Their WordPress account will not be deleted.', 'upstream'); ?>
            </p>
        </div>

        <?php
        self::renderUserPermissionsModal();
        self::renderAddNewUserModal();
        self::renderAddExistentUserModal();
    }

    /**
     * Renders the Disclaimer metabox.
     *
     * @since   1.11.0
     * @static
     */
    public static function renderDisclaimerMetabox()
    {
        ?>
        <div class="notice notice-info is-dismissible">
            <h3><?php echo __("Please note: we made an important change to UpStream", 'upstream'); ?></h3>
            <div class="upstream-row">
                <p><?php echo sprintf(
                    __("<code>%s</code> are now <code>%s</code>. Clients will be able to log in using their own password and manage their very own profile.", 'upstream'),
                    __('UpStream Client Users', 'upstream'),
                    __('WordPress Users', 'upstream')
                ); ?></p>

                <ul class="up-list-disc">
                    <li><?php echo __('UpStream attempted to convert them automatically when the plugin was updated.', 'upstream'); ?></li>
                    <li><?php echo __('Client Users that <strong>could not</strong> be automatically converted for some reason will be listed in the <code>Legacy Users</code> box on this page. They can be manually either converted/migrated or discarded.', 'upstream'); ?></li>
                    <li><?php echo __("Client Users that were <strong>successfully</strong> converted will have the same permissions they have before and their email address is now their new password. Please make sure that they change their password.", 'upstream'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * It defines the Users metabox.
     *
     * @since   1.11.0
     * @static
     */
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

    /**
     * It defines the Legacy Users metabox.
     * This metabox lists all legacy client users that couldn't be automatically
     * migrated for some reason, which is also displayed here.
     *
     * If there's no legacy user to be migrated, the box is not shown.
     *
     * @since   1.11.0
     * @static
     */
    public static function createLegacyUsersMetabox()
    {
        $client_id = upstream_post_id();

        $legacyUsersErrors = (array)get_post_meta($client_id, '_upstream_client_legacy_users_errors');
        if (count($legacyUsersErrors) === 0 || empty($legacyUsersErrors[0])) {
            delete_post_meta($client_id, '_upstream_client_legacy_users_errors');
            return;
        }

        add_meta_box(
            self::$prefix . 'legacy_users',
            '<span class="dashicons dashicons-groups"></span>' . __("Legacy Users", 'upstream'),
            array(get_class(self::$instance), 'renderLegacyUsersMetabox'),
            self::$postType,
            'normal'
        );
    }

    /**
     * Renders the Legacy Users metabox.
     *
     * @since   1.11.0
     * @static
     */
    public static function renderLegacyUsersMetabox()
    {
        $client_id = upstream_post_id();

        $legacyUsersErrors = get_post_meta($client_id, '_upstream_client_legacy_users_errors')[0];

        $legacyUsersMeta = get_post_meta($client_id, '_upstream_client_users')[0];
        $legacyUsers = array();
        foreach ($legacyUsersMeta as $a) {
            $legacyUsers[$a['id']] = $a;
        }
        unset($legacyUsersMeta);
        ?>
        <div class="upstream-row">
            <p><?php echo __('The users listed below are those old <code>UpStream Client Users</code> that could not be automatically converted/migrated to <code>WordPress Users</code> by UpStream for some reason. More details on the Disclaimer metabox.', 'upstream'); ?></p>
        </div>
        <div class="upstream-row">
            <table id="table-legacy-users" class="wp-list-table widefat fixed striped posts upstream-table">
                <thead>
                    <tr>
                        <th><?php echo __('First Name', 'upstream'); ?></th>
                        <th><?php echo __('Last Name', 'upstream'); ?></th>
                        <th><?php echo __('Email', 'upstream'); ?></th>
                        <th><?php echo __('Phone', 'upstream'); ?></th>
                        <th class="text-center"><?php echo __('Migrate?', 'upstream'); ?></th>
                        <th class="text-center"><?php echo __('Discard?', 'upstream'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($legacyUsersErrors as $legacyUserId => $legacyUserError):
                $user = $legacyUsers[$legacyUserId];
                $userFirstName = isset($user['fname']) ? trim($user['fname']) : '';
                $userLastName = isset($user['lname']) ? trim($user['lname']) : '';
                $userEmail = isset($user['email']) ? trim($user['email']) : '';
                $userPhone = isset($user['phone']) ? trim($user['phone']) : '';

                switch ($legacyUserError) {
                    case 'ERR_EMAIL_NOT_AVAILABLE':
                        $errorMessage = __("This email address is already being used by another user.", 'upstream');
                        break;
                    case 'ERR_EMPTY_EMAIL':
                        $errorMessage = __("Email addresses cannot be empty.", 'upstream');
                        break;
                    case 'ERR_INVALID_EMAIL':
                        $errorMessage = __("Invalid email address.", 'upstream');
                        break;
                    default:
                        $errorMessage = $legacyUserError;
                        break;
                }

                $emptyValueString = '<i>' . __('empty', 'upstream') .'</i>';
                ?>
                    <tr data-id="<?php echo $legacyUserId; ?>">
                        <td data-column="fname"><?php echo !empty($userFirstName) ? $userFirstName : $emptyValueString; ?></td>
                        <td data-column="lname"><?php echo !empty($userLastName) ? $userLastName : $emptyValueString; ?></td>
                        <td data-column="email"><?php echo !empty($userEmail) ? $userEmail : $emptyValueString; ?></td>
                        <td data-column="phone"><?php echo !empty($userPhone) ? $userPhone : $emptyValueString; ?></td>
                        <td class="text-center">
                            <a name="<?php echo __('Migrating Client User', 'upstream'); ?>" href="#TB_inline?width=350&height=400&inlineId=modal-migrate-user" class="thickbox" data-modal-identifier="user-migration">
                                <span class="dashicons dashicons-plus-alt"></span>
                            </a>
                        </td>
                        <td class="text-center">
                            <a href="#" onclick="javascript:void(0);" class="up-u-color-red" data-action="legacyUser:discard">
                                <span class="dashicons dashicons-trash"></span>
                            </a>
                        </td>
                    </tr>
                    <tr data-id="<?php echo $legacyUserId; ?>">
                        <td colspan="7">
                            <span class="dashicons dashicons-warning"></span>&nbsp;<?php echo $errorMessage; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php self::renderMigrateUserModal(); ?>
        </div>
        <?php
    }

    /**
     * Renders the Details metabox using CMB2.
     *
     * @since   1.11.0
     * @static
     */
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

        $addressField = $metabox->add_field(array(
            'name' => __('Address', 'upstream'),
            'id'   => self::$prefix . 'address',
            'type' => 'textarea_small'
        ));

        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($phoneField, $websiteField, $addressField));
    }

    /**
     * Renders Logo metabox using CMB2.
     *
     * @since   1.11.0
     * @static
     */
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
            'id'   => self::$prefix . 'logo',
            'type' => 'file',
            'name' => __('Image URL', 'upstream')
        ));

        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($logoField));
    }

    /**
     * Ajax endpoint responsible for adding new Client Users to the system and client.
     *
     * @since   1.11.0
     * @static
     */
    public static function addNewUser()
    {
        header('Content-Type: application/json');

        global $wpdb;

        $response = array(
            'success' => false,
            'data'    => null,
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
            }

            $data = array(
                'email'        => isset($_POST['email']) ? $_POST['email'] : '',
                'password'     => isset($_POST['password']) ? $_POST['password'] : '',
                'password_c'   => isset($_POST['password_c']) ? $_POST['password_c'] : '',
                'first_name'   => trim(@$_POST['first_name']),
                'last_name'    => trim(@$_POST['last_name']),
                'notification' => isset($_POST['notification']) ? (bool)$_POST['notification'] : true
            );

            // Validate `password` field.
            if (strlen($data['password']) < 6) {
                throw new \Exception(__("Password must be at least 6 characters long.", 'upstream'));
            }

            if (strcmp($data['password'], $data['password_c']) !== 0) {
                throw new \Exception(__("Passwords don't match.", 'upstream'));
            }

            // Validate the `email` field.
            $userDataEmail = trim($data['email']);
            if (!filter_var($userDataEmail, FILTER_VALIDATE_EMAIL) || !is_email($userDataEmail)) {
                throw new \Exception(__("Invalid email.", 'upstream'));
            } else {
                $emailExists = (bool)$wpdb->get_var(sprintf('
                    SELECT COUNT(`ID`)
                    FROM `%s`
                    WHERE `user_email` = "%s"',
                    $wpdb->prefix . 'users',
                    $data['email']
                ));

                if ($emailExists) {
                    throw new \Exception(__("This email address is not available.", 'upstream'));
                }
            }

            $userDataDisplayName = trim($data['first_name'] . ' ' . $data['last_name']);
            $userDataDisplayName = !empty($userDataDisplayName) ? $userDataDisplayName : $data['email'];

            $userData = array(
                'user_login'    => $userDataDisplayName,
                'user_pass'     => $data['password'],
                'user_nicename' => $userDataEmail,
                'user_email'    => $userDataEmail,
                'display_name'  => $userDataEmail,
                'nickname'      => $userDataDisplayName,
                'first_name'    => $data['first_name'],
                'last_name'     => $data['last_name'],
                'role'          => 'upstream_client_user'
            );

            $userDataId = wp_insert_user($userData);
            if (is_wp_error($userDataId)) {
                throw new \Exception($userDataId->get_error_message());
            }

            if ($data['notification']) {
                wp_new_user_notification($userDataId);
            }

            $currentUser = get_userdata(get_current_user_id());

            $nowTimestamp = time();

            $response['data'] = array(
                'id'          => $userDataId,
                'assigned_at' => upstream_convert_UTC_date_to_timezone($nowTimestamp),
                'assigned_by' => $currentUser->display_name,
                'name'        => $userDataDisplayName,
                'email'       => $userDataEmail
            );

            $clientUsersMetaKey = '_upstream_new_client_users';
            $clientUsersList = (array)get_post_meta($clientId, $clientUsersMetaKey, true);
            array_push($clientUsersList, array(
                'user_id'     => $userDataId,
                'assigned_by' => $currentUser->ID,
                'assigned_at' => date('Y-m-d H:i:s', $nowTimestamp)
            ));
            update_post_meta($clientId, $clientUsersMetaKey, $clientUsersList);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    /**
     * Ajax endpoint responsible for removing Client Users from a given client.
     *
     * @since   1.11.0
     * @static
     */
    public static function removeUser()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
            }

            $userId = (int)@$_POST['user'];
            if ($userId <= 0) {
                throw new \Exception(__('Invalid User ID.', 'upstream'));
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

    /**
     * Ajax endpoint responsible for fetching all Client Users that are not related to
     * the given client.
     *
     * @since   1.11.0
     * @static
     */
    public static function fetchUnassignedUsers()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_GET) || !isset($_GET['client'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $clientId = (int)$_GET['client'];
            if ($clientId <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
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

    /**
     * Ajax endpoint responsible for associating existent Client Users to a given client.
     *
     * @since   1.11.0
     * @static
     */
    public static function addExistentUsers()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $clientId = (int)$_POST['client'];
            if ($clientId <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
            }

            if (!isset($_POST['users']) && empty($_POST['users'])) {
                throw new \Exception(__('Users IDs cannot be empty.', 'upstream'));
            }

            $currentUser = get_userdata(get_current_user_id());
            $nowTimestamp = time();
            $now = date('Y-m-d H:i:s', $nowTimestamp);

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

            $assignedAt = upstream_convert_UTC_date_to_timezone($now);

            foreach ($rowset as $user) {
                array_push($response['data'], array(
                    'id'          => (int)$user->ID,
                    'name'        => $user->display_name,
                    'email'       => $user->user_email,
                    'assigned_by' => $currentUser->display_name,
                    'assigned_at' => $assignedAt
                ));
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    /**
     * Ajax endpoint responsible for migrating a given Legacy Client User.
     *
     * @since   1.11.0
     * @static
     */
    public static function migrateLegacyUser()
    {
        header('Content-Type: application/json');

        global $wpdb;

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__("Invalid UpStream Client ID.", 'upstream'));
            }

            $client_id = (int)$_POST['client'];

            $data = array(
                'id'         => isset($_POST['user_id']) ? $_POST['user_id'] : null,
                'email'      => isset($_POST['email']) ? $_POST['email'] : null,
                'password'   => isset($_POST['password']) ? $_POST['password'] : "",
                'password_c' => isset($_POST['password_c']) ? $_POST['password_c'] : "",
                'fname'      => isset($_POST['first_name']) ? $_POST['first_name'] : null,
                'lname'      => isset($_POST['last_name']) ? $_POST['last_name'] : null
            );

            $userData = ClientUsersMigration::insertNewClientUser($data, $client_id);
            $response['data'] = $userData;

            $legacy_user_id = $userData['legacy_id'];
            $user_id = $userData['id'];

            // Update the '_upstream_client_users' meta.
            $meta = (array)get_post_meta($client_id, '_upstream_client_users');
            if (!empty($meta)) {
                $meta = $meta[0];
                foreach ($meta as $legacyUserIndex => $legacyUser) {
                    if (isset($legacyUser['id']) && $legacyUser['id'] === $data['id']) {
                        unset($meta[$legacyUserIndex]);
                    }
                }

                update_post_meta($client_id, '_upstream_client_users', $meta);
            }

            // Update the '_upstream_client_legacy_users_errors' meta.
            $meta = (array)get_post_meta($client_id, '_upstream_client_legacy_users_errors');
            if (!empty($meta)) {
                $meta = $meta[0];
                foreach ($meta as $legacyUserId => $legacyUserError) {
                    if ($legacyUserId === $data['id']) {
                        unset($meta[$legacyUserId]);
                    }
                }

                update_post_meta($client_id, '_upstream_client_legacy_users_errors', $meta);
            }

            $rowset = $wpdb->get_results('
                SELECT `post_id`, `meta_key`, `meta_value`
                FROM `' . $wpdb->prefix . 'postmeta`
                WHERE `meta_key` LIKE "_upstream_project_%"
                ORDER BY `post_id` ASC'
            );

            if (count($rowset) > 0) {
                $convertUsersLegacyIdFromHaystack = function(&$haystack) use ($legacy_user_id, $user_id) {
                    foreach ($haystack as &$needle) {
                        if ($needle === $legacy_user_id) {
                            $needle = $user_id;
                        }
                    }
                };

                foreach ($rowset as $projectMeta) {
                    $project_id = (int)$projectMeta->post_id;

                    if ($projectMeta->meta_key === '_upstream_project_activity') {
                        $metaValue = (array)maybe_unserialize($projectMeta->meta_value);
                        foreach ($metaValue as $activityIndex => $activity) {
                            if ($activity['user_id'] === $legacy_user_id) {
                                $activity['user_id'] = $user_id;
                            }

                            if (isset($activity['fields'])) {
                                if (isset($activity['fields']['single'])) {
                                    foreach ($activity['fields']['single'] as $activitySingleIndentifier => $activitySingle) {
                                        if ($activitySingleIndentifier === '_upstream_project_client_users') {
                                            if (isset($activitySingle['add'])) {
                                                if (is_array($activitySingle['add'])) {
                                                    $convertUsersLegacyIdFromHaystack($activitySingle['add']);
                                                }
                                            }

                                            if (isset($activitySingle['from'])) {
                                                if (is_array($activitySingle['from'])) {
                                                    $convertUsersLegacyIdFromHaystack($activitySingle['from']);
                                                }
                                            }

                                            if (isset($activitySingle['to'])) {
                                                if (is_array($activitySingle['to'])) {
                                                    $convertUsersLegacyIdFromHaystack($activitySingle['to']);
                                                }
                                            }
                                        }

                                        $activity['fields']['single'][$activitySingleIndentifier] = $activitySingle;
                                    }
                                }

                                if (isset($activity['fields']['group'])) {
                                    foreach ($activity['fields']['group'] as $groupIdentifier => $groupData) {
                                        if (isset($groupData['add'])) {
                                            foreach ($groupData['add'] as $rowIndex => $row) {
                                                if (isset($row['created_by']) && $row['created_by'] === $legacy_user_id) {
                                                    $row['created_by'] = $user_id;
                                                    $groupData['add'][$rowIndex] = $row;
                                                }
                                            }
                                        }

                                        if (isset($groupData['remove'])) {
                                            foreach ($groupData['remove'] as $rowIndex => $row) {
                                                if (isset($row['created_by']) && $row['created_by'] === $legacy_user_id) {
                                                    $row['created_by'] = $user_id;
                                                    $groupData['remove'][$rowIndex] = $row;
                                                }
                                            }
                                        }

                                        $activity['fields']['group'][$groupIdentifier] = $groupData;
                                    }
                                }
                            }

                            $metaValue[$activityIndex] = $activity;
                        }

                        update_post_meta($project_id, $projectMeta->meta_key, $metaValue);
                    } else if ($projectMeta->meta_key === '_upstream_project_discussion') {
                        $metaValue = (array)maybe_unserialize($projectMeta->meta_value);
                        foreach ($metaValue as $commentIndex => $comment) {
                            if ($comment['created_by'] === $legacy_user_id) {
                                $comment['created_by'] = $user_id;
                                $metaValue[$commentIndex] = $comment;
                            }
                        }

                        update_post_meta($project_id, $projectMeta->meta_key, $metaValue);
                    } else if (preg_match('/(milestones|tasks|bugs|files)$/i', $projectMeta->meta_key)) {
                        $metaValue = (array)maybe_unserialize($projectMeta->meta_value);
                        foreach ($metaValue as $rowIndex => $row) {
                            if (isset($row['created_by']) && $row['created_by'] === $legacy_user_id) {
                                $row['created_by'] = $user_id;

                                $metaValue[$rowIndex] = $row;
                            }
                        }

                        update_post_meta($project_id, $projectMeta->meta_key, $metaValue);
                    }
                }
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    /**
     * Ajax endpoint responsible for discard a given Legacy Client User.
     *
     * @since   1.11.0
     * @static
     */
    public static function discardLegacyUser()
    {
        header('Content-Type: application/json');

        global $wpdb;

        $response = array(
            'success' => false,
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__("Invalid UpStream Client ID.", 'upstream'));
            }

            $client_id = (int)$_POST['client'];
            $user_id = isset($_POST['user_id']) ? trim($_POST['user_id']) : '';

            if (empty($user_id)) {
                throw new \Exception(__("Invalid UpStream Client ID.", 'upstream'));
            }

            // Update the '_upstream_client_legacy_users_errors' meta.
            $meta = (array)get_post_meta($client_id, '_upstream_client_legacy_users_errors');
            if (!empty($meta)) {
                $meta = $meta[0];
                foreach ($meta as $legacyUserId => $legacyUserError) {
                    if ($legacyUserId === $user_id) {
                        unset($meta[$legacyUserId]);
                    }
                }

                update_post_meta($client_id, '_upstream_client_legacy_users_errors', $meta);
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    /**
     * Renders the modal's html which is used to manage a given Client User's permissions.
     *
     * @since   1.11.0
     * @access  private
     * @static
     */
    private static function renderUserPermissionsModal()
    {
        ?>
        <div id="modal-user-permissions" style="display: none;">
            <div id="form-user-permissions">
                <div>
                    <h3><?php echo __("UpStream's Custom Permissions", 'upstream'); ?></h3>
                    <table class="wp-list-table widefat fixed striped posts upstream-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 20px;">
                                    <input type="checkbox" />
                                </th>
                                <th><?php echo __('Permission', 'upstream'); ?></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div>
                    <div class="up-form-group">
                        <button
                            type="submit"
                            class="button button-primary"
                            data-label="<?php echo __('Update Permissions', 'upstream'); ?>"
                            data-loading-label="<?php echo __('Updating...', 'upstream'); ?>"
                        ><?php echo __('Update Permissions', 'upstream'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Ajax endpoint responsible for fetching all permissions a given Client User might have.
     *
     * @since   1.11.0
     * @static
     */
    public static function fetchUserPermissions()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'data'    => array(),
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_GET) || !isset($_GET['client']) || !isset($_GET['user'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $client_id = (int)$_GET['client'];
            if ($client_id <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
            }

            $client_user_id = (int)$_GET['user'];
            if ($client_user_id <= 0) {
                throw new \Exception(__('Invalid User ID.', 'upstream'));
            }

            if (!upstream_do_client_user_belongs_to_client($client_user_id, $client_id)) {
                throw new \Exception(__("This Client User is not associated with this Client.", 'upstream'));
            }

            $response['data'] = array_values(upstream_get_client_user_permissions($client_user_id));

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }

    /**
     * Ajax endpoint responsible for updating a given Client User permissions.
     *
     * @since   1.11.0
     * @static
     */
    public static function updateUserPermissions()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'err'     => null
        );

        try {
            if (!upstream_admin_permissions('edit_clients')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            if (empty($_POST) || !isset($_POST['client'])) {
                throw new \Exception(__('Invalid request.', 'upstream'));
            }

            $client_id = (int)$_POST['client'];
            if ($client_id <= 0) {
                throw new \Exception(__('Invalid Client ID.', 'upstream'));
            }

            $client_user_id = isset($_POST['user']) ? (int)$_POST['user'] : 0;
            if ($client_user_id <= 0) {
                throw new \Exception(__('Invalid User ID.', 'upstream'));
            }

            if (!upstream_do_client_user_belongs_to_client($client_user_id, $client_id)) {
                throw new \Exception(__("This Client User is not associated with this Client.", 'upstream'));
            }

            $clientUser = new \WP_User($client_user_id);
            if (array_search('upstream_client_user', $clientUser->roles) === false) {
                throw new \Exception(__("This user doesn't seem to be a valid Client User.", 'upstream'));
            }

            if (isset($_POST['permissions']) && !empty($_POST['permissions'])) {
                $permissions = upstream_get_client_users_permissions();
                $newPermissions = (array)$_POST['permissions'];

                $deniedPermissions = (array)array_diff(array_keys($permissions), $newPermissions);
                foreach ($deniedPermissions as $permissionKey) {
                    // Make sure this is a valid permission.
                    if (isset($permissions[$permissionKey])) {
                        $clientUser->add_cap($permissionKey, false);
                    }
                }

                foreach ($newPermissions as $permissionKey) {
                    // Make sure this is a valid permission.
                    if (isset($permissions[$permissionKey])) {
                        $clientUser->add_cap($permissionKey, true);
                    }
                }
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['err'] = $e->getMessage();
        }

        echo wp_json_encode($response);

        wp_die();
    }
}
