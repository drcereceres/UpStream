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

        do_action('upstream_admin_notices_errors');
    }

    /**
     * Returns the running object
     *
     * @return Myprefix_Admin
     **/
    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();

            if (upstream_post_id() > 0) {
                self::$instance->overview();
            }

            if (!upstream_disable_milestones()) {
                self::$instance->milestones();
            }

            if (!upstream_disable_tasks()) {
                self::$instance->tasks();
            }

            if(!upstream_disable_bugs()) {
                self::$instance->bugs();
            }

            if (!upstream_disable_files()) {
                self::$instance->files();
            }

            self::$instance->details();
            self::$instance->sidebar_low();

            if (!upstream_disable_discussions()) {
                self::$instance->comments();
            }

            do_action('upstream_details_metaboxes');
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
        $areMilestonesDisabled = upstream_are_milestones_disabled();
        $areMilestonesDisabledAtAll = upstream_disable_milestones();
        $areTasksDisabled = upstream_are_tasks_disabled();
        $areBugsDisabled = upstream_are_bugs_disabled();

        if ((!$areMilestonesDisabled && $areMilestonesDisabledAtAll) || !$areTasksDisabled || !$areBugsDisabled) {
            $metabox = new_cmb2_box( array(
                'id'            => $this->prefix . 'overview',
                'title'         => $this->project_label . __( ' Overview', 'upstream' ) .
                    '<span class="progress align-right"><progress value="' . upstream_project_progress() . '" max="100"></progress> <span>' . upstream_project_progress() . '%</span></span>',
                'object_types'  => array( $this->type ),
            ) );

            //Create a default grid
            $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

            $columnsList = array();

            if (!$areMilestonesDisabled && !$areMilestonesDisabledAtAll) {
                array_push($columnsList, $metabox->add_field( array(
                    'name'  => '<span>' . upstream_count_total( 'milestones', upstream_post_id() ) . '</span> ' . upstream_milestone_label_plural(),
                    'id'    => $this->prefix . 'milestones',
                    'type'  => 'title',
                    'after' => 'upstream_output_overview_counts'
                )));
            }

            if (!upstream_disable_tasks()) {
                if (!$areTasksDisabled) {
                    $grid2 = $metabox->add_field( array(
                        'name'              => '<span>' . upstream_count_total( 'tasks', upstream_post_id() ) . '</span> ' . upstream_task_label_plural(),
                        'desc'              => '',
                        'id'                => $this->prefix . 'tasks',
                        'type'              => 'title',
                        'after'             => 'upstream_output_overview_counts',
                    ) );
                    array_push($columnsList, $grid2);
                }
            }

            if (!$areBugsDisabled) {
                $grid3 = $metabox->add_field( array(
                    'name'              => '<span>' . upstream_count_total( 'bugs', upstream_post_id() ) . '</span> ' . upstream_bug_label_plural(),
                    'desc'              => '',
                    'id'                => $this->prefix . 'bugs',
                    'type'              => 'title',
                    'after'             => 'upstream_output_overview_counts',
                ) );
                array_push($columnsList, $grid3);
            }

            //Create now a Grid of group fields
            $row = $cmb2Grid->addRow();
            $row->addColumns($columnsList);
        }
    }


