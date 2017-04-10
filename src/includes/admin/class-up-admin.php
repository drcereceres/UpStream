<?php
/**
 * UpStream Admin
 *
 * @class    UpStream_Admin
 * @author   UpStream
 * @category Admin
 * @package  UpStream/Admin
 * @version  1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * UpStream_Admin class.
 */
class UpStream_Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'includes' ) );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		add_filter( 'ajax_query_attachments_args', array( $this, 'filter_user_attachments' ), 10, 1 );
	}

	/**
	 * Include any classes we need within admin.
	 */
	public function includes() {

		// option pages
		include_once( 'class-up-admin-options.php' );
		include_once( 'options/option-functions.php' );

		// metaboxes
		include_once( 'class-up-admin-metaboxes.php' );
		include_once( 'metaboxes/metabox-functions.php' );
		
		include_once( 'up-enqueues.php' );
		include_once( 'class-up-admin-projects-menu.php' );
		include_once( 'class-up-admin-project-columns.php' );
		include_once( 'class-up-admin-client-columns.php' );
		include_once( 'class-up-admin-pointers.php' );
	}


	/**
	 * Adds one or more classes to the body tag in the dashboard.
	 *
	 * @param  String $classes Current body classes.
	 * @return String          Altered body classes.
	 */
	public function admin_body_class( $classes ) {
		
		$screen = get_current_screen();

		if ( in_array( $screen->id, array( 'client', 'edit-client', 'project', 'edit-project', 'edit-project_category', 'project_page_tasks', 'project_page_bugs', 'toplevel_page_upstream_general', 'upstream_page_upstream_bugs', 'upstream_page_upstream_tasks', 'upstream_page_upstream_milestones', 'upstream_page_upstream_clients', 'upstream_page_upstream_projects' ) ) ) {

			return "$classes upstream";

		}

	}

	
	/**
	 * Only show current users media items
	 *
	 */
	public function filter_user_attachments( $query = array() ) {
	    $user_id = get_current_user_id();
	    if( $user_id ) {
	        $query['author'] = $user_id;
	    }
	    return $query;
	}
}

return new UpStream_Admin();