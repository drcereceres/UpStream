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

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'projects_menu' ), 9 );
        add_filter( 'custom_menu_order', array( $this, 'submenu_order' ) );

    }

    /**
     * Add menu item.
     */
    public function projects_menu() {
        add_submenu_page( 'edit.php?post_type=project', upstream_client_label_plural(),upstream_client_label_plural(), 'edit_clients', 'edit.php?post_type=client' );
        add_submenu_page( 'edit.php?post_type=project', sprintf( __( 'New %s', 'upstream' ), upstream_client_label() ),sprintf( __( 'New %s', 'upstream' ), upstream_client_label() ), 'edit_clients', 'post-new.php?post_type=client' );
    }


    public function submenu_order( $menu_ord ) {

        global $submenu;
            if( $submenu['edit.php?post_type=project'] ) {
            $arr = array();
            $arr[] = $submenu['edit.php?post_type=project'][5];
            $arr[] = $submenu['edit.php?post_type=project'][18];
            $arr[] = isset( $submenu['edit.php?post_type=project'][19] ) ? $submenu['edit.php?post_type=project'][19] : null;
            $arr[] = $submenu['edit.php?post_type=project'][10];
            $arr[] = $submenu['edit.php?post_type=project'][15];
            $arr[] = $submenu['edit.php?post_type=project'][16];
            $arr[] = $submenu['edit.php?post_type=project'][17];
            $submenu['edit.php?post_type=project'] = $arr;
        }
        return $menu_ord;
    }

}

endif;

return new UpStream_Admin_Projects_Menu();
