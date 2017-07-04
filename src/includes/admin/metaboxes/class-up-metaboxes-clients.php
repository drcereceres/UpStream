<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Metaboxes_Clients' ) ) :


/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Metaboxes_Clients {

    /**
     * Post type
     * @var string
     */
    public $type = 'client';

    /**
     * Post type
     * @var string
     */
    public $label = '';

    /**
     * Metabox prefix
     * @var string
     */
    public $prefix = '_upstream_client_';

    /**
     * Holds an instance of the object
     *
     * @var Myprefix_Admin
     **/
    public static $instance = null;

    public function __construct() {
        $this->label = upstream_client_label();
        $this->label_plural = upstream_client_label_plural();
    }

    /**
     * Returns the running object
     *
     * @return Myprefix_Admin
     **/
    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
            //self::$instance->sidebar_top();
        }
        return self::$instance;
    }
}

endif;
