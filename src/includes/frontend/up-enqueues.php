<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Removing / Dequeueing All Stylesheets And Scripts
 *
 * @return void
 */
function upstream_enqueue_styles_scripts() {

    global $wp_styles, $wp_scripts;

    if ( get_post_type() != 'project' ) {
        return;
    }

    // Dequeueing styles
    if ( is_array( $wp_styles->queue ) ) {
        foreach ( $wp_styles->queue as $style ) {
            wp_dequeue_style( $style );
        }
    }
    // Dequeueing scripts
    $scripts_to_keep = [ 'jquery' ];
    if ( is_array( $wp_scripts->queue ) ) {
        foreach ( $wp_scripts->queue as $script ) {
            if ( in_array( $script, $scripts_to_keep ) ) {
                continue;
            }
            wp_dequeue_script( $script );
        }
    }

    $up_url  = UPSTREAM_PLUGIN_URL;
    $up_ver  = UPSTREAM_VERSION;
    $lib_dir = 'templates/assets/libraries/';

    /*
     * Enqueue styles
     */
    $css_dir = 'templates/assets/css/';

    $dir        = upstream_template_path();
    $maintheme  = trailingslashit( get_template_directory() ) . $dir . 'assets/css/';
    $childtheme = trailingslashit( get_stylesheet_directory() ) . $dir . 'assets/css/';

    wp_enqueue_style( 'up-bootstrap', $up_url . $css_dir . 'bootstrap.min.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'up-tableexport', $up_url . $css_dir . 'vendor/tableexport.min.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'up-select2', $up_url . $css_dir . 'vendor/select2.min.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'up-chosen', $up_url . $lib_dir . 'chosen/chosen.min.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'up-fontawesome', $up_url . $css_dir . 'fontawesome.min.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'framework', $up_url . $css_dir . 'framework.css', [], $up_ver, 'all' );
    wp_enqueue_style( 'upstream-datepicker', $up_url . $css_dir . 'vendor/bootstrap-datepicker3.min.css', [], $up_ver,
        'all' );
    wp_enqueue_style( 'upstream', $up_url . $css_dir . 'upstream.css', [ 'admin-bar' ], $up_ver, 'all' );

    if ( isset( $GLOBALS['login_template'] ) ) {
        wp_enqueue_style( 'up-login', $up_url . $css_dir . 'login.css', [], $up_ver, 'all' );
    }

    if ( file_exists( $childtheme ) ) {
        $custom = trailingslashit( get_stylesheet_directory_uri() ) . $dir . 'assets/css/upstream-custom.css';
        wp_enqueue_style( 'child-custom', $custom, [], $up_ver, 'all' );
    }
    if ( file_exists( $maintheme ) ) {
        $custom = trailingslashit( get_template_directory_uri() ) . $dir . 'assets/css/upstream-custom.css';
        wp_enqueue_style( 'theme-custom', $custom, [], $up_ver, 'all' );
    }

    // Enqueue style for poopy sandbox to complement the admin bar.
    if ( file_exists( ABSPATH . 'wp-content/plugins/' . plugin_dir_path( 'sandbox/sandbox.php' ) . 'static/css/poopy.css' ) ) {
        wp_enqueue_style( 'poopy', plugin_dir_url( 'sandbox/sandbox.php' ) . '/static/css/poopy.css', [], $up_ver );
    }

    /*
     * Enqueue scripts
     */
    $js_dir = 'templates/assets/js/';

    wp_enqueue_script( 'up-filesaver', $up_url . $js_dir . 'vendor/FileSaver.min.js', [], $up_ver, true );
    wp_enqueue_script( 'up-tableexport', $up_url . $js_dir . 'vendor/tableexport.min.js', [], $up_ver, true );
    wp_enqueue_script( 'up-select2', $up_url . $js_dir . 'vendor/select2.full.min.js', [], $up_ver, true );
    wp_enqueue_script( 'up-chosen', $up_url . $lib_dir . '/chosen/chosen.jquery.min.js', [ 'jquery' ], $up_ver, true );
    wp_enqueue_script( 'up-bootstrap', $up_url . $js_dir . 'bootstrap.min.js', [ 'jquery' ], $up_ver, true );
    wp_enqueue_script( 'up-fastclick', $up_url . $js_dir . 'fastclick.js', [ 'jquery' ], $up_ver, true );
    wp_enqueue_script( 'up-nprogress', $up_url . $js_dir . 'nprogress.js', [ 'jquery' ], $up_ver, true );

    wp_enqueue_script( 'upstream-datepicker', $up_url . $js_dir . 'vendor/bootstrap-datepicker.min.js',
        [ 'jquery', 'up-bootstrap' ], $up_ver, true );
    wp_enqueue_script( 'up-modal', $up_url . $js_dir . 'vendor/modal.min.js', [ 'jquery' ], $up_ver, true );

    wp_enqueue_script( 'upstream', $up_url . $js_dir . 'upstream.js', [ 'jquery', 'up-modal', 'admin-bar' ], $up_ver, true );


    $noDataStringTemplate = _x( "You haven't created any %s yet",
        '%s: item name, ie Milestones, Tasks, Bugs, Files, Discussion', 'upstream' );

    wp_localize_script( 'upstream', 'upstream', apply_filters( 'upstream_localized_javascript', [
        'ajaxurl'              => admin_url( 'admin-ajax.php' ),
        'upload_url'           => admin_url( 'async-upload.php' ),
        'security'             => wp_create_nonce( 'upstream-nonce' ),
        'js_date_format'       => upstream_php_to_js_dateformat(),
        'datepickerDateFormat' => upstreamGetDateFormatForJsDatepicker(),
        'langs'                => [
            'LB_COPY'                 => __( 'Copy', 'upstream' ),
            'LB_CSV'                  => __( 'CSV', 'upstream' ),
            'LB_SEARCH'               => __( 'Search:', 'upstream' ),
            'MSG_TABLE_NO_DATA_FOUND' => _x( "You haven't created any %s yet",
                '%s: item name, ie Milestones, Tasks, Bugs, Files, Discussion', 'upstream' ),
            'MSG_NO_MILESTONES_YET'   => sprintf( $noDataStringTemplate, upstream_milestone_label_plural() ),
            'MSG_NO_TASKS_YET'        => sprintf( $noDataStringTemplate, upstream_task_label_plural() ),
            'MSG_NO_BUGS_YET'         => sprintf( $noDataStringTemplate, upstream_bug_label_plural() ),
            'MSG_NO_FILES_YET'        => sprintf( $noDataStringTemplate, upstream_file_label_plural() ),
            'MSG_NO_DISCUSSION_YET'   => sprintf( $noDataStringTemplate, upstream_discussion_label() ),
        ],
    ] ) );
}

add_action( 'wp_enqueue_scripts', 'upstream_enqueue_styles_scripts',
    1000 ); // Hook this late enough so all stylesheets / scripts has been added (to be further dequeued by this action)

// Removes the "next"/"prev" <link rel /> tags. This prevents links to another projects appearing on the HTML code.
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

function upstream_deregister_assets() {
    $isAdmin  = is_admin();
    $postType = get_post_type();

    if ( $isAdmin
         || $postType !== 'project'
    ) {
        return;
    }

    wp_dequeue_script( 'jquery-ui-datepicker' );
    wp_deregister_script( 'jquery-ui-datepicker' );
}

add_action( 'wp_enqueue_scripts', 'upstream_deregister_assets', 10 );
