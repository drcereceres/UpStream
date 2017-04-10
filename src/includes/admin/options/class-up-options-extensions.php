<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Options_Extensions' ) ) :

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Options_Extensions {
	
	/**
	 * Array of metaboxes/fields
	 * @var array
	 */
	public $id = 'upstream_extensions';

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
		$this->title = __( 'Extensions', 'upstream' );
		$this->menu_title = __( 'Extensions', 'upstream' );
		$this->description = __( 'These extensions add extra functionality to the UpStream Project Management plugin.', 'upstream' );
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

		$options = apply_filters( $this->id . '_option_fields', array(
			'id'         => $this->id, // upstream_tasks
			'title'      => $this->title,
			'menu_title' => $this->menu_title,
			'desc'       => $this->description,
			'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->id ), ),
			'show_names' => true,
			'fields'     => array(

				array(
				    'name' 			=> __( 'Project Timeline', 'upstream' ),
				    'id'   			=> 'upstream_project_timeline',
				    'desc' 			=> __( 'Add a Gantt style chart to visualize your projects.', 'upstream' ),
				    'type' 			=> 'title',
				    'attributes' 	=> array(
				    	'class' => 'extension',
				    ),
				    'after' => 'upstream_extension_markup'
				),
				array(
				    'name' 			=> __( 'Frontend Edit', 'upstream' ),
				    'id'   			=> 'upstream_frontend_edit',
				    'desc' 			=> __( 'Allow users to add and edit items on the frontend.', 'upstream' ),
				    'type' 			=> 'title',
				    'attributes' 	=> array(
				    	'class' => 'extension',
				    ),
				    'after' => 'upstream_extension_markup'
				),

			) )
		);
		
		return $options;

	}



}


	/**
	 * Wrapper HTML markup for the extensions.
	 * @param  object $field_args Current field args
 	 * @param  object $field      Current field object
	 * @since  1.0.0
	 */
	function upstream_extension_markup( $field_args, $field ) {

		$home 	= 'http://upstreamplugin.com/';

		$ext 	= str_replace( 'upstream_', '', $field_args['id'] ); // remove prefix from id
		$slug 	= str_replace( '_', '-', $ext ); // get the slug
		$img 	= UPSTREAM_PLUGIN_URL . 'includes/admin/assets/img/banner-' . $slug . '.jpg'; // get the image
		$link 	= $home . 'extensions/' . $slug . '/?utm_source=extensions&utm_campaign=plugin&utm_medium=settings_extensions&utm_content=' . $slug; // get the link to the extension

		?>
		<div class="wrapper">
			<img src="<?php echo esc_url( $img ) ?>" />
			<a class="button button-primary button-large" style="margin-top: 0px" target="_blank" href="<?php echo esc_url( $link ) ?>">View this Extension </a>
		</div>
		<?php
	}


endif;