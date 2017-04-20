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
        add_action( 'upstream_head', array( $this, 'custom_styles' ) );
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
     * styles
     * @since  1.0.0
     */
    public function custom_styles() {

        // logo
        $logo_css = null;
        if( isset( $this->opt['logo'] ) && $this->opt['logo'] != '' ) {
            $logo   = $this->opt['logo'];
            $logo_w = isset( $this->opt['logo_width'] ) && $this->opt['logo_width'] != '' ? $this->opt['logo_width'] : '230px';
            $logo_h = isset( $this->opt['logo_height'] ) && $this->opt['logo_height'] != '' ? $this->opt['logo_height'] : '90px';

            $logo_css = ".site_title {";
            $logo_css .= "background-image: url( $logo );";
            $logo_css .= "background-size: $logo_w $logo_h;";
            $logo_css .= "width: $logo_w;";
            $logo_css .= "height: $logo_h;";
            $logo_css .= "}";
            $logo_css .= ".site_title span { display: none; }";
        }

        ?>

        <style>
            <?php echo esc_html( $logo_css ); ?>

            /* text color */
            body, table.dataTable.dtr-inline.collapsed > tbody > tr > td:first-child::before, table.dataTable.dtr-inline.collapsed > tbody > tr > th:first-child::before, a:focus, a:hover, .nav.child_menu li li a:hover, .nav.side-menu > li > a:hover, .pagination > .active > a, .pagination > .active > a:focus, .pagination > .active > a:hover, .pagination > .active > span, .pagination > .active > span:focus, .pagination > .active > span:hover, .profile_info p, a.btn {
                color: <?php echo $this->css( 'text_color' ); ?>;
            }

            /* heading color */
            table thead, .x_title h2, .details-panel p.title, .fn-label, .menu_section h3, .navbar-brand, .pagination > li > a, .pagination > li > span, .panel_toolbox > li > a:hover, .text-info {
              color: <?php echo $this->css( 'heading_color' ); ?>;
            }

            /* lines & borders color */
            .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th, .table-bordered, div.dataTables_wrapper div.dataTables_filter input, .form-control, .btn-default, .pagination > .disabled > a, .pagination > .disabled > a:focus, .pagination > .disabled > a:hover, .pagination > .disabled > span, .pagination > .disabled > span:focus, .pagination > .disabled > span:hover, .x_title {
                border-color: <?php echo $this->css( 'lines_borders_color' ); ?>;
            }

            /* highlight color  */
            .nav.side-menu > li.active, .nav.side-menu > li.current-page {
                border-color: <?php echo $this->css( 'highlight_color' ); ?>;
            }
            .nav li li.current-page a, .nav.child_menu li li a.active, .x_title h2 .fa, .main_menu .fa, .nav.toggle a  {
                color: <?php echo $this->css( 'highlight_color' ); ?>;
            }
            .tile-stats .count > span  {
                background-color: <?php echo $this->css( 'highlight_color' ); ?>;
            }

            /* sidebar background */
            body, .left_col, .nav_title, .sidebar-footer a:hover {
                background-color: <?php echo $this->css( 'sidebar_background' ); ?>;
            }

            /* page background */
            body .container.body .right_col {
                background-color: <?php echo $this->css( 'page_background' ); ?>;
            }
            /* sidebar link */
            .nav.side-menu > li > a, .nav.child_menu > li > a {
                color: <?php echo $this->css( 'sidebar_link_color' ); ?>;
            }
            .nav.side-menu > li > a:hover, .nav.child_menu > li > a:hover {
                color: <?php echo $this->css( 'sidebar_link_hover_color' ); ?>;
            }
            /* sidebar bottom icons `*/
            .sidebar-footer a {
                color: <?php echo $this->css( 'sidebar_bottom_icons_color' ); ?>;
            }
            /* panel colors `*/
            .x_panel {
                background-color: <?php echo $this->css( 'panel_background' ); ?>;
            }
            .x_title h2 {
                color: <?php echo $this->css( 'panel_heading_color' ); ?>;
            }

            <?php echo isset( $this->opt['custom_css'] ) && $this->opt['custom_css'] != '' ? esc_html( $this->opt['custom_css'] ) : ''; ?>

        </style>

        <?php

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