/* ======================================================================================
                                        MILESTONES
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function milestones() {
        $areMilestonesDisabled = upstream_are_milestones_disabled();
        $areMilestonesDisabledAtAll = upstream_disable_milestones();
        $userHasAdminPermissions = upstream_admin_permissions('disable_project_milestones');

        if ($areMilestonesDisabledAtAll || ($areMilestonesDisabled && !$userHasAdminPermissions)) {
            return;
        }

        $label          = upstream_milestone_label();
        $label_plural   = upstream_milestone_label_plural();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'milestones',
            'title'         => '<span class="dashicons dashicons-flag"></span> ' . esc_html( $label_plural ),
            'object_types'  => array( $this->type )
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
            )
        ) );

        if (!$areMilestonesDisabled) {
            $group_field_id = $metabox->add_field( array(
                'id'                => $this->prefix . 'milestones',
                'type'              => 'group',
                'description'       => '',
                'permissions'       => 'delete_project_milestones', // also set on individual row level
                'options'           => array(
                    'group_title'   => esc_html( $label ) . " {#}",
                    'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                    'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                    'sortable'      => upstream_admin_permissions( 'sort_project_milestones' ),
                ),
                'after_group' =>
                    $this->getFiltersHeaderHtml() .
                    $this->getAssignedToFilterHtml() .
                    $this->getFiltersFooterHtml()
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
                'before'            => 'upstream_add_field_attributes',
                'options'           => array(
                    'media_buttons' => true,
                    'textarea_rows' => 5
                ),
                'escape_cb'         => 'applyOEmbedFiltersToWysiwygEditorContent'
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

        if ($userHasAdminPermissions) {
            $metabox->add_field(array(
                'id'          => $this->prefix .'disable_milestones',
                'type'        => 'checkbox',
                'description' => __('Disable Milestones for this project', 'upstream')
            ));
        }
    }

    /**
     * Return the Assigned To filter HTML.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getAssignedToFilterHtml()
    {
        $upstreamUsersList = upstream_admin_get_all_project_users();
        $usersOptionsHtml = '<option>- ' . __('Show Everyone', 'upstream') . ' -</option>';
        foreach ($upstreamUsersList as $userId => $userName) {
            $usersOptionsHtml .= sprintf('<option value="%s">%s</option>', $userId, $userName);
        }

        $html = sprintf('
            <div class="col-md-4">
                <div>
                    <label>%s</label>
                    <select class="cmb-type-select upstream-filter upstream-filter-assigned_to" data-disabled="false" data-owner="true" data-no-items-found-message="%s" data-column="assigned_to">
                        %s
                    </select>
                </div>
            </div>',
            __('Assigned To', 'upstream'),
            __('No items found.', 'upstream'),
            $usersOptionsHtml
        );

        return $html;
    }

    /**
     * Return the Status filter HTML.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getStatusFilterHtml()
    {
        $upstreamStatusList = upstream_admin_get_task_statuses();
        $statusOptionsHtml = '<option>- ' . __('Show All', 'upstream') . ' -</option>';
        foreach ($upstreamStatusList as $statusId => $statusTitle) {
            $statusOptionsHtml .= sprintf('<option value="%s">%s</option>', $statusId, $statusTitle);
        }

        $html = sprintf('
            <div class="col-md-4">
                <div>
                    <label>%s</label>
                    <select class="cmb-type-select upstream-filter upstream-filter-status" data-disabled="false" data-owner="true" data-no-items-found-message="%s" data-column="status">
                        %s
                    </select>
                </div>
            </div>',
            __('Status', 'upstream'),
            __('No items found.', 'upstream'),
            $statusOptionsHtml
        );

        return $html;
    }

    /**
     * Return the Severity filter HTML.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getSeverityFilterHtml()
    {
        $upstreamSeveritiesList = upstream_admin_get_bug_severities();
        $statusOptionsHtml = '<option>- ' . __('Show All', 'upstream') . ' -</option>';
        foreach ($upstreamSeveritiesList as $severityId => $severityTitle) {
            $statusOptionsHtml .= sprintf('<option value="%s">%s</option>', $severityId, $severityTitle);
        }

        $html = sprintf('
            <div class="col-md-4">
                <div>
                    <label>%s</label>
                    <select class="cmb-type-select upstream-filter upstream-filter-severity" data-disabled="false" data-owner="true" data-column="severity" data-no-items-found-message="%s">
                        %s
                    </select>
                </div>
            </div>',
            __('Severity', 'upstream'),
            __('No items found.', 'upstream'),
            $statusOptionsHtml
        );

        return $html;
    }

    /**
     * Return the HTML that opens the Filters wrapper.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getFiltersHeaderHtml()
    {
        $html = '<div class="row upstream-filters-wrapper">';

        return $html;
    }

    /**
     * Return the HTML that closes the Filters wrapper.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getFiltersFooterHtml()
    {
        $html = '</div>';

        return $html;
    }

    /**
     * Return the Milestone filter HTML.
     *
     * @since   1.0.0
     * @access  private
     *
     * @return  string
     */
    private function getMilestoneFilterHtml()
    {
        $upstreamMilestonesList = upstream_admin_get_options_milestones();
        $milestonesOptionsHtml = '<option>- ' . __('Show All', 'upstream') . ' -</option>';
        foreach ($upstreamMilestonesList as $milestoneId => $milestoneTitle) {
            $milestonesOptionsHtml .= sprintf('<option value="%s">%s</option>', $milestoneId, $milestoneTitle);
        }

        $html = sprintf('
            <div class="col-md-4">
                <div>
                    <label>%s</label>
                    <select class="cmb-type-select upstream-filter upstream-filter-milestone" data-disabled="false" data-owner="true" data-no-items-found-message="%s" data-column="milestone">
                        %s
                    </select>
                </div>
            </div>',
            __('Milestone', 'upstream'),
            __('No items found.', 'upstream'),
            $milestonesOptionsHtml
        );

        return $html;
    }


