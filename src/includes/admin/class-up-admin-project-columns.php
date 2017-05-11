<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Admin_Project_Columns' ) ) :

/**
 * Admin columns
 * @version 0.1.0
 */
class UpStream_Admin_Project_Columns {

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        return $this->hooks();
    }


    public function hooks() {
        add_filter( 'manage_project_posts_columns', array( $this, 'project_columns' ) );
        add_action( 'manage_project_posts_custom_column', array( $this, 'project_data' ), 10, 2 );

        // sorting
        add_filter( 'manage_edit-project_sortable_columns', array( $this, 'table_sorting' ) );
        add_filter( 'request', array( $this, 'project_orderby_status' ) );
        add_filter( 'request', array( $this, 'project_orderby_dates' ) );
        add_filter( 'request', array( $this, 'project_orderby_progress' ) );

        // filtering
        add_action( 'restrict_manage_posts', array( $this, 'table_filtering' ) );
        add_action( 'parse_query', array( $this, 'filter' ) );
    }

    /**
     * Set columns for project
     */
    public function project_columns( $defaults ) {

        $post_type  = $_GET['post_type'];

        $columns    = array();
        $taxonomies = array();

        /* Get taxonomies that should appear in the manage posts table. */
        $taxonomies = get_object_taxonomies( $post_type, 'objects');
        $taxonomies = wp_filter_object_list( $taxonomies, array( 'show_admin_column' => true ), 'and', 'name');

        /* Allow devs to filter the taxonomy columns. */
        $taxonomies = apply_filters("manage_taxonomies_for_upstream_{$post_type}_columns", $taxonomies, $post_type);
        $taxonomies = array_filter($taxonomies, 'taxonomy_exists');

        /* Loop through each taxonomy and add it as a column. */
        foreach ( $taxonomies as $taxonomy ) {
            $columns[ 'taxonomy-' . $taxonomy ] = get_taxonomy($taxonomy)->labels->name;
        }

        $defaults['owner']      = __( 'Owner', 'upstream' );
        $defaults['client']     = __( 'Client', 'upstream' );
        $defaults['start']      = __( 'Start', 'upstream' );
        $defaults['end']        = __( 'End', 'upstream' );
        $defaults['tasks']      = upstream_task_label_plural();
        if( ! upstream_disable_bugs() ) {
            $defaults['bugs']       = upstream_bug_label_plural();
        }
        $defaults['progress']   = __( 'Progress', 'upstream' );
        $defaults['messages']   = '<span class="dashicons dashicons-admin-comments"></span>';

        $defaults = array( 'project-status' => '' ) + $defaults;

        return $defaults;
    }

    public function project_data( $column_name, $post_id ) {

        if ( $column_name == 'project-status' ) {

            $status = upstream_project_status_color( $post_id );
            if( ! $status['status'] )
                return;

                echo '<div title="' . esc_attr( $status['status'] ) . '" style="width: 100%; position: absolute; top: 0px; left: 0px; overflow: hidden; height: 100%; border-left: 2px solid ' . esc_attr( $status['color'] ) . '" class="' . esc_attr( strtolower( $status['status'] ) ) . '"></div>';

        }

        if ( $column_name == 'owner' ) {
            echo upstream_project_owner_name( $post_id );
        }

        if ( $column_name == 'client' ) {
            echo upstream_project_client_name( $post_id );
        }

        if ( $column_name == 'start' ) {
            $start = upstream_project_start_date( $post_id );
            echo $start ? '<span class="start-date">' . upstream_format_date( $start ) . '</span>' : '';
        }

        if ( $column_name == 'end' ) {
            $end = upstream_project_end_date( $post_id );
            echo $end ? '<span class="end-date">' . upstream_format_date( $end ) . '</span>' : '';
        }

        if ( $column_name == 'tasks' ) {
            if (upstream_are_tasks_disabled()) {
                return;
            }

            $counts = upstream_project_tasks_counts( $post_id );
            $colors = upstream_project_task_statuses_colors();

            if( ! $counts )
                return;

            foreach ($counts as $status => $count) {
                $color = isset( $colors[$status] ) ? $colors[$status] : '#aaaaaa';
                echo '<span style="border-color:' . esc_attr( $color ) . '" class="status ' . esc_attr( strtolower( $status ) ) . '"><span class="count" style="background-color:' . esc_attr( $color ) . '">' . $count . '</span>' . $status . '</span>';
            }

        }

        if ( $column_name == 'bugs' ) {
            if (upstream_are_bugs_disabled()) {
                return;
            }

            $counts = upstream_project_bugs_counts( $post_id );
            $colors = upstream_project_bug_statuses_colors( $post_id );

            if( ! $counts )
                return;

            foreach ($counts as $status => $count) {
                $color = isset( $colors[$status] ) ? $colors[$status] : '#aaaaaa';
                echo '<span style="border-color:' . esc_attr( $color ) . '" class="status ' . esc_attr( strtolower( $status ) ) . '"><span class="count" style="background-color:' . esc_attr( $color ) . '">' . $count . '</span>' . $status . '</span>';
            }

        }

        if ( $column_name == 'progress' ) {
            echo upstream_project_progress( $post_id ) . '%';
        }

        if ( $column_name == 'messages' ) {
            $count = (int) upstream_count_total( 'discussion', $post_id );
            if( $count > 0 ) {
                echo '<a href="' . esc_url( get_edit_post_link( $post_id ) .'#_upstream_project_discussions' ) . '"><span>' . esc_html( $count ) . '</a></span>';
            } else {
                echo '—';
            }

        }

    }


    /*
     * Sorting the table
     */
    function table_sorting( $columns ) {
        $columns['project-status']  = 'project-status';
        $columns['start']           = 'start';
        $columns['end']             = 'end';
        $columns['progress']        = 'progress';
        return $columns;
    }


    function project_orderby_status( $vars ) {
        if ( isset( $vars['orderby'] ) && 'project-status' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_upstream_project_status',
                'orderby' => 'meta_value'
            ) );
        }

        return $vars;
    }

    function project_orderby_dates( $vars ) {
        if ( isset( $vars['orderby'] ) && 'start' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_upstream_project_start',
                'orderby' => 'meta_value_num'
            ) );
        }

        if ( isset( $vars['orderby'] ) && 'end' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_upstream_project_end',
                'orderby' => 'meta_value_num'
            ) );
        }

        return $vars;
    }

    function project_orderby_progress( $vars ) {
        if ( isset( $vars['orderby'] ) && 'progress' == $vars['orderby'] ) {
            $vars = array_merge( $vars, array(
                'meta_key' => '_upstream_project_progress',
                'orderby' => 'meta_value_num'
            ) );
        }

        return $vars;
    }


    function table_filtering() {

        $type = 'project';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }

        //only add filter to post type you want
        if ( 'project' == $type ){
            //change this to the list of values you want to show
            //in 'label' => 'value' format
            $option = get_option( 'upstream_projects' );
            $statuses = $option['statuses'];
            ?>

            <select name='project-status' id='project-status' class='postform'>
                <option value=''><?php printf( __( 'Show all %s', 'upstream' ), 'statuses' ) ?></option>
                <?php foreach ( $statuses as $status ) { ?>
                    <option value="<?php echo strtolower( $status['name'] ) ?>" <?php isset( $_GET['project-status'] ) ? selected( $_GET['project-status'], $status['name'] ) : ''; ?>><?php echo $status['name'] ?></option>
                <?php } ?>
            </select>

            <?php
        }
    }

    function filter( $query ){
        global $pagenow;
        $type = 'project';
        if (isset($_GET['post_type'])) {
            $type = $_GET['post_type'];
        }
        if ( 'project' == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET['project-status'] ) && $_GET['project-status'] != '' ) {
            $query->query_vars['meta_key'] = '_upstream_project_status';
            $query->query_vars['meta_value'] = $_GET['project-status'];
        }
    }


}

new UpStream_Admin_Project_Columns;

endif;
