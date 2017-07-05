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

    private static function getUsers($client_id)
    {

    }

    public static function renderUsersMetabox()
    {
        $client_id = get_the_id();

        $usersList = self::getUsers($client_id);
        ?>

        <? // @todo: create js/css to make Thickbox responsive. ?>
        <a name="Add New User" href="#TB_inline?width=600&height=300&inlineId=modal-add-new-user" class="thickbox">Add New User</a>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
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
                    <td><?php echo $user->role; ?></td>
                    <td><?php echo $user->assigned_at; ?></td>
                    <td><?php echo $user->assigned_by; ?></td>
                    <td><a href="#" onclick="javascript:void(0);">x</a></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="7">There's no users assigned yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div id="modal-add-new-user" style="display: none;">
            <form id="form-add-new-user">
                <div class="up-form-group">
                    <label for="new-user-username">Username *</label>
                    <input type="text" name="username" id="new-user-username" required />
                </div>
                <div class="up-form-group">
                    <label for="new-user-email">Email *</label>
                    <input type="email" name="email" id="new-user-email" required />
                </div>
                <div class="up-form-group">
                    <label for="new-user-first_name">First Name</label>
                    <input type="text" name="first_name" id="new-user-first_name" />
                </div>
                <div class="up-form-group">
                    <label for="new-user-last_name">Last Name</label>
                    <input type="text" name="last_name" id="new-user-last_name" />
                </div>
                <div class="up-form-group">
                    <label>Password</label>
                    <button type="button">Show Password</button>
                </div>
                <div class="up-form-group">
                    <label>Send User Notification</label>
                    <label for="new-user-notification">
                        Send user info via email
                        <input type="checkbox" name="notification" id="new-user-notification" value="1" checked />
                    </label>
                </div>
                <button type="button">Add New User</button>
            </form>
        </div>
        <?php
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
            'title'        => '<span class="dashicons dashicons-admin-generic"></span>' . __("Details", 'upstream'),
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
}
