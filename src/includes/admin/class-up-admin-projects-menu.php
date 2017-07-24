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

            if ($pagenow === 'edit.php' && $_GET['post_type'] === 'project' && self::$userIsUpStreamUser) {
                echo '<style type="text/css">.page-title-action { display: none; }</style>';
            }
        }
    }

    /**
     * Add menu item.
     */
    public function projects_menu() {
        add_submenu_page( 'edit.php?post_type=project', upstream_client_label_plural(),upstream_client_label_plural(), 'edit_clients', 'edit.php?post_type=client' );
        add_submenu_page( 'edit.php?post_type=project', sprintf( __( 'New %s', 'upstream' ), upstream_client_label() ),sprintf( __( 'New %s', 'upstream' ), upstream_client_label() ), 'edit_clients', 'post-new.php?post_type=client' );
    }


    public function submenu_order($menu)
    {
        global $submenu;

        $subMenuIdentifier = 'edit.php?post_type=project';
        if (isset($submenu[$subMenuIdentifier]) && $submenu[$subMenuIdentifier]) {
            $arr = array();
            $arr[] = $submenu[$subMenuIdentifier][5];
            $arr[] = isset($submenu[$subMenuIdentifier][18]) ? $submenu[$subMenuIdentifier][18] : null;
            $arr[] = isset($submenu[$subMenuIdentifier][19]) ? $submenu[$subMenuIdentifier][19] : null;


            if (!self::$userIsUpStreamUser) {
                $arr[] = $submenu[$subMenuIdentifier][10];
                $arr[] = $submenu[$subMenuIdentifier][15];
                $arr[] = $submenu[$subMenuIdentifier][16];
                $arr[] = $submenu[$subMenuIdentifier][17];
            }

            $submenu[$subMenuIdentifier] = $arr;
        }

        return $menu;
    }

}

endif;

return new UpStream_Admin_Projects_Menu();
