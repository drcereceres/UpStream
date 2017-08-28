<?php
/**
 * Setup menus in WP admin.
 *
 * @author   UpStream
 * @category Admin
 * @package  UpStream/Admin
 * @version  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Admin_Projects_Menu' ) ) :

/**
 * UpStream_Admin_Menus Class.
 */
class UpStream_Admin_Projects_Menu {
    private static $userIsUpStreamUser = null;

    /**
     * Hook in tabs.
     */
    public function __construct() {
        if (self::$userIsUpStreamUser === null) {
            $user = wp_get_current_user();
            self::$userIsUpStreamUser = count(array_intersect($user->roles, array('administrator', 'upstream_manager'))) === 0;
        }

        add_action( 'admin_menu', array( $this, 'projects_menu' ), 9 );
        add_filter( 'custom_menu_order', array( $this, 'submenu_order' ) );
        add_action('admin_head', array($this, 'hideAddNewProjectButtonIfNeeded'));
    }

    public function hideAddNewProjectButtonIfNeeded()
    {
        if (is_admin()) {
            global $pagenow;

            if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'project' && self::$userIsUpStreamUser) {
                echo '<style type="text/css">.page-title-action { display: none; }</style>';
            }
        }
    }

    /**
     * Add menu item.
     */
    public function projects_menu() {
        add_submenu_page('edit.php?post_type=project', upstream_client_label_plural(), upstream_client_label_plural(), 'edit_clients', 'edit.php?post_type=client');
    }


    public function submenu_order($menu)
    {
        global $submenu;

        $subMenuIdentifier = 'edit.php?post_type=project';
        if (isset($submenu[$subMenuIdentifier]) && !empty($submenu[$subMenuIdentifier])) {
            $ourSubmenu = &$submenu[$subMenuIdentifier];

            // Projects
            $newSubmenu = array($ourSubmenu[5]);
            if (!self::$userIsUpStreamUser) {
                if (is_project_categorization_disabled()) {
                    // Tasks
                    if (isset($ourSubmenu[12])) {
                        $newSubmenu[] = $ourSubmenu[12];
                    }

                    // Bugs
                    if (isset($ourSubmenu[13])) {
                        $newSubmenu[] = $ourSubmenu[13];
                    }

                    // Clients
                    if (!is_clients_disabled() && isset($ourSubmenu[11])) {
                        $newSubmenu[] = $ourSubmenu[11];
                    }
                } else {
                    // Tasks
                    if (isset($ourSubmenu[17])) {
                        $newSubmenu[] = $ourSubmenu[17];
                    }

                    // Bugs
                    if (isset($ourSubmenu[18])) {
                        $newSubmenu[] = $ourSubmenu[18];
                    }

                    // Clients
                    if (!is_clients_disabled() && isset($ourSubmenu[16])) {
                        $newSubmenu[] = $ourSubmenu[16];
                    }

                    // Categories
                    if (isset($ourSubmenu[15])) {
                        $newSubmenu[] = $ourSubmenu[15];
                    }
                }
            }

            $ourSubmenu = $newSubmenu;
        }

        return $menu;
    }

}

endif;

return new UpStream_Admin_Projects_Menu();
