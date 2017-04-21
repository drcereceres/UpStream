<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Metaboxes_Projects' ) ) :


/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Metaboxes_Projects {


    /**
     * Post type
     * @var string
     */
    public $type = 'project';

    /**
     * Metabox prefix
     * @var string
     */
    public $prefix = '_upstream_project_';

    public $project_label = '';

    /**
     * Holds an instance of the object
     *
     * @var Myprefix_Admin
     **/
    public static $instance = null;

    public function __construct() {
        $this->project_label = upstream_project_label();
    }

    /**
     * Returns the running object
     *
     * @return Myprefix_Admin
     **/
    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->overview();
            self::$instance->milestones();
            self::$instance->tasks();
            if( ! upstream_disable_bugs() ) {
                self::$instance->bugs();
            }
            self::$instance->files();
            self::$instance->details();
            self::$instance->sidebar_low();
            self::$instance->comments();
        }
        return self::$instance;
    }


/* ======================================================================================
                                        OVERVIEW
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function overview() {

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'overview',
            'title'         => $this->project_label . __( ' Overview', 'upstream' ) .
                '<span class="progress align-right"><progress value="' . upstream_project_progress() . '" max="100"></progress> <span>' . upstream_project_progress() . '%</span></span>',
            'object_types'  => array( $this->type ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        $grid1 = $metabox->add_field( array(
            'name'              => '<span>' . upstream_count_total( 'milestones', upstream_post_id() ) . '</span> ' . upstream_milestone_label_plural(),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'milestones',
            'type'              => 'title',
            'after'             => 'upstream_output_overview_counts',
        ) );
        $grid2 = $metabox->add_field( array(
            'name'              => '<span>' . upstream_count_total( 'tasks', upstream_post_id() ) . '</span> ' . upstream_task_label_plural(),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'tasks',
            'type'              => 'title',
            'after'             => 'upstream_output_overview_counts',
        ) );
        $grid3 = $metabox->add_field( array(
            'name'              => '<span>' . upstream_count_total( 'bugs', upstream_post_id() ) . '</span> ' . upstream_bug_label_plural(),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'bugs',
            'type'              => 'title',
            'after'             => 'upstream_output_overview_counts',
        ) );

        //Create now a Grid of group fields
        $row = $cmb2Grid->addRow();
        $row->addColumns(array($grid1, $grid2, $grid3));


    }


/* ======================================================================================
                                        MILESTONES
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function milestones() {

        $label          = upstream_milestone_label();
        $label_plural   = upstream_milestone_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'milestones',
            'title'         => '<span class="dashicons dashicons-flag"></span> ' . esc_html( $label_plural ),
            'object_types'  => array( $this->type ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        /*
         * Outputs some hidden data for dynamic use.
         */
        $metabox->add_field( array(
            'id'                => $this->prefix . 'hidden',
            'type'              => 'title',
            'description'       => '',
            'after'             => 'upstream_admin_output_milestone_hidden_data',
            'attributes'        => array(
                'class'             => 'hidden',
                'data-publish'      => upstream_admin_permissions( 'publish_project_milestones' ),
            ),
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'milestones',
            'type'              => 'group',
            'description'       => __( '', 'upstream' ),
            'permissions'       => 'delete_project_milestones', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_milestones' ),
            ),
        ) );

        $fields = array();

        $fields[0] = array(
            'id'            => 'id',
            'type'          => 'text',
            'before'        => 'upstream_add_field_attributes',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );
        $fields[1] = array(
            'id'            => 'created_by',
            'type'          => 'text',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );
        $fields[2] = array(
            'id'            => 'created_time',
            'type'          => 'text',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );


        // start row
        $fields[10] = array(
            'name'              => esc_html( $label ),
            'id'                => 'milestone',
            'type'              => 'select',
            //'show_option_none' => true, // ** IMPORTANT - enforce a value in this field.
            // An row with no value here is considered to be a deleted row.
            'permissions'       => 'milestone_milestone_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_options_milestones',
            'attributes'        => array(
                'class' => 'milestone',
            )
        );

        $fields[11] = array(
            'name'              => __( "Assigned To", 'upstream' ),
            'id'                => 'assigned_to',
            'type'              => 'select',
            'permissions'       => 'milestone_assigned_to_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none'  => true,
            'options_cb'        => 'upstream_admin_get_all_project_users',
        );


        // start row
        $fields[20] = array(
            'name'              => __( "Start Date", 'upstream' ),
            'id'                => 'start_date',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'milestone_start_date_field',
            'before'            => 'upstream_add_field_attributes',
            'default'           => time(),
            'attributes'        => array(
                //'data-validation'     => 'required',
            )
        );
        $fields[21] = array(
            'name'              => __( "End Date", 'upstream' ),
            'id'                => 'end_date',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'milestone_end_date_field',
            'before'            => 'upstream_add_field_attributes',
            'default'           => time() + ( 2 * 7 * 24 * 60 * 60 ), // time plus 2 weeks
            'attributes'        => array(
                //'data-validation'     => 'required',
            )
        );

        // start row
        $fields[30] = array(
            'name'              => __( "Notes", 'upstream' ),
            'id'                => 'notes',
            'type'              => 'wysiwyg',
            'permissions'       => 'milestone_notes_field',
            'before'            => 'upstream_add_field_attributes'
        );

        // set up the group grid plugin
        $cmb2GroupGrid = $cmb2Grid->addCmb2GroupGrid( $group_field_id );

        // define nuber of rows
        $rows = apply_filters( 'upstream_milestone_metabox_rows', 4 );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_milestone_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
                $fields[$key] = $metabox->add_group_field( $group_field_id, $value );
            }
        }

        // loop through number of rows
        for ($i=0; $i < $rows; $i++) {

            // add each row
            $row[$i] = $cmb2GroupGrid->addRow();

            // this is our hidden row that must remain as is
            if( $i == 0 ) {

                $row[0]->addColumns( array( $fields[0], $fields[1], $fields[2] ) );

            } else {

                // this allows up to 4 columns in each row
                $array = array();
                if( isset( $fields[$i * 10] ) ) {
                    $array[] = $fields[$i * 10];
                }
                if( isset( $fields[$i * 10 + 1] ) ) {
                    $array[] = $fields[$i * 10 + 1];
                }
                if( isset( $fields[$i * 10 + 2] ) ) {
                    $array[] = $fields[$i * 10 + 2];
                }
                if( isset( $fields[$i * 10 + 3] ) ) {
                    $array[] = $fields[$i * 10 + 3];
                }

                // add the fields as columns
                // probably don't need this to be filterable but will leave it for now
                $row[$i]->addColumns(
                    apply_filters( "upstream_milestone_row_{$i}_columns", $array )
                );

            }

        }



    }


