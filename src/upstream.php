<?php
/**
 * Plugin Name: UpStream
 * Description: A WordPress Project Management plugin by UpStream.
 * Author: UpStream
 * Author URI: https://upstreamplugin.com
 * Version: 1.16.3
 * Text Domain: upstream
 * Domain Path: /languages
 */

use \UpStream\Comments;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream' ) ) :

/**
 * Main UpStream Class.
 *
 * @since 1.0.0
 */
final class UpStream
{
    /**
     * @var UpStream The one true UpStream
     * @since 1.0.0
     */
    protected static $_instance = null;

    /**
     * Main UpStream Instance.
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Throw error on object clone.
     *
     * The whole idea of the singleton design pattern is that there is a single
     * object therefore, we don't want the object to be cloned.
     *
     * @since   1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, 'You\'re not supposed to clone this class.', UPSTREAM_VERSION);
    }

    /**
     * Disable unserializing of the class.
     *
     * @since   1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, 'You\'re not supposed to unserialize this class.', UPSTREAM_VERSION);
    }

    /**
     * Prevent the class instance being serialized.
     *
     * @since   1.10.2
     */
    public function __sleep()
    {
        _doing_it_wrong(__FUNCTION__, 'You\'re not supposed to serialize this class.', UPSTREAM_VERSION);
    }

    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        do_action( 'upstream_loaded' );
    }

    /**
     * Hook into actions and filters.
     * @since  1.0.0
     */
    private function init_hooks()
    {
        add_action( 'init', array( $this, 'init' ), 0 );
        add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
        add_filter('plugin_action_links_upstream/upstream.php', array($this, 'handleActionLinks'));
        add_filter('http_request_host_is_external', array('UpStream', 'allowExternalUpdateHost'), 10, 3);
        add_filter('quicktags_settings', 'upstream_tinymce_quicktags_settings');
        add_filter('tiny_mce_before_init', 'upstream_tinymce_before_init_setup_toolbar');
        add_filter('tiny_mce_before_init', 'upstream_tinymce_before_init');
        add_filter('teeny_mce_before_init', 'upstream_tinymce_before_init_setup_toolbar');
        add_filter('comments_clauses', array($this, 'filterCommentsOnDashboard'), 10, 2);
        add_filter('views_dashboard', array('UpStream_Admin', 'commentStatusLinks'), 10, 1);

        // Render additional update info if needed.
        global $pagenow;
        if ($pagenow === "plugins.php") {
            add_action('in_plugin_update_message-' . UPSTREAM_PLUGIN_BASENAME, array($this, 'renderAdditionalUpdateInfo'), 20, 2);
        }
    }

    /**
     * Prevent a Client User from accessing any page other than the profile.
     *
     * @since   1.11.0
     *
     * @global  $pagenow
     */
    public function limitClientUsersAdminAccess()
    {
        global $pagenow;

        $profilePage = 'profile.php';
        if ($pagenow !== $profilePage && $pagenow !== "edit.php" && !wp_doing_ajax()) {
            wp_redirect(admin_url($profilePage));
            exit;
        }
    }

    /**
     * Make sure Client Users can only see the Profile menu item.
     *
     * @since   1.11.0
     *
     * @global  $menu
     */
    public function limitClientUsersMenu()
    {
        global $menu;

        foreach ($menu as $menuIndex => $menuData) {
            $menuFile = isset($menuData[2]) ? $menuData[2] : null;
            if ($menuFile !== null) {
                if ($menuFile === 'profile.php' || $menuFile === 'edit.php?post_type=project') {
                    continue;
                }

                remove_menu_page($menuFile);
            }
        }
    }

    /**
     * Hide some toolbar items from Client Users.
     *
     * @since   1.11.0
     *
     * @param   \WP_Admin_Bar   $wp_admin_bar
     */
    public function limitClientUsersToolbarItems($wp_admin_bar)
    {
        $user = wp_get_current_user();
        $userRoles = (array)$user->roles;

        if (count(array_intersect($userRoles, array('administrator', 'upstream_manager'))) === 0 && in_array('upstream_client_user', $userRoles)) {
            $menuItems = array('about', 'comments', 'new-content');

            if (!is_admin()) {
                $menuItems = array_merge($menuItems, array('dashboard', 'edit'));
            }

            foreach ($menuItems as $menuItem) {
                $wp_admin_bar->remove_menu($menuItem);
            }
        }
    }

    /**
     * Define Constants.
     * @since  1.0.0
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir();
        $this->define( 'UPSTREAM_PLUGIN_FILE', __FILE__ );
        $this->define( 'UPSTREAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        $this->define( 'UPSTREAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'UPSTREAM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
        $this->define( 'UPSTREAM_VERSION', '1.16.3' );
    }

    /**
     * Define constant if not already set.
     * @since  1.0.0
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value )
    {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * What type of request is this?
     * string $type frontend or admin.
     * @since  1.0.0
     * @return bool
     */
    private function is_request( $type )
    {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     * @since  1.0.0
     */
    public function includes()
    {
        include_once( 'includes/up-install.php' );
        include_once( 'includes/class-up-autoloader.php' );
        include_once( 'includes/class-up-roles.php' );
        include_once( 'includes/class-up-counts.php' );
        include_once( 'includes/class-up-project-activity.php' );
        include_once( 'includes/up-post-types.php' );
        include_once( 'includes/up-labels.php' );
        include_once( 'includes/trait-up-singleton.php' );
        include_once( 'includes/abs-class-up-struct.php' );

        if ( $this->is_request( 'admin' ) ) {
            global $pagenow;

            $isMultisite = (bool)is_multisite();
            $loadCmb2 = false;

            if ($isMultisite) {
                $currentPage = isset($_SERVER['PHP_SELF']) ? preg_replace('/^\/wp-admin\//i', '', $_SERVER['PHP_SELF']) : '';
            } else {
                $currentPage = (string)$pagenow;
            }

            if (in_array($currentPage, array('post.php', 'post-new.php'))) {
                $postType = isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : null;
                if (empty($postType)) {
                    $projectId = isset($_REQUEST['post']) ? (int)$_REQUEST['post'] : 0;
                    $postType = get_post_type($projectId);
                }

                if (!empty($postType)) {
                    $postTypesUsingCmb2 = apply_filters('upstream:post_types_using_cmb2', array('project', 'client'));
                    $loadCmb2 = in_array($postType, $postTypesUsingCmb2);
                }
            } else if ($currentPage === 'admin.php'
                && isset($_REQUEST['page'])
                && preg_match('/^upstream_/i', $_REQUEST['page'])
            ) {
                $loadCmb2 = true;
            }

            if ($loadCmb2) {
                include_once('includes/libraries/cmb2/init.php');
                include_once('includes/libraries/cmb2-grid/Cmb2GridPlugin.php');
            }

            include_once( 'includes/admin/class-up-admin.php' );
            include_once( 'includes/admin/class-up-admin-tasks-page.php' );
            include_once( 'includes/admin/class-up-admin-bugs-page.php' );
        }

        if ( $this->is_request( 'frontend' ) ) {
            include_once( 'includes/frontend/class-up-template-loader.php' );
            include_once( 'includes/frontend/class-up-login.php' );
            include_once( 'includes/frontend/class-up-style-output.php' );
            include_once( 'includes/frontend/up-enqueues.php' );
            include_once( 'includes/frontend/up-template-functions.php' );
            include_once( 'includes/frontend/up-table-functions.php' );
            include_once( 'includes/frontend/class-up-view.php' );
        }

        include_once( 'includes/up-general-functions.php' );
        include_once( 'includes/up-project-functions.php' );
        include_once( 'includes/up-client-functions.php' );
        include_once( 'includes/up-permissions-functions.php' );
        include_once( 'includes/up-comments-migration.php' );
        include_once( 'includes/class-up-comments.php' );
        include_once( 'includes/class-up-comment.php' );
    }

    /**
     * Init UpStream when WordPress Initialises.
     */
    public function init()
    {
        // Before init action.
        do_action( 'before_upstream_init' );
        // Set up localisation.
        $this->load_plugin_textdomain();
        // Load class instances.
        $this->project = new UpStream_Project();
        $this->project_activity = new UpStream_Project_Activity();

        // If PHP < 5.5, loads a library intended to provide forward compatibility with the password_* functions that ship with PHP 5.5.
        if (version_compare(PHP_VERSION, '5.5', '<')) {
            require_once UPSTREAM_PLUGIN_DIR . 'includes/libraries/password_compat-1.0.4/lib/password.php';
        }

        // Make sure "UpStream Client Users" role is added in existent instances.
        UpStream_Roles::addClientUsersRole();

        // Executes the Legacy Client Users Migration script if needed.
        \UpStream\Migrations\Comments::run();

        $user = wp_get_current_user();
        $userRoles = (array)$user->roles;
        if (count(array_intersect($userRoles, array('administrator', 'upstream_manager'))) === 0 && in_array('upstream_client_user', $userRoles)) {
            add_filter('admin_init', array($this, 'limitClientUsersAdminAccess'));
            add_filter('admin_head', array($this, 'limitClientUsersMenu'));
            add_action('admin_bar_menu', array($this, 'limitClientUsersToolbarItems'), 999);
        }

        // Starting from v1.12.5 UpStream Users role won't have 'edit_others_projects' capability by default.
        $editOtherProjectsPermissionWereRemoved = (bool)get_option('upstream:role_upstream_users:drop_edit_others_projects');
        if (!$editOtherProjectsPermissionWereRemoved) {
            $role = get_role('upstream_user');
            $role->remove_cap('edit_others_projects');
            unset($role);

            update_option('upstream:role_upstream_users:drop_edit_others_projects', 1);
        }

        Comments::instantiate();

        // Init action.
        do_action( 'upstream_init' );
    }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     *
     * Locales found in:
     *      - WP_LANG_DIR/upstream/upstream-LOCALE.mo
     *      - WP_LANG_DIR/plugins/upstream-LOCALE.mo
     */
    public function load_plugin_textdomain()
    {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'upstream' );

        load_textdomain( 'upstream', WP_LANG_DIR . '/upstream/upstream-' . $locale . '.mo' );
        load_plugin_textdomain( 'upstream', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
    }


    /**
     * Show row meta on the plugin screen.
     *
     * @param   mixed $links Plugin Row Meta
     * @param   mixed $file  Plugin Base file
     * @return  array
     */
    public function plugin_row_meta( $links, $file )
    {
        if ( $file == UPSTREAM_PLUGIN_BASENAME ) {
            $row_meta = array(
                'docs' => '<a href="' . esc_url( 'http://upstreamplugin.com/documentation' ) . '" title="' . esc_attr( __( 'View Documentation', 'upstream' ) ) . '">' . __( 'Docs', 'upstream' ) . '</a>',
                'quick-start' => '<a href="' . esc_url( 'http://upstreamplugin.com/quick-start-guide' ) . '" title="' . esc_attr( __( 'View Quick Start Guide', 'upstream' ) ) . '">' . __( 'Quick Start Guide', 'upstream' ) . '</a>',
            );

            return array_merge( $links, $row_meta );
        }

        return (array) $links;
    }

    /**
     * Callback called to setup the links to display on the plugins page, besides active/deactivate links.
     *
     * @since   1.11.1
     * @static
     *
     * @param   array   $links  The list of links to be displayed.
     *
     * @return  array
     */
    public static function handleActionLinks($links)
    {
        $links['settings'] = sprintf(
            '<a href="%s" title="%2$s" aria-label="%2$s">%3$s</a>',
            admin_url('admin.php?page=upstream_general'),
            __('Open Settings Page', 'upstream'),
            __('Settings', 'upstream')
        );

        return $links;
    }

    /**
     * Ensures the plugins update API's host is whitelisted to WordPress external requests.
     *
     * @since   1.11.1
     * @static
     *
     * @param   boolean     $isAllowed
     * @param   string      $host
     * @param   string      $url
     *
     * @return  boolean
     */
    public static function allowExternalUpdateHost($isAllowed, $host, $url)
    {
        if ($host === 'upstreamplugin.com') {
            return true;
        }

        return $isAllowed;
    }

    /**
     * Render additional update info if needed.
     *
     * @since   1.12.5
     * @static
     *
     * @see     https://developer.wordpress.org/reference/hooks/in_plugin_update_message-file
     *
     * @param   array   $pluginData     Plugin metadata.
     * @param   object  $response       Metadata about the available plugin update.
     */
    public static function renderAdditionalUpdateInfo($pluginData, $response)
    {
        $updateNoticeTitleHtml = sprintf('<strong style="font-size: 1.25em; display: block; margin-top: 10px;">%s</strong>', __('Update notice:', 'upstream'));

        if (version_compare(UPSTREAM_VERSION, "1.12.5", "<")) {
            printf(
                $updateNoticeTitleHtml .
                _x('Starting from <strong>%s</strong> <code>%s</code> capability was removed from <code>%s</code> users role.', '1st %s: plugin version, 2nd %s: capability name, 3rd: UpStream User role', 'upstream'),
                'v1.12.5',
                'edit_others_projects',
                __('UpStream User', 'upstream')
            );
        }
    }

    /**
     * Make sure Recent Comments section on admin Dashboard display only comments
     * current user is allowed to see from projects he's allowed to access.
     *
     * @since   1.13.0
     * @static
     *
     * @global  $pagenow, $wpdb
     *
     * @param   array               $queryArgs  Query clauses.
     * @param   WP_Comment_Query    $query      Current query instance.
     *
     * @return  array   $queryArgs
     */
    public static function filterCommentsOnDashboard($queryArgs, $query)
    {
        global $pagenow;

        if (is_admin()
            && $pagenow === "index.php"
            && !isUserEitherManagerOrAdmin()
        ) {
            global $wpdb;

            $queryArgs['join'] = 'LEFT JOIN ' . $wpdb->prefix . 'posts AS post ON post.ID = ' . $wpdb->prefix . 'comments.comment_post_ID';

            $user = wp_get_current_user();
            if (in_array('upstream_user', $user->roles) || in_array('upstream_client_user', $user->roles)) {
                $projects = (array)upstream_get_users_projects($user);
                if (count($projects) === 0) {
                    $queryArgs['where'] = "(post.ID = -1)";
                } else {
                    $queryArgs['where'] = "(post.post_type = 'project' AND post.ID IN (" . implode(', ', array_keys($projects)) . "))";

                    $userCanModerateComments = user_can($user, 'moderate_comments');
                    if (!$userCanModerateComments) {
                        $queryArgs['where'] .= " AND ( comment_approved = '1' )";
                    } else {
                        $queryArgs['where'] .= " AND ( comment_approved = '1' OR comment_approved = '0' )";
                    }
                }
            } else {
                $queryArgs['where'] .= " AND (post.post_type != 'project')";
            }
        }

        return $queryArgs;
    }
}
endif;


