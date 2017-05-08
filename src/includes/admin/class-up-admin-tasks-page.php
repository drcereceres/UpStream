<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Upstream_Task_List extends WP_List_Table {

    private $columns = array();

    public $task_label = '';
    public $task_label_plural = '';

    /** Class constructor */
    public function __construct() {

        $this->task_label         = upstream_task_label();
        $this->task_label_plural  = upstream_task_label_plural();

        parent::__construct( array(
            'singular' => $this->task_label,
            'plural'   => $this->task_label_plural,
            'ajax'     => false //does this table support ajax?
        ) );

    }

    function get_columns() {
        return $columns = apply_filters( 'upstream_admin_task_page_columns', array(
            'title'         => $this->task_label,
            'progress'      => __( 'Progress', 'upstream' ),
            'project'       => upstream_project_label(),
            'milestone'     => upstream_milestone_label(),
            'assigned_to'   => __( 'Assigned To', 'upstream' ),
            'end_date'      => __( 'End Date', 'upstream' ),
            'status'        => __( 'Status', 'upstream' ),
        ) );
    }

    /*
     * Displays the filtering links above the table
     */
    function get_views(){

        $views = array();

        if( ! empty( $_REQUEST['status'] ) ) {
            $current = esc_html( $_REQUEST['status'] );
        } elseif ( ! empty( $_REQUEST['view'] ) ) {
            $current = esc_html( $_REQUEST['view'] );
        } else {
            $current = 'all';
        }

        //All link
        $all_class      = ( $current == 'all' ? ' class="current"' : '' );
        $all_url        = remove_query_arg( array( 'status', 'view' ) );
        $all_count      = upstream_count_total( 'tasks' );
        // pp($all_count);
        $views['all']   = "<a href='" . esc_url( $all_url ) . "' " . esc_attr( $all_class ) . " >" . __( 'All', 'upstream' ) . "</a>(" . esc_html( $all_count ) . ")";

        //Mine link
        $mine_class     = ( $current == 'mine' ? ' class="current"' : '' );
        $mine_url       = add_query_arg( array( 'view' => 'mine', 'status' => false ) );
        $mine_count     = upstream_count_assigned_to( 'tasks' );
        $views['mine']  = "<a href='" . esc_url( $mine_url ) . "' " . esc_attr( $mine_class ) . " >" . __( 'Mine', 'upstream' ) . "</a>(" . esc_html( $mine_count ) . ")";

        // links for other statuses
        $option         = get_option( 'upstream_tasks' );
        $statuses       = isset( $option['statuses'] ) ? $option['statuses'] : '';
        $counts         = self::count_statuses();

        if( $statuses ) {
            // check if user wants to hide completed tasks
            $hide   = get_user_option( 'upstream_completed_tasks', get_current_user_id() );

            foreach ($statuses as $status) {
                if ($hide === 'on' && self::hide_completed($status['name'])) {
                    continue;
                }

                $stati  = strtolower( $status['name'] );
                $class  = ( $current == $stati ? ' class="current"' : '' );
                $url    = add_query_arg( array( 'status' => $stati, 'view' => false, 'paged' => false ) );
                $count  = isset( $counts[$status['name']] ) ? $counts[$status['name']] : 0;
                $views[$stati] = "<a href='" . esc_url( $url ) . "' " . esc_attr( $class ) . " >" . esc_html( $status['name'] ) . "</a>(" . esc_html( $count ) . ")";
            }
        }

        return $views;

    }

    function extra_tablenav( $which ) {

        if( $which != 'top' )
            return;
        ?>

        <div class="alignleft actions">

        <?php
        if ( ! is_singular() ) {

        $projects = $this->get_projects_unique();
        if ( ! empty( $projects ) ) { ?>

            <select name='project' id='project' class='postform'>
                <option value=''><?php printf( __( 'Show all %s', 'upstream' ), 'projects' ) ?></option>
                <?php foreach ( $projects as $project_id => $title ) { ?>
                    <option value="<?php echo $project_id ?>" <?php isset( $_GET['project'] ) ? selected( $_GET['project'], $project_id ) : ''; ?>><?php echo esc_html( $title ) ?></option>
                <?php } ?>
            </select>

        <?php }

        $assigned_to = $this->get_assigned_to_unique();
        if ( ! empty( $assigned_to ) ) { ?>

            <select name='assigned_to' id='assigned_to' class='postform'>
                <option value=''><?php printf( __( 'Show all %s', 'upstream' ), 'users' ) ?></option>
                <?php foreach ( $assigned_to as $user_id => $user ) { ?>
                    <option value="<?php echo $user_id ?>" <?php isset( $_GET['assigned_to'] ) ? selected( $_GET['assigned_to'], $user_id ) : ''; ?>><?php echo esc_html( $user ) ?></option>
                <?php } ?>
            </select>

        <?php }

        $status = $this->get_status_unique();
        if ( ! empty( $status ) ) { ?>

            <select name='status' id='status' class='postform'>
                <option value=''><?php printf( __( 'Show all %s', 'upstream' ), 'statuses' ) ?></option>
                <?php foreach ( $status as $stati ) { ?>
                    <option value="<?php echo strtolower( $stati ) ?>" <?php isset( $_GET['status'] ) ? selected( $_GET['status'], $stati ) : ''; ?>><?php echo esc_html( $stati ) ?></option>
                <?php } ?>
            </select>

        <?php }

            submit_button( __( 'Filter' ), 'button', 'filter', false );
        }

        ?>
        </div>
        <?php

    }


    private function get_projects_unique() {
        $tasks = self::get_tasks();
        if ( empty( $tasks ) )
            return;

        $items = wp_list_pluck( $tasks, 'project', 'project_id' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        return $items;
    }

    private function get_assigned_to_unique() {
        $tasks = self::get_tasks();
        if ( empty( $tasks ) )
            return;

        $items = wp_list_pluck( $tasks, 'assigned_to' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        $new_items = array();
        foreach ($items as $k => $v) {
            $new_items[$v] = upstream_users_name( $v );
        }
        return $new_items;
    }

    private function get_status_unique() {
        $tasks = self::get_tasks();
        if ( empty( $tasks ) )
            return;

        $items = wp_list_pluck( $tasks, 'status' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        return $items;
    }

    /**
     * Returns the count of each status
     *
     * @return array
     */
    public static function count_statuses() {

        $tasks = self::get_tasks();
        if( ! $tasks )
            return null;

        $statuses = wp_list_pluck( $tasks, 'status' );
        $statuses = array_count_values( $statuses );

        // double check so we have no empty keys or values
        foreach ($statuses as $key => $value) {
            if( empty( $key ) || is_null( $key ) || empty( $value ) || is_null( $value ) )
                unset($statuses[$key]);
            if( empty( $value ) || is_null( $value ) )
                unset($statuses[$key]);
        }

        return $statuses;
    }


    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {

            case 'title':

                $output = '<a class="row-title" href="' . get_edit_post_link( $item['project_id'] ). '">' . esc_html( $item['title'] ) . '</a>';

                return $output;

            case 'project':

                $output = '<a href="' . get_edit_post_link( $item['project_id'] ). '">' . esc_html( $item['project'] ) . '</a>';
                $output .= '<br>' . esc_html( upstream_project_progress( $item['project_id'] ) ) . '% ' . __( 'Complete', 'upstream' );

                return $output;

            case 'milestone':

                $milestone  = upstream_project_milestone_by_id( $item['project_id'], $item['milestone'] );
                $progress   = $milestone['progress'] ? $milestone['progress'] : '0';

                $output     = $milestone ? esc_html( $milestone['milestone'] ) . '<br>' . esc_html( $progress ) . '% ' . __( 'Complete', 'upstream' ) : '';

                return $output;

            case 'assigned_to':

                $assigned_to = isset( $item['assigned_to'] ) && $item['assigned_to'] ? $item['assigned_to'] : '';
                $user = upstream_user_data( $assigned_to, true );
                $output = $user['full_name'];

                if ( $assigned_to == get_current_user_id() ){
                    $output = '<span class="mine">' . esc_html( $output ) . '</span>';
                }
                return $output;

            case 'end_date':
                $output = '<span class="end-date">' . esc_html( upstream_format_date( $item['end_date'] ) ) . '</span>';
                return $output;

            case 'status':

                if( ! $item['status'] )
                    return null;

                $color  = upstream_project_task_status_color( $item['project_id'], $item['id'] );
                $output = '<span style="border-color:' . esc_attr( $color ) . '" class="status ' . esc_attr( strtolower( $item['status'] ) ) . '"><span class="count" style="background-color:' . esc_attr( $color ) . '">1</span>' . esc_html( $item['status'] ) . '</span>';

                return $output;

            case 'progress':

                $task       = upstream_project_item_by_id( $item['project_id'], $item['id'] );
                $progress   = isset( $task['progress'] ) && $task['progress'] ? $task['progress'] : '0';
                $output     = esc_html( $progress ) . '%';

                return $output ;

            default:
                //return print_r( $item, true ); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            'title'         => array( 'title', true ),
            // 'project'       => array( 'project', false ),
            // 'milestone'     => array( 'milestone', false ),
            'assigned_to'   => array( 'assigned_to', false ),
            'end_date'      => array( 'end_date', false ),
            'status'        => array( 'status', false ),
            'progress'      => array( 'progress', false ),
        );

        return $sortable_columns;
    }

    /**
     * Retrieve all tasks from all projects.
     *
     * @return array
     */
    public static function get_tasks() {

        $args = array(
            'post_type'     => 'project',
            'post_status'   => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key'     => '_upstream_project_tasks',
                    'compare' => 'EXISTS',
                )
            ),
        );

        // The Query
        $the_query = new WP_Query( $args );

        // The Loop
        if ( ! $the_query->have_posts() )
            return;

        $tasks = array();
        while ( $the_query->have_posts() ) : $the_query->the_post();

            $post_id    = get_the_ID();
            $meta       = get_post_meta( $post_id, '_upstream_project_tasks', true );
            $owner      = get_post_meta( $post_id, '_upstream_project_owner', true );

            if( $meta ) :
                foreach ( $meta as $meta_val => $task) {

                    // set up the data for each column
                    $task['title']          = isset( $task['title'] ) ? $task['title'] : __( '(no title)', 'upstream' );
                    $task['project']        = get_the_title( $post_id );
                    $task['owner']          = $owner;
                    $task['assigned_to']    = isset( $task['assigned_to'] ) ? $task['assigned_to'] : 0;
                    $task['milestone']      = isset( $task['milestone'] ) ? $task['milestone'] : '';
                    $task['start_date']     = isset( $task['start_date'] ) ? $task['start_date'] : '';
                    $task['end_date']       = isset( $task['end_date'] ) ? $task['end_date'] : '';
                    $task['status']         = isset( $task['status'] ) ? $task['status'] : '';
                    $task['progress']       = isset( $task['progress'] ) ? $task['progress'] : '';
                    $task['notes']          = isset( $task['notes'] ) ? $task['notes'] : '';
                    $task['project_id']     = $post_id; // add the post id to each task

                    // check if we can add the task to the list
                    $user_id    = get_current_user_id();
                    // $option     = get_option( 'upstream_tasks' );
                    // $hide       = $option['hide_closed'];

                    // // check if user wants to hide completed tasks
                    // if ( $hide == 'on' && self::hide_completed( $task['status'] ) )
                    //     continue;


                    $tasks[] = $task;

                }

            endif;

        endwhile;

        return $tasks;

    }

    /**
     *
     *
     * @return null|int
     */
    public static function hide_completed( $status ) {

        $option     = get_option( 'upstream_tasks' );
        $statuses   = isset( $option['statuses'] ) ? $option['statuses'] : '';

        if( ! $statuses )
            return false;

        $types = wp_list_pluck( $statuses, 'type', 'name' );

        foreach ( $types as $key => $value ) {
            if( $key == $status && $value == 'open' )
                return false;
        }

        return true;

    }
    /**
     * Output tasks
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function output_tasks( $per_page = 10, $page_number = 1 ) {

        // get the tasks
        $tasks = self::get_tasks();

        // sort & filter the tasks
        $tasks = self::sort_filter( $tasks );

        // does the paging
        if( ! $tasks ) {
            $output = 0;
        } else {
            $output = array_slice( $tasks, ( $page_number - 1 ) * $per_page, $per_page );
        }

        return $output;

    }

    public static function sort_filter( $tasks = array() ) {

        // filtering
        $the_tasks = $tasks; // store the tasks array
        $status = ( isset( $_REQUEST['status']) ? $_REQUEST['status'] : 'all' );
        if( $status != 'all' ) {
            if ( ! empty( $the_tasks ) ) {
                $tasks = array(); // reset the tasks array
                foreach( $the_tasks as $key => $task ) {
                    $stat = isset( $task['status'] ) ? $task['status'] : null;
                    if( strtolower( $stat ) == $status )
                        $tasks[] = $task;
                }
            }
        }
        $mine = ( isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'all' );
        if( $mine == 'mine' ) {
            $user_id = get_current_user_id();
            if ( ! empty( $tasks ) ) {
                foreach( $tasks as $key => $task ) {
                    $assigned_to = isset( $task['assigned_to'] ) ? $task['assigned_to'] : null;
                    if( $assigned_to != $user_id )
                        unset( $tasks[$key] );
                }
            }
        }

        $project = ( isset( $_REQUEST['project'] ) ? $_REQUEST['project'] : '' );
        if ( ! empty( $tasks ) && ! empty( $project ) ) {
            foreach( $tasks as $key => $task ) {
                if( $task['project_id'] != $project )
                    unset( $tasks[$key] );
            }
        }
        $assigned_to = ( isset( $_REQUEST['assigned_to'] ) ? $_REQUEST['assigned_to'] : '' );
        if ( ! empty( $tasks ) && ! empty( $assigned_to ) ) {
            foreach( $tasks as $key => $task ) {
                $assigned = isset( $task['assigned_to'] ) ? $task['assigned_to'] : null;
                if( $assigned != $assigned_to )
                    unset( $tasks[$key] );
            }
        }

        // sorting the tasks
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            if( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'asc' ) {
                $tmp = Array();
                foreach($tasks as &$ma)
                    $tmp[] = &$ma[esc_html($_REQUEST['orderby'])];
                array_multisort($tmp, SORT_ASC, $tasks);
            }
            if( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'desc' ) {
                $tmp = Array();
                foreach($tasks as &$ma)
                    $tmp[] = &$ma[esc_html($_REQUEST['orderby'])];
                array_multisort($tmp, SORT_DESC, $tasks);
            }
        }

        return $tasks;

    }



    /** Text displayed when no customer data is available */
    public function no_items() {
        printf( __( 'No %s avaliable.', 'upstream' ), strtolower( $this->task_label_plural ) );
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $per_page     = $this->get_items_per_page( 'tasks_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = upstream_count_total( 'tasks' );

        $this->set_pagination_args( array(
            'total_items' => $total_items, //We have to calculate the total number of items
            'per_page'    => $per_page //We have to determine how many items to show on a page
        ) );

        $this->items = self::output_tasks( $per_page, $current_page );
    }

    protected function get_table_classes() {
        return array( 'widefat', 'striped', $this->_args['plural'] );
    }
}


class Upstream_Admin_Tasks_Page {

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $tasks_obj;

    // class constructor
    public function __construct() {
        add_filter( 'set-screen-option', array( $this, 'set_screen' ), 10, 3 );
        add_action( 'admin_menu', array( $this, 'plugin_menu' ) );
    }

    /** Singleton instance */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Screen options
     */
    public function screen_option() {

        $option = 'per_page';
        $args   = array(
            'label'   => upstream_task_label_plural(),
            'default' => 10,
            'option'  => 'tasks_per_page'
        );

        add_screen_option( $option, $args );

        $screen = get_current_screen();
        if( $screen->id == 'project_page_tasks' )
            $this->tasks_obj = new Upstream_Task_List();
    }


    public function plugin_menu() {

        $hook = add_submenu_page(
            'edit.php?post_type=project',
            upstream_task_label_plural(),
            upstream_task_label_plural(),
            'edit_projects',
            'tasks',
            array( $this, 'plugin_settings_page' )
        );

        add_action( "load-$hook", array( $this, 'screen_option' ) );

        global $submenu;

        $count = upstream_count_assigned_to_open( 'tasks' );
        $proj = isset( $submenu['edit.php?post_type=project'] ) ? $submenu['edit.php?post_type=project'] : '';
        if( $proj ) {
            foreach ($proj as $key => $value) {
                if( in_array( 'tasks', $value ) ) {
                    $i = (int) $key;
                    $submenu['edit.php?post_type=project'][$i][0] .= $count ? " <span class='update-plugins count-1'><span class='update-count'>" . esc_html( $count ) ."</span></span>" : '';
                }
            }
        }

    }


    /**
     * Plugin settings page
     */
    public function plugin_settings_page() {

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( upstream_task_label_plural() ); ?></h1>

                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">
                        <?php $this->tasks_obj->views(); ?>
                        <?php //$this->tasks_obj->display_tablenav( 'top' ); ?>
                        <?php //$this->tasks_obj->search_box('search', 'search_id'); ?>
                            <form method="post">
                                <?php
                                $this->tasks_obj->prepare_items();
                                $this->tasks_obj->display(); ?>
                            </form>
                        </div>
                    </div>

                <br class="clear">
        </div>
    <?php
    }

}

add_action( 'plugins_loaded', function () {
    Upstream_Admin_Tasks_Page::get_instance();
} );
