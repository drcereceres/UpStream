<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Admin_Metaboxes' ) ) :

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Admin_Metaboxes {

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        add_action( 'cmb2_admin_init', array( $this, 'register_metaboxes' ) );
    }

    /**
     * Add the options metabox to the array of metaboxes
     * @since  0.1.0
     */
    public function register_metaboxes() {

        /**
         * Load the metaboxes for project post type
         */
        $project_metaboxes = new UpStream_Metaboxes_Projects();
        $project_metaboxes->get_instance();

        /**
         * Load the metaboxes for client post type
         */
        $client_metaboxes = new UpStream_Metaboxes_Clients();
        $client_metaboxes->get_instance();

    }

}

new UpStream_Admin_Metaboxes();

endif;