/* ======================================================================================
                                        TASKS
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function tasks() {

        $label          = upstream_task_label();
        $label_plural   = upstream_task_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'tasks',
            'title'         => '<span class="dashicons dashicons-admin-tools"></span> ' . esc_html( $label_plural ),
            'object_types'  => array( $this->type ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        /*
         * Outputs some hidden data for dynamic use.
         */
        $metabox->add_field( array(
            'id'                => $this->prefix . 'hidden',
            'type'              => 'title',
            'description'       => '',
            'after'             => 'upstream_admin_output_task_hidden_data',
            'attributes'        => array(
                'class'         => 'hidden',
                'data-empty'    => upstream_empty_group( 'tasks' ),
                'data-publish'  => upstream_admin_permissions( 'publish_project_tasks' ),
            ),
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'tasks',
            'type'              => 'group',
            'description'       => __( '', 'upstream' ),
            'permissions'       => 'delete_project_tasks', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_tasks' ), // beta
            ),
        ) );

        $fields = array();

        $fields[0] = array(
            'id'                => 'id',
            'type'              => 'text',
            'before'            => 'upstream_add_field_attributes',
            'permissions'       => '',
            'attributes'        => array(
                'class' => 'hidden',
            )
        );
        $fields[1] = array(
            'id'                => 'created_by',
            'type'              => 'text',
            'attributes'        => array(
                'class' => 'hidden',
            )
        );
        $fields[2] = array(
            'id'                => 'created_time',
            'type'              => 'text',
            'attributes'        => array(
                'class' => 'hidden',
            )
        );

        // start row
        $fields[10] = array(
            'name'              => __( 'Title', 'upstream' ),
            'id'                => 'title',
            'type'              => 'text',
            'permissions'       => 'task_title_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'class'             => 'task-title',
                //'data-validation'     => 'required',
            )
        );

        $fields[11] = array(
            'name'              => __( "Assigned To", 'upstream' ),
            'id'                => 'assigned_to',
            'type'              => 'select',
            'permissions'       => 'task_assigned_to_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none'  => true,
            'options_cb'        => 'upstream_admin_get_all_project_users',
        );

        // start row
        $fields[20] = array(
            'name'              => __( "Status", 'upstream' ),
            'id'                => 'status',
            'type'              => 'select',
            'permissions'       => 'task_status_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none' => true,  // ** IMPORTANT - do not enforce a value in this field.
            // An row with no value here is considered to be a deleted row.
            'options_cb'        => 'upstream_admin_get_task_statuses',
            'attributes'        => array(
                'class' => 'task-status',
            )
        );

        $fields[21] = array(
            'name'              => __( "Progress", 'upstream' ),
            'id'                => 'progress',
            'type'              => 'select',
            'permissions'       => 'task_progress_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_get_percentages_for_dropdown',
            'attributes'        => array(
                'class' => 'task-progress',
            )
        );

        // start row
        $fields[30] = array(
            'name'              => __( "Start Date", 'upstream' ),
            'id'                => 'start_date',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'task_start_date_field',
            'before'            => 'upstream_add_field_attributes',
            'default'           => time(),
            'attributes'        => array(
                //'data-validation'     => 'required',
            ),
        );
        $fields[31] = array(
            'name'              => __( "End Date", 'upstream' ),
            'id'                => 'end_date',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'task_end_date_field',
            'before'            => 'upstream_add_field_attributes',
            'default'           => time() + ( 2 * 7 * 24 * 60 * 60 ), // time plus 2 weeks
            'attributes'        => array(
                //'data-validation'     => 'required',
            ),
        );

        $fields[40] = array(
            'name'              => __( "Notes", 'upstream' ),
            'id'                => 'notes',
            'type'              => 'textarea_small',
            'permissions'       => 'task_notes_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'rows' => 2,
            )
        );

        $fields[41] = array(
            'name'              => '<span class="dashicons dashicons-flag"></span> ' . esc_html( upstream_milestone_label() ),
            'id'                => 'milestone',
            'desc'              =>
                __( 'Selecting a milestone will count this task\'s progress toward that milestone as well as overall project progress.', 'upstream' ),
            'type'              => 'select',
            'permissions'       => 'task_milestone_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none'  => true,
            'options_cb'        => 'upstream_admin_get_project_milestones',
            'attributes'        => array(
                'class' => 'task-milestone',
            )
        );

        // set up the group grid plugin
        $cmb2GroupGrid = $cmb2Grid->addCmb2GroupGrid( $group_field_id );

        // define nuber of rows
        $rows = apply_filters( 'upstream_task_metabox_rows', 5 );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_task_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
                $fields[$key] = $metabox->add_group_field( $group_field_id, $value );
            }
        }

        // loop through number of rows
        for ($i=0; $i < $rows; $i++) {

            // add each row
            $row[$i] = $cmb2GroupGrid->addRow();

            // this is our hidden row that must remain as is
            if( $i == 0 ) {

                $row[0]->addColumns( array( $fields[0], $fields[1], $fields[2] ) );

            } else {

                // this allows up to 4 columns in each row
                $array = array();
                if( isset( $fields[$i * 10] ) ) {
                    $array[] = $fields[$i * 10];
                }
                if( isset( $fields[$i * 10 + 1] ) ) {
                    $array[] = $fields[$i * 10 + 1];
                }
                if( isset( $fields[$i * 10 + 2] ) ) {
                    $array[] = $fields[$i * 10 + 2];
                }
                if( isset( $fields[$i * 10 + 3] ) ) {
                    $array[] = $fields[$i * 10 + 3];
                }

                // add the fields as columns
                $row[$i]->addColumns(
                    apply_filters( "upstream_task_row_{$i}_columns", $array )
                );

            }

        }

    }


