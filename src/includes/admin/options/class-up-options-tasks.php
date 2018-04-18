<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Options_Tasks' ) ) :

/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Options_Tasks {

    /**
     * Array of metaboxes/fields
     * @var array
     */
    public $id = 'upstream_tasks';

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
        $this->title = upstream_task_label_plural();
        $this->menu_title = $this->title;
        //$this->description = sprintf( __( '%s Description', 'upstream' ), upstream_task_label() );
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
                    'name' => __( 'Statuses', 'upstream' ),
                    'id'   => 'status_title',
                    'type' => 'title',
                    'desc' => sprintf( __( 'The statuses and colors that can be used for the status of %s.<br>These will become available in the %s Status dropdown within each %s', 'upstream' ), upstream_task_label_plural(), upstream_task_label(), upstream_task_label() )
                ),
                array(
                    'id'          => 'statuses',
                    'type'        => 'group',
                    'name'        => '',
                    'description' => '',
                    'options'     => array(
                        'group_title'   => __( 'Status {#}', 'upstream' ),
                        'add_button'    => __( 'Add Status', 'upstream' ),
                        'remove_button' => __( 'Remove Entry', 'upstream' ),
                        'sortable'      => true, // beta
                    ),
                    'sanitization_cb' => array($this, 'onBeforeSave'),
                    'fields'     => array(
                        array(
                            'name' => __( 'Hidden', 'upstream' ),
                            'id'   => 'id',
                            'type' => 'hidden',
                        ),
                        array(
                            'name'      => __( 'Status Color', 'upstream' ),
                            'id'        => 'color',
                            'type'      => 'colorpicker',
                            'attributes' => array(
                                'data-colorpicker' => json_encode( array(
                                    // Iris Options set here as values in the 'data-colorpicker' array
                                    'palettes' => upstream_colorpicker_default_colors(),
                                    'width' => 300,
                                ) ),
                            ),
                        ),
                        array(
                            'name' => __( 'Status Name', 'upstream' ),
                            'id'   => 'name',
                            'type' => 'text',
                        ),
                        array(
                            'name' => __( 'Type of Status', 'upstream' ),
                            'id'   => 'type',
                            'type' => 'radio',
                            'default' => 'open',
                            'desc' => __("A Status Name such as 'In Progress' or 'Overdue' would be considered Open.", 'upstream') . '<br>' . __("A Status Name such as 'Complete' or 'Cancelled' would be considered Closed.", 'upstream'),
                            'options' => array (
                                'open' => __( 'Open', 'upstream' ),
                                'closed' => __( 'Closed', 'upstream' ),
                            )
                        )
                    )
                ),



            ) )
        );

        return $options;

    }

    /**
     * Create missing id in a Tasks set.
     *
     * @since   @todo
     * @static
     *
     * @param   array   $tasks     Array of Tasks.
     *
     * @return  array
     */
    public static function createMissingIdsInSet($tasks)
    {
        if (!is_array($tasks)) {
            return false;
        }

        if (count($tasks) > 0) {
            $indexesMissingId = array();
            $idsMap = array();

            foreach ($tasks as $taskIndex => $task) {
                if (!isset($task['id'])
                    || empty($task['id'])
                ) {
                    $indexesMissingId[] = $taskIndex;
                } else {
                    $idsMap[$task['id']] = $taskIndex;
                }
            }

            if (count($indexesMissingId) > 0) {
                $newIdsLength = 5;
                $newIdsCharsPool = 'abcdefghijklmnopqrstuvwxyz0123456789';

                foreach ($indexesMissingId as $taskIndex) {
                    do {
                        $id = upstreamGenerateRandomString($newIdsLength, $newIdsCharsPool);
                    } while (isset($idsMap[$id]));

                    $tasks[$taskIndex]['id'] = $id;
                    $idsMap[$id] = $taskIndex;
                }
            }
        }

        return $tasks;
    }

    /**
     * Create id for newly added tasks statuses.
     * This method is called right before field data is saved to db.
     *
     * @since   @todo
     * @static
     *
     * @param   array           $value  Array of the new set of Task statuses.
     * @param   array           $args   Field arguments.
     * @param   \CMB2_Field     $field  The field object.
     *
     * @return  array           $value
     */
    public static function onBeforeSave($value, $args, $field)
    {
        if (is_array($value)) {
            $value = self::createMissingIdsInSet($value);
        }

        return $value;
    }
}


endif;