/* ======================================================================================
                                        TASKS
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function tasks() {
        $areTasksDisabled = upstream_are_tasks_disabled();
        $userHasAdminPermissions = upstream_admin_permissions('disable_project_tasks');

        if (upstream_disable_tasks() || ($areTasksDisabled && !$userHasAdminPermissions)) {
            return;
        }

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
            )
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'tasks',
            'type'              => 'group',
            'description'       => '',
            'permissions'       => 'delete_project_tasks', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_tasks' ), // beta
            ),
            'after_group'       =>
                $this->getFiltersHeaderHtml() .
                $this->getAssignedToFilterHtml() .
                $this->getMilestoneFilterHtml() .
                $this->getStatusFilterHtml() .
                $this->getFiltersFooterHtml()
        ) );

        if (!$areTasksDisabled) {
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
                'before'            => 'upstream_add_field_attributes'
            );
            $fields[31] = array(
                'name'              => __( "End Date", 'upstream' ),
                'id'                => 'end_date',
                'type'              => 'text_date_timestamp',
                'date_format'       => 'Y-m-d',
                'permissions'       => 'task_end_date_field',
                'before'            => 'upstream_add_field_attributes'
            );

            $fields[40] = array(
                'name'              => __( "Notes", 'upstream' ),
                'id'                => 'notes',
                'type'              => 'wysiwyg',
                'permissions'       => 'task_notes_field',
                'before'            => 'upstream_add_field_attributes',
                'options'           => array(
                    'media_buttons' => true,
                    'textarea_rows' => 5
                ),
                'escape_cb'         => 'applyOEmbedFiltersToWysiwygEditorContent'
            );

            if (!upstream_are_milestones_disabled() && !upstream_disable_milestones()) {
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
            } else {
                $fields[40]['before_field'] = '<div class="row"><div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">';
                $fields[40]['after_field'] = '</div><div class="hidden-xs hidden-sm col-md-6 col-lg-6"></div></div>';
            }

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

        if ($userHasAdminPermissions) {
            $metabox->add_field(array(
                'id'          => $this->prefix .'disable_tasks',
                'type'        => 'checkbox',
                'description' => __('Disable Tasks for this project', 'upstream')
            ));
        }
    }

/* ======================================================================================
                                        BUGS
   ====================================================================================== */
    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function bugs()  {
        $areBugsDisabled = upstream_are_bugs_disabled();
        $userHasAdminPermissions = upstream_admin_permissions('disable_project_bugs');

        if (upstream_disable_bugs() || ($areBugsDisabled && !$userHasAdminPermissions)) {
            return;
        }

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
            )
        ) );

        $group_field_id = $metabox->add_field( array(
            'id'                => $this->prefix . 'bugs',
            'type'              => 'group',
            'description'       => '',
            'permissions'       => 'delete_project_bugs', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_bugs' ),
            ),
            'after_group'       =>
                $this->getFiltersHeaderHtml() .
                $this->getAssignedToFilterHtml() .
                $this->getStatusFilterHtml() .
                $this->getSeverityFilterHtml() .
                $this->getFiltersFooterHtml()
        ) );

        if (!$areBugsDisabled) {
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
                'type'              => 'wysiwyg',
                'permissions'       => 'bug_description_field',
                'before'            => 'upstream_add_field_attributes',
                'options'           => array(
                    'media_buttons' => true,
                    'textarea_rows' => 5
                ),
                'escape_cb'         => 'applyOEmbedFiltersToWysiwygEditorContent'
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
                'name'              => __( "Severity", 'upstream' ),
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
                'desc'              => '',
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

        if ($userHasAdminPermissions) {
            $metabox->add_field(array(
                'id'          => $this->prefix .'disable_bugs',
                'type'        => 'checkbox',
                'description' => __('Disable Bugs for this project', 'upstream')
            ));
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
            'desc'              => '',
            'id'                => $this->prefix . 'status',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_status_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_project_statuses',
            'save_field'        => upstream_admin_permissions('project_status_field')
        );

        $fields[1] = array(
            'name'              => __( 'Owner', 'upstream' ),
            'desc'              => '',
            'id'                => $this->prefix . 'owner',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_owner_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_project_users',
            'save_field'        => upstream_admin_permissions('project_owner_field')
        );
        $fields[2] = array(
            'name'              => $client_label,
            'desc'              => '',
            'id'                => $this->prefix . 'client',
            'type'              => 'select',
            'show_option_none'  => true,
            'permissions'       => 'project_client_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_clients',
            'save_field'        => upstream_admin_permissions('project_client_field')
        );

        $fields[3] = array(
            'name'              => sprintf( __( '%s Users', 'upstream' ), $client_label ),
            'id'                => $this->prefix . 'client_users',
            'type'              => 'multicheck',
            'select_all_button' => false,
            'permissions'       => 'project_users_field',
            'before'            => 'upstream_add_field_attributes',
            'options_cb'        => 'upstream_admin_get_all_clients_users',
            'save_field'        => upstream_admin_permissions('project_users_field')
        );

        $fields[10] = array(
            'name'              => __( 'Start Date', 'upstream' ),
            'desc'              => '',
            'id'                => $this->prefix . 'start',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'project_start_date_field',
            'before'            => 'upstream_add_field_attributes',
            'show_on_cb'        => 'upstream_show_project_start_date_field',
            'save_field'        => upstream_admin_permissions('upstream_start_date_field')
        );
        $fields[11] = array(
            'name'              => __( 'End Date', 'upstream' ),
            'desc'              => '',
            'id'                => $this->prefix . 'end',
            'type'              => 'text_date_timestamp',
            'date_format'       => 'Y-m-d',
            'permissions'       => 'project_end_date_field',
            'before'            => 'upstream_add_field_attributes',
            'show_on_cb'        => 'upstream_show_project_end_date_field',
            'save_field'        => upstream_admin_permissions('project_end_date_field')
        );

        $fields[12] = array(
            'name'              => __( "Description", 'upstream' ),
            'desc'              => '',
            'id'                => $this->prefix . 'description',
            'type'              => 'wysiwyg',
            'permissions'       => 'project_description',
            'before'            => 'upstream_add_field_attributes',
            'options'           => array(
                'media_buttons' => false,
                'textarea_rows' => 3,
                'teeny'         => true
            ),
            'save_field'        => upstream_admin_permissions('project_description')
        );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_details_metabox_fields', $fields );
        ksort( $fields );

        // loop through ordered fields and add them to the group
        if( $fields ) {
            foreach ($fields as $key => $value) {
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
        $areFilesDisabled = upstream_are_files_disabled();
        $userHasAdminPermissions = upstream_admin_permissions('disable_project_files');

        if (upstream_disable_files() || ($areFilesDisabled && !$userHasAdminPermissions)) {
            return;
        }

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
            'description'       => '',
            'permissions'       => 'delete_project_files', // also set on individual row level
            'options'           => array(
                'group_title'   => esc_html( $label ) . " {#}",
                'add_button'    => sprintf( __( "Add %s", 'upstream' ), esc_html( $label ) ),
                'remove_button' => sprintf( __( "Delete %s", 'upstream' ), esc_html( $label ) ),
                'sortable'      => upstream_admin_permissions( 'sort_project_files' ),
            ),
        ) );

        if (!$areFilesDisabled) {
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
                'desc'              => '',
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
                'type'              => 'wysiwyg',
                'permissions'       => 'file_description_field',
                'before'            => 'upstream_add_field_attributes',
                'options'           => array(
                    'media_buttons' => true,
                    'textarea_rows' => 3
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

        if ($userHasAdminPermissions) {
            $metabox->add_field(array(
                'id'          => $this->prefix .'disable_files',
                'type'        => 'checkbox',
                'description' => __('Disable Files for this project', 'upstream')
            ));
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
        if (upstream_disable_discussions()) {
            return;
        }

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'discussions',
            'title'         => '<span class="dashicons dashicons-format-chat"></span> ' . __( "Discussion", 'upstream' ),
            'object_types'  => array( $this->type ),
            'priority'      => 'low',
        ) );

        $metabox->add_field( array(
            'name'              => __( 'New Message', 'upstream' ),
            'desc'              => '',
            'id'                => $this->prefix . 'new_message',
            'type'              => 'wysiwyg',
            'permissions'       => 'publish_project_discussion',
            'before'            => 'upstream_add_field_attributes',
            'after_field'       => '<p><button class="button" id="new_message" type="button">' . __( 'New Message', 'upstream ') . '</button></p></div><div class="col-xs-12 col-sm-12 col-md-6 col-lg-6"></div></div>',
            'after_row'         => 'upstream_admin_display_messages',
            'options'           => array(
                'media_buttons' => true,
                'textarea_rows' => 5
            ),
            'escape_cb'         => 'applyOEmbedFiltersToWysiwygEditorContent',
            'before_field'      => '<div class="row"><div class="hidden-xs hidden-sm col-md-6 col-lg-6">'
        ) );
    }
}

endif;
