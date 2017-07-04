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

    if( $post_type == 'client' ) {
        // @todo: fix version
        wp_enqueue_script( 'up-metabox-client', $js_dir . 'metabox-client.js', $admin_deps, UPSTREAM_VERSION . mt_rand(1, 99999), false );
        // wp_enqueue_script( 'upstream-client' );
    }

    /*
     * CSS
     */
    $css_dir = UPSTREAM_PLUGIN_URL . 'includes/admin/assets/css/';

    wp_register_style( 'upstream-admin', $css_dir . 'upstream.css', UPSTREAM_VERSION);
    wp_enqueue_style( 'upstream-admin' );


}
add_action( 'admin_enqueue_scripts', 'upstream_load_admin_scripts', 100 );