/* ======================================================================================
                                        BUGS
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function bugs() {

        $label          = upstream_bug_label();
        $label_plural   = upstream_bug_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'bugs',
            'title'         => '<span class="dashicons dashicons-warning"></span> ' . esc_html( $label_plural ),
            'object_types'  => array( $this->type ),
            'attributes'  => array( 'data-test' => 'test' ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        /*
         * Outputs some hidden data for dynamic use.
         */
        $metabox->add_field( array(
            'id'            => $this->prefix . 'hidden',
            'type'          => 'title',
            'description'   => '',
            'after'         => 'upstream_admin_output_bug_hidden_data',
            'attributes'    => array(
                'class'         => 'hidden',
                'data-empty'    => upstream_empty_group( 'bugs' ),
                'data-publish'  => upstream_admin_permissions( 'publish_project_bugs' ),
            ),
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'bugs',
            'type'              => 'group',
            'description'       => __( '', 'upstream' ),
            'permissions'       => 'delete_project_bugs', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_bugs' ),
            ),
        ) );

        $fields = array();

        $fields[0] = array(
            'id'            => 'id',
            'type'          => 'text',
            'before'        => 'upstream_add_field_attributes',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );
        $fields[1] = array(
            'id'            => 'created_by',
            'type'          => 'text',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );
        $fields[2] = array(
            'id'            => 'created_time',
            'type'          => 'text',
            'attributes'    => array(
                'class' => 'hidden',
            )
        );

        // start row
        $fields[10] = array(
            'name'              => __( 'Title', 'upstream' ),
            'id'                => 'title',
            'type'              => 'text',
            'permissions'       => 'bug_title_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'class'             => 'bug-title',
            )
        );

        $fields[11] = array(
            'name'              => __( "Assigned To", 'upstream' ),
            'id'                => 'assigned_to',
            'type'              => 'select',
            'permissions'       => 'bug_assigned_to_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none'  => true,
            'options_cb'        => 'upstream_admin_get_all_project_users',
        );

        // start row
        $fields[20] = array(
            'name'              => __( "Description", 'upstream' ),
            'id'                => 'description',
            'type'              => 'textarea_small',
            'permissions'       => 'bug_description_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'rows' => 2,
            )
        );

        // start row
        $fields[30] = array(
            'name'              => __( "Status", 'upstream' ),
            'id'                => 'status',
            'type'              => 'select',
            'permissions'       => 'bug_status_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none' => true, // ** IMPORTANT - do not enforce a value in this field.
            // An row with no value here is considered to be a deleted row.
            'options_cb'        => 'upstream_admin_get_bug_statuses',
            'attributes'        => array(
                'class'             => 'bug-status',
            )
        );
        $fields[31] = array(
            'name'              => '' . __( "Severity", 'upstream' ),
            'id'                => 'severity',
            'type'              => 'select',
            'permissions'       => 'bug_severity_field',
            'before'            => 'upstream_add_field_attributes',
            'show_option_none'  => true,
            'options_cb'        => 'upstream_admin_get_bug_severities',
            'attributes'        => array(
                'class' => 'bug-severity',
            )
        );

        // start row
        $fields[40] = array(
            'name'              => __( 'Attachments', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => 'file',
            'type'              => 'file',
            'permissions'       => 'bug_files_field',
            'before'            => 'upstream_add_field_attributes',
            'options' => array(
                'url' => false, // Hide the text input for the url
            ),
        );

        $fields[41] = array(
            'name'              => __( "Due Date", 'upstream' ),
            'id'                => 'due_date',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'bug_due_date_field',
            'before'            => 'upstream_add_field_attributes',
        );

        // set up the group grid plugin
        $cmb2GroupGrid = $cmb2Grid->addCmb2GroupGrid( $group_field_id );

        // define nuber of rows
        $rows = apply_filters( 'upstream_bug_metabox_rows', 5 );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_bug_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
                $fields[$key] = $metabox->add_group_field( $group_field_id, $value );
            }
        }

        // loop through number of rows
        for ($i=0; $i < $rows; $i++) {

            // add each row
            $row[$i] = $cmb2GroupGrid->addRow();

            // this is our hidden row that must remain as is
            if( $i == 0 ) {

                $row[0]->addColumns( array( $fields[0], $fields[1], $fields[2] ) );

            } else {

                // this allows up to 4 columns in each row
                $array = array();
                if( isset( $fields[$i * 10] ) ) {
                    $array[] = $fields[$i * 10];
                }
                if( isset( $fields[$i * 10 + 1] ) ) {
                    $array[] = $fields[$i * 10 + 1];
                }
                if( isset( $fields[$i * 10 + 2] ) ) {
                    $array[] = $fields[$i * 10 + 2];
                }
                if( isset( $fields[$i * 10 + 3] ) ) {
                    $array[] = $fields[$i * 10 + 3];
                }

                // add the fields as columns
                $row[$i]->addColumns(
                    apply_filters( "upstream_bug_row_{$i}_columns", $array )
                );

            }

        }



    }



