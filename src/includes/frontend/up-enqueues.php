<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



/**
 * Removing / Dequeueing All Stylesheets And Scripts
 *
 * @return void
 */
function upstream_enqueue_styles_scripts(){

    global $wp_styles, $wp_scripts;

    if( get_post_type() != 'project' )
        return;

    // Dequeueing styles
    if( is_array( $wp_styles->queue ) ){
        foreach ( $wp_styles->queue as $style ) {
            wp_dequeue_style( $style );
        }
    }
    // Dequeueing scripts
    $scripts_to_keep = array( 'jquery' );
    if( is_array( $wp_scripts->queue) ){
        foreach ( $wp_scripts->queue as $script ) {
            if ( in_array( $script, $scripts_to_keep ) ) continue;
            wp_dequeue_script( $script );
        }
    }

    $up_url = UPSTREAM_PLUGIN_URL;
    $up_ver = UPSTREAM_VERSION;

    /*
     * Enqueue styles
     */
    $css_dir    = 'templates/assets/css/';

    $dir        = upstream_template_path();
    $maintheme  = trailingslashit( get_template_directory() ) . $dir . 'assets/css/';
    $childtheme = trailingslashit( get_stylesheet_directory() ) . $dir . 'assets/css/';

    wp_enqueue_style( 'up-bootstrap', $up_url . $css_dir . 'bootstrap.min.css', array(), $up_ver, 'all' );
    wp_enqueue_style( 'up-fontawesome', $up_url . $css_dir . 'fontawesome.min.css', array(), $up_ver, 'all' );
    wp_enqueue_style( 'framework', $up_url . $css_dir . 'framework.css', array(), $up_ver, 'all' );
    wp_enqueue_style( 'upstream', $up_url . $css_dir . 'upstream.css', array(), $up_ver, 'all' );

    if( isset( $GLOBALS['login_template'] ) )
        wp_enqueue_style( 'up-login', $up_url . $css_dir . 'login.css', array(), $up_ver, 'all' );

    if ( file_exists( $childtheme ) ) {
        $custom = trailingslashit( get_stylesheet_directory_uri() ) . $dir . 'assets/css/upstream-custom.css';
        wp_enqueue_style( 'child-custom', $custom, array(), $up_ver, 'all' );
    }
    if ( file_exists( $maintheme ) ) {
        $custom = trailingslashit( get_template_directory_uri() ) . $dir . 'assets/css/upstream-custom.css';
        wp_enqueue_style( 'theme-custom', $custom, array(), $up_ver, 'all' );
    }

    /*
     * Enqueue scripts
     */
    $js_dir = 'templates/assets/js/';

    wp_enqueue_script( 'up-bootstrap', $up_url . $js_dir . 'bootstrap.min.js', array( 'jquery' ), $up_ver, true );

    wp_enqueue_script( 'up-fastclick', $up_url . $js_dir . 'fastclick.js', array( 'jquery' ), $up_ver, true );
    wp_enqueue_script( 'up-nprogress', $up_url . $js_dir . 'nprogress.js', array( 'jquery' ), $up_ver, true );
    wp_enqueue_script( 'up-progressbar', $up_url . $js_dir . 'progressbar.min.js', array( 'jquery' ), $up_ver, true );

    wp_enqueue_script( 'up-datatables', $up_url . $js_dir . 'datatables/dataTables.min.js', array( 'jquery' ), $up_ver, true );
    wp_enqueue_script( 'dt-bootstrap', $up_url . $js_dir . 'datatables/dataTables.bootstrap.min.js', array( 'jquery' ), $up_ver, true );
    wp_enqueue_script( 'dt-buttons', $up_url . $js_dir . 'datatables/dataTables.buttons.min.js', array( 'jquery' ), $up_ver, true );
    wp_enqueue_script( 'dt-responsive', $up_url . $js_dir . 'datatables/dataTables.responsive.min.js', array( 'jquery' ), $up_ver, true );

    wp_enqueue_script( 'upstream', $up_url . $js_dir . 'upstream.js', array( 'jquery' ), $up_ver, true );
    wp_localize_script( 'upstream', 'upstream', apply_filters( 'upstream_localized_javascript', array(
        'ajaxurl'           => admin_url( 'admin-ajax.php'),
        'upload_url'        => admin_url('async-upload.php'),
        'security'          => wp_create_nonce( 'upstream-nonce' ),
        'js_date_format'    => upstream_php_to_js_dateformat(),
    )));

}
add_action( 'wp_enqueue_scripts', 'upstream_enqueue_styles_scripts', 1000 ); // Hook this late enough so all stylesheets / scripts has been added (to be further dequeued by this action)
