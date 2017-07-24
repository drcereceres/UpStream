<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Enqueues the required admin scripts.
 *
 */
function upstream_load_admin_scripts( $hook ) {


    /*
     * Javascript
     */
    $js_dir     = UPSTREAM_PLUGIN_URL . 'includes/admin/assets/js/';
    $admin_deps = array( 'jquery', 'cmb2-scripts' );
    $post_type  = get_post_type();

    if( $post_type == 'project' ) {
        wp_register_script( 'upstream-project', $js_dir . 'edit-project.js', $admin_deps, UPSTREAM_VERSION, false );
        wp_enqueue_script( 'upstream-project' );
        wp_localize_script( 'upstream-project', 'upstream_project', apply_filters( 'upstream_project_script_vars', array(
            'version'   => UPSTREAM_VERSION,
            'user'      => upstream_current_user_id(),
        ) ) );
    }

    if ($post_type === 'client') {
        // @todo : Remove the mt_rand function.
        wp_enqueue_script('up-metabox-client', $js_dir . 'metabox-client.js', array('jquery'), UPSTREAM_VERSION . mt_rand(1, 999999), true);
        wp_localize_script('up-metabox-client', 'upstreamMetaboxClientLangStrings', array(
            'ERR_JQUERY_NOT_FOUND'    => __('UpStream requires jQuery.', 'upstream'),
            'MSG_NO_ASSIGNED_USERS'   => __("There's no users assigned yet.", 'upstream'),
            'MSG_NO_USER_SELECTED'    => __('Please, select at least one user', 'upstream'),
            'MSG_ADD_ONE_USER'        => __('Add 1 User', 'upstream'),
            'MSG_ADD_MULTIPLE_USERS'  => __('Add %d Users', 'upstream'),
            'MSG_NO_USERS_FOUND'      => __('No users found.', 'upstream'),
            'LB_ADDING_USERS'         => __('Adding...', 'upstream'),
            'MSG_ARE_YOU_SURE'        => __('Are you sure? This action cannot be undone.', 'upstream'),
            'MSG_FETCHING_DATA'       => __('Fetching data...', 'upstream'),
            'MSG_NO_DATA_FOUND'       => __('No data found.', 'upstream')
        ));
    }

    /*
     * CSS
     */
    $css_dir = UPSTREAM_PLUGIN_URL . 'includes/admin/assets/css/';

    wp_register_style( 'upstream-admin', $css_dir . 'upstream.css', UPSTREAM_VERSION);
    wp_enqueue_style( 'upstream-admin' );


}
add_action( 'admin_enqueue_scripts', 'upstream_load_admin_scripts', 100 );