/* ======================================================================================
                                        SIDEBAR TOP
   ====================================================================================== */

    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function details() {

        $client_label           = upstream_client_label();
        $client_label_plural    = upstream_client_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'details',
            'title'         => '<span class="dashicons dashicons-admin-generic"></span> ' . sprintf( __( "%s Details", 'upstream' ), $this->project_label ),
            'object_types'  => array( $this->type ),
            'context'       => 'side',
            'priority'      => 'high',
        ) );

        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid( $metabox );

        $fields = array();

        $fields[0] = array(
            'name'              => __( 'Status', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'status',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_status_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_project_statuses',
        );

        $fields[1] = array(
            'name'              => __( 'Owner', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'owner',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_owner_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_project_users',
        );
        $fields[2] = array(
            'name'              => $client_label,
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'client',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_client_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_clients',
        );

        $fields[3] = array(
            'name'              => sprintf( __( '%s Users', 'upstream' ), $client_label ),
            'desc'              => sprintf( __( 'Selected users can access the project by logging in with their email address & the %s password.', 'upstream' ), $client_label ),
            'id'                => $this->prefix . 'client_users',
            'type'              => 'multicheck',
            'select_all_button' => false,
            'permissions'       => 'project_users_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_clients_users',
        );

        $fields[10] = array(
            'name'              => __( 'Start Date', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'start',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'project_start_date_field',
            'before'            => 'upstream_add_field_attributes',
            'show_on_cb'        => 'upstream_show_project_start_date_field',
        );
        $fields[11] = array(
            'name'              => __( 'End Date', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'end',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'project_end_date_field',
            'before'            => 'upstream_add_field_attributes',
            'show_on_cb'        => 'upstream_show_project_end_date_field',
        );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_details_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
                //pp( $value );
                $fields[$key] = $metabox->add_field( $value );
            }
        }

        $row = $cmb2Grid->addRow();
        $row->addColumns(array( $fields[10], $fields[11] ));


    }



/* ======================================================================================
                                        Files
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function files() {

        $label          = upstream_file_label();
        $label_plural   = upstream_file_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'files',
            'title'         => '<span class="dashicons dashicons-paperclip"></span> ' . esc_html( $label_plural ),
            'object_types'  => array( $this->type ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        /*
         * Outputs some hidden data for dynamic use.
         */
        $metabox->add_field( array(
            'id'            => $this->prefix . 'hidden',
            'type'          => 'title',
            'description'   => '',
            //'after'       => 'upstream_admin_output_files_hidden_data',
            'attributes'    => array(
                'class'         => 'hidden',
                'data-empty'    => upstream_empty_group( 'files' ),
                'data-publish'  => upstream_admin_permissions( 'publish_project_files' ),

            ),
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'files',
            'type'              => 'group',
            'description'       => __( '', 'upstream' ),
            'permissions'       => 'delete_project_files', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_files' ),
            ),
        ) );

        $fields = array();

        // start row
        $fields[0] = array(
            'id'            => 'id',
            'type'          => 'text',
            'before'        => 'upstream_add_field_attributes',
            'attributes'    => array( 'class' => 'hidden' )
        );
        $fields[1] = array(
            'id'            => 'created_by',
            'type'          => 'text',
            'attributes'    => array( 'class' => 'hidden' )
        );
        $fields[2] = array(
            'id'            => 'created_time',
            'type'          => 'text',
            'attributes'    => array( 'class' => 'hidden' )
        );

        // start row
        $fields[10] = array(
            'name'              => __( 'Title', 'upstream' ),
            'id'                => 'title',
            'type'              => 'text',
            'permissions'       => 'file_title_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'class'             => 'file-title',
            )
        );
        $fields[11] = array(
            'name'              => esc_html( $label ),
            'desc'              => __( '', 'upstream' ),
            'id'                => 'file',
            'type'              => 'file',
            'permissions'       => 'file_files_field',
            'before'            => 'upstream_add_field_attributes',
            'options' => array(
                'url' => false, // Hide the text input for the url
            ),
        );

        // start row
        $fields[20] = array(
            'name'              => __( "Description", 'upstream' ),
            'id'                => 'description',
            'type'              => 'textarea_small',
            'permissions'       => 'file_description_field',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'rows' => 2,
            )
        );

        // set up the group grid plugin
        $cmb2GroupGrid = $cmb2Grid->addCmb2GroupGrid( $group_field_id );

        // define nuber of rows
        $rows = apply_filters( 'upstream_file_metabox_rows', 3 );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_file_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
                $fields[$key] = $metabox->add_group_field( $group_field_id, $value );
            }
        }

        // loop through number of rows
        for ($i=0; $i < $rows; $i++) {

            // add each row
            $row[$i] = $cmb2GroupGrid->addRow();

            // this is our hidden row that must remain as is
            if( $i == 0 ) {

                $row[0]->addColumns( array( $fields[0], $fields[1], $fields[2] ) );

            } else {

                // this allows up to 4 columns in each row
                $array = array();
                if( isset( $fields[$i * 10] ) ) {
                    $array[] = $fields[$i * 10];
                }
                if( isset( $fields[$i * 10 + 1] ) ) {
                    $array[] = $fields[$i * 10 + 1];
                }
                if( isset( $fields[$i * 10 + 2] ) ) {
                    $array[] = $fields[$i * 10 + 2];
                }
                if( isset( $fields[$i * 10 + 3] ) ) {
                    $array[] = $fields[$i * 10 + 3];
                }

                // add the fields as columns
                $row[$i]->addColumns(
                    apply_filters( "upstream_file_row_{$i}_columns", $array )
                );

            }

        }



    }


