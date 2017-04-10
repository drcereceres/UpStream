<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Options_Style' ) ) :

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Options_Style {
	
	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	public $id = 'upstream_style';

	/**
	 * Page title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Menu Title
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * Menu Title
	 * @var string
	 */
	protected $description = '';

	/**
	 * Holds an instance of the object
	 *
	 * @var Myprefix_Admin
	 **/
	public static $instance = null;

	/**
	 * Constructor
	 * @since 0.1.0
	 */
	public function __construct() {
		// Set our title
		$this->title = __( 'Style Settings', 'upstream' );
		$this->menu_title = __( 'Style Settings', 'upstream' );
		$this->description = __( '', 'upstream' );
	}
	/**
	 * Returns the running object
	 *
	 * @return Myprefix_Admin
	 **/
	public static function get_instance() {
		if( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	


	/**
	 * Add the options metabox to the array of metaboxes
	 * @since  0.1.0
	 */
	public function options() {

		$project_url = '<a target="_blank" href="' . home_url( 'projects' ) . '">' . home_url( 'projects' ) . '</a>';

		$options = apply_filters( $this->id . '_option_fields', array(
			'id'         => $this->id, // upstream_tasks
			'title'      => $this->title,
			'menu_title' => $this->menu_title,
			'desc'       => $this->description,
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->id ), ),
			'show_names' => true,
			'fields'     => array(

				array(
				    'name' => __( 'Logo', 'upstream-customizer' ),
				    'id'   => 'logo',
				    'type' => 'file',
				    'desc' => __( '', 'upstream-customizer' ),
				), 
				array(
				    'name' => __( 'Logo Width', 'upstream-customizer' ),
				    'id'   => 'logo_width',
				    'type' => 'text',
				    'desc' => __( 'Logo width in pixels, up to a maximum of 230px. Include px on the end of number.', 'upstream-customizer' ),
				    'attributes' => array( 
				    	'placeholder' => '230px',
				    )
				), 
				array(
				    'name' => __( 'Logo Height', 'upstream-customizer' ),
				    'id'   => 'logo_height',
				    'type' => 'text',
				    'desc' => __( 'Logo height in pixels. Include px on the end of number.', 'upstream-customizer' ),
				    'attributes' => array( 
				    	'placeholder' => '50px',
				    )
				), 

				array(
				    'name' => __( 'Footer Text', 'upstream-customizer' ),
				    'id'   => 'footer_text',
				    'type' => 'text',
				    'desc' => __( '', 'upstream-customizer' ),
				), 

				array(
				    'name' => __( 'Heading Color', 'upstream-customizer' ),
				    'id'   => 'heading_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Text Color', 'upstream-customizer' ),
				    'id'   => 'text_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				), 
				array(
				    'name' => __( 'Lines & Borders Color', 'upstream-customizer' ),
				    'id'   => 'lines_borders_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				), 
				array(
				    'name' => __( 'Highlight Color', 'upstream-customizer' ),
				    'id'   => 'highlight_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),


				array(
				    'name' => __( 'Page Background', 'upstream-customizer' ),
				    'id'   => 'page_background',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Panel Backgrounds', 'upstream-customizer' ),
				    'id'   => 'panel_background',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Panel Heading Color', 'upstream-customizer' ),
				    'id'   => 'panel_heading_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),


				array(
				    'name' => __( 'Sidebar Background', 'upstream-customizer' ),
				    'id'   => 'sidebar_background',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Sidebar Link Color', 'upstream-customizer' ),
				    'id'   => 'sidebar_link_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Sidebar Link Hover Color', 'upstream-customizer' ),
				    'id'   => 'sidebar_link_hover_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),
				array(
				    'name' => __( 'Sidebar Bottom Icons Color', 'upstream-customizer' ),
				    'id'   => 'sidebar_bottom_icons_color',
				    'type' => 'colorpicker',
				    'desc' => __( '', 'upstream-customizer' ),
				),

				array(
				    'name' => __( 'Custom CSS', 'upstream-customizer' ),
				    'id'   => 'custom_css',
				    'type' => 'textarea',
				    'desc' => __( '', 'upstream-customizer' ),
				),

			) )
		);

		return $options;

	}

}


endif;