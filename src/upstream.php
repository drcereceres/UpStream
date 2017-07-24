<?php
/**
 * Plugin Name: UpStream
 * Description: A WordPress Project Management plugin by UpStream.
 * Author: UpStream
 * Author URI: https://upstreamplugin.com
 * Version: 1.10.4
 * Text Domain: upstream
 * Domain Path: /languages
 */


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream' ) ) :
/*
 * Helper function for quick debugging
 */
if (!function_exists('pp')) {
    function pp( $array )
    {
        echo '<pre style="white-space:pre-wrap;">';
            print_r( $array );
        echo '</pre>';
    }
}

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
        _doing_it_wrong(__FUNCTION__, __('You\'re not supposed to clone this class.', 'upstream'), UPSTREAM_VERSION);
    }

    /**
     * Disable unserializing of the class.
     *
     * @since   1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, __('You\'re not supposed to unserialize this class.', 'upstream'), UPSTREAM_VERSION);
    }

    /**
     * Prevent the class instance being serialized.
     *
     * @since   1.10.2
     */
    public function __sleep()
    {
        _doing_it_wrong(__FUNCTION__, __('You\'re not supposed to serialize this class.', 'upstream'), UPSTREAM_VERSION);
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
        add_filter('quicktags_settings', 'upstream_tinymce_quicktags_settings');
        add_filter('tiny_mce_before_init', 'upstream_tinymce_before_init_setup_toolbar');
        add_filter('tiny_mce_before_init', 'upstream_tinymce_before_init');
        add_filter('teeny_mce_before_init', 'upstream_tinymce_before_init_setup_toolbar');
    }

    /**
     * Prevent a Client User from accessing any page other than the profile.
     *
     * @since   @todo
     *
     * @global  $pagenow
     */
    public function limitClientUsersAdminAccess()
    {
        global $pagenow;

        $profilePage = 'profile.php';

        if ($pagenow !== $profilePage) {
            wp_redirect(admin_url($profilePage));
            exit;
        }
    }

    /**
     * Make sure Client Users can only see the Profile menu item.
     *
     * @since   @todo
     *
     * @global  $menu
     */
    public function limitClientUsersMenu()
    {
        global $menu;

        foreach ($menu as $menuIndex => $menuData) {
            $menuFile = isset($menuData[2]) ? $menuData[2] : null;
            if ($menuFile !== null) {
                if ($menuFile === 'profile.php') {
                    continue;
                }

                remove_menu_page($menuFile);
            }
        }
    }

    /**
     * Hide some toolbar items from Client Users.
     *
     * @since   @todo
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
        $this->define( 'UPSTREAM_VERSION', '1.10.4' );
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

        if ( $this->is_request( 'admin' ) ) {
            include_once( 'includes/libraries/cmb2/init.php' );
            include_once( 'includes/libraries/cmb2-grid/Cmb2GridPlugin.php' );
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
        }

        include_once( 'includes/up-general-functions.php' );
        include_once( 'includes/up-project-functions.php' );
        include_once( 'includes/up-client-functions.php' );
        include_once( 'includes/up-permissions-functions.php' );
        include_once( 'includes/up-client-users-migration.php' );
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
        \UpStream\Migrations\ClientUsers::run();

        $user = wp_get_current_user();
        $userRoles = (array)$user->roles;
        if (count(array_intersect($userRoles, array('administrator', 'upstream_manager'))) === 0 && in_array('upstream_client_user', $userRoles)) {
            add_filter('admin_init', array($this, 'limitClientUsersAdminAccess'));
            add_filter('admin_head', array($this, 'limitClientUsersMenu'));
            add_action('admin_bar_menu', array($this, 'limitClientUsersToolbarItems'), 999);
        }

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