/* ======================================================================================
                                        SIDEBAR LOW
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function sidebar_low() {

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'activity',
            'title'         => '<span class="dashicons dashicons-update"></span> ' . __( 'Activity', 'upstream' ),
            'object_types'  => array( $this->type ),
            'context'      => 'side', //  'normal', 'advanced', or 'side'
            'priority'     => 'low',  //  'high', 'core', 'default' or 'low'
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        /*
         * Outputs some hidden data for dynamic use.
         */
        $metabox->add_field( array(
            'name'              => '',
            'desc'              => '',
            'id'                => $this->prefix . 'activity',
            'type'              => 'title',
            'before'            => 'upstream_activity_buttons',
            'after'             => 'upstream_output_activity',
        ) );



    }

    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function comments() {

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'discussions',
            'title'         => '<span class="dashicons dashicons-format-chat"></span> ' . __( "Discussion", 'upstream' ),
            'object_types'  => array( $this->type ),
            'priority'      => 'low',
        ) );
        $metabox->add_field( array(
            'name'              => __( 'New Message', 'upstream' ),
            'desc'              => __( '', 'upstream' ),
            'id'                => $this->prefix . 'new_message',
            'type'              => 'textarea_small',
            'permissions'       => 'publish_project_discussion',
            'before'            => 'upstream_add_field_attributes',
            'attributes'        => array(
                'rows' => 1,
            ),
            'after_field'       => 'upstream_admin_discussion_button',
            'after_row'         => 'upstream_admin_display_messages',
        ) );



    }

}

endif;
