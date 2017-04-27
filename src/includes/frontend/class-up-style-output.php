<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Run the extension after UpStream is loaded.
 */
add_action( 'upstream_loaded', 'upstream_run_styles' );
function upstream_run_styles() {
    return UpStream_Style_Output::instance();

}

/**
 * Main UpStream Style Output Class.
 *
 * @since 1.0.0
 */
class UpStream_Style_Output {

    private $opt = '';

    /**
     * @var UpStream The one true UpStream Style Output
     * @since 1.0.0
     */
    protected static $_instance = null;


    /**
     * @since 1.0.0
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        $this->init_hooks();
        $this->opt = get_option( 'upstream_style' );
    }

    /**
     * Hook into actions and filters.
     * @since  1.0.0
     */
    private function init_hooks() {
        add_action( 'upstream_footer_text', array( $this, 'footer_text' ) );
    }

    /**
     * Enqueues
     * @since  1.0.0
     */
    public function footer_text( $text ) {
        if( isset( $this->opt['footer_text'] ) && ! empty( $this->opt['footer_text'] ) ) {
            $text = $this->opt['footer_text'];
        }
        return $text;
    }

    /**
     * Check if we have this CSS
     * @since  1.0.0
     */
    private function css( $item ) {
        $css = isset( $this->opt[ $item ] ) && $this->opt[ $item ] != '' ? $this->opt[ $item ] : '';
        return esc_html( $css );
    }


}