/**
 * Main instance of UpStream.
 *
 * Returns the main instance of UpStream to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return UpStream
 */
function UpStream()
{
    return UpStream::instance();
}

UpStream();

/*
// @todo
$generateRandomString = function($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
};

$chance = function($successRate)
{
    return (float)$successRate >= mt_rand(1, 100);
};

$slugify = function($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\pL\d]+~u', '-', $text);

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  // trim
  $text = trim($text, '-');

  // remove duplicate -
  $text = preg_replace('~-+~', '-', $text);

  // lowercase
  $text = strtolower($text);

  if (empty($text)) {
    return 'n-a';
  }

  return $text;
};

$tasksStatuses = array(
    'In Progress',
    'Overdue',
    'Completed',
    'Closed'
);

$bugsStatuses = array(
    'In Progress',
    'Overdue',
    'Completed',
    'Closed'
);

$bugsSeverities = array(
    'Critical',
    'Standard',
    'Minor'
);

$generateMilestones = function($qty, $project_id) use ($generateRandomString, $chance) {
    $currentTimestamp = time();
    $milestones = (array)get_option('upstream_milestones')['milestones'];
    $milestonesCount = count($milestones);

    $rowset = (array)get_post_meta($project_id, '_upstream_project_milestones', true);

    for ($i = 0; $i < $qty; $i++) {
        $row = array(
            'id'           => strtolower($generateRandomString(14)),
            'created_by'   => mt_rand(1, 7),
            'created_time' => $currentTimestamp,
            'milestone'    => $milestones[mt_rand(0, $milestonesCount -1)]['title'],
            'assigned_to'  => array(),
            'start_date'   => '',
            'end_date'     => '',
            'notes'        => '',
            'comments'     => '',
            'progress'     => '',
            'task_count'   => 0,
            'task_open'    => 0
        );

        if ($chance(75)) {
            $assigneesCount = $chance(75) ? 1 : mt_rand(2, 6);
            for ($assigneeIndex = 0; $assigneeIndex < $assigneesCount; $assigneeIndex++) {
                $row['assigned_to'][] = mt_rand(1, 7);
            }

            $row['assigned_to'] = array_unique($row['assigned_to']);

            unset($assigneeIndex, $assigneesCount);
        }

        if ($chance(75)) {
            $row['start_date'] = mt_rand(1519934411, 1527796811);
        }

        if ($chance(75)) {
            if ((int)$row['start_date'] > 0) {
                $row['end_date'] = mt_rand($row['start_date'], 1527796811);
            } else {
                $row['end_date'] = mt_rand(1519934411, 1527796811);
            }
        }

        $rowset[] = $row;
    }

    update_post_meta($project_id, '_upstream_project_milestones', $rowset);
};

$generateTasks = function($qty, $project_id) use ($tasksStatuses, $generateRandomString, $chance) {
    $currentTimestamp = time();

    $tasks = (array)get_post_meta($project_id, '_upstream_project_tasks', true);
    $milestones = (array)get_post_meta($project_id, '_upstream_project_milestones', true);

    for ($i = 0; $i < $qty; $i++) {
        $row = array(
            'id'           => strtolower($generateRandomString(14)),
            'created_by'   => mt_rand(1, 7),
            'created_time' => $currentTimestamp,
            'title'        => 'Task '. $generateRandomString(7),
            'assigned_to'  => array(),
            'status'       => '',
            'progress'     => '',
            'start_date'   => '',
            'end_date'     => '',
            'notes'        => '',
            'milestone'    => '',
            'comments'     => ''
        );

        if ($chance(75)) {
            $assigneesCount = $chance(75) ? 1 : mt_rand(2, 6);
            for ($assigneeIndex = 0; $assigneeIndex < $assigneesCount; $assigneeIndex++) {
                $row['assigned_to'][] = mt_rand(1, 7);
            }

            $row['assigned_to'] = array_unique($row['assigned_to']);

            unset($assigneeIndex, $assigneesCount);
        }

        if ($chance(75)) {
            $row['status'] = $tasksStatuses[mt_rand(0, 3)];
        }

        if ($chance(75)) {
            $row['progress'] = mt_rand(1, 100);
        }

        if ($chance(75)) {
            $row['start_date'] = mt_rand(1519934411, 1527796811);
        }

        if ($chance(75)) {
            if ((int)$row['start_date'] > 0) {
                $row['end_date'] = mt_rand($row['start_date'], 1527796811);
            } else {
                $row['end_date'] = mt_rand(1519934411, 1527796811);
            }
        }

        if ($chance(75)) {
            $row['milestone'] = $milestones[mt_rand(0, 3)]['id'];
        }

        $tasks[] = $row;
    }

    update_post_meta($project_id, '_upstream_project_tasks', $tasks);
};

$generateBugs = function($qty, $project_id) use ($bugsStatuses, $bugsSeverities, $generateRandomString, $chance) {
    $currentTimestamp = time();

    $rowset = (array)get_post_meta($project_id, '_upstream_project_bugs', true);

    for ($i = 0; $i < $qty; $i++) {
        $row = array(
            'id'           => strtolower($generateRandomString(14)),
            'created_by'   => mt_rand(1, 7),
            'created_time' => $currentTimestamp,
            'title'        => 'Bug '. $generateRandomString(7),
            'assigned_to'  => array(),
            'description'  => '',
            'status'       => '',
            'severity'     => '',
            'file_id'      => '',
            'file'         => '',
            'due_date'     => '',
            'comments'     => '',
            'what-screen-resolution-shows-the-bug' => '',
            'which-browser-shows-the-bug' => ''
        );

        if ($chance(75)) {
            $assigneesCount = $chance(75) ? 1 : mt_rand(2, 6);
            for ($assigneeIndex = 0; $assigneeIndex < $assigneesCount; $assigneeIndex++) {
                $row['assigned_to'][] = mt_rand(1, 7);
            }

            $row['assigned_to'] = array_unique($row['assigned_to']);

            unset($assigneeIndex, $assigneesCount);
        }

        if ($chance(75)) {
            $row['status'] = $bugsStatuses[mt_rand(0, 3)];
        }

        if ($chance(75)) {
            $row['severity'] = $bugsSeverities[mt_rand(0, 2)];
        }

        if ($chance(75)) {
            $row['due_date'] = mt_rand(1519934411, 1527796811);
        }

        $rowset[] = $row;
    }

    update_post_meta($project_id, '_upstream_project_bugs', $rowset);
};

$generateProjects = function($qty) use ($generateRandomString, $chance, $slugify) {
    $currentTimestamp = time();

    for ($i = 0; $i < $qty; $i++) {
        $row = array(
            'post_author' => mt_rand(1, 7),
            'post_title'  => 'Project '. $generateRandomString(7),
            'post_status' => 'publish',
            'post_type'   => 'project',
        );

        $row['post_name'] = $slugify($row['post_title']);

        @wp_insert_post($row, true);
    }
};

$project_id = 92;
$generateMilestones(mt_rand(100, 200), $project_id);
$generateTasks(mt_rand(500, 1000), $project_id);
$generateBugs(mt_rand(1000, 2000), $project_id);

die();
*/
