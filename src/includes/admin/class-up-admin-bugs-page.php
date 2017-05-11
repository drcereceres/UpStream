<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Upstream_Bug_List extends WP_List_Table {

    private $columns = array();

    public $bug_label = '';
    public $bug_label_plural = '';

    /** Class constructor */
    public function __construct() {

        $this->bug_label         = upstream_bug_label();
        $this->bug_label_plural  = upstream_bug_label_plural();

        parent::__construct( array(
            'singular' => $this->bug_label,
            'plural'   => $this->bug_label_plural,
            'ajax'     => false //does this table support ajax?
        ) );

    }

    public function get_columns() {
        return $columns = apply_filters( 'upstream_admin_bug_page_columns', array(
            'title'         => $this->bug_label,
            'project'       => upstream_project_label(),
            'assigned_to'   => __( 'Assigned To', 'upstream' ),
            'due_date'      => __( 'Due Date', 'upstream' ),
            'status'        => __( 'Status', 'upstream' ),
            'severity'      => __( 'Severity', 'upstream' ),
        ) );
    }

    /*
     * Displays the filtering links above the table
     */
    public function get_views(){

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
        $all_count      = upstream_count_total('bugs');
        $views['all']   = "<a href='" . esc_url( $all_url ) . "' {$all_class} >" . __( 'All', 'upstream' ) . "</a>({$all_count})";

        //Mine link
        $mine_class     = ( $current == 'mine' ? ' class="current"' : '' );
        $mine_url       = add_query_arg( array( 'view' => 'mine', 'status' => false ) );
        $mine_count     = upstream_count_assigned_to('bugs');
        $views['mine']  = "<a href='" . esc_url( $mine_url ) . "' {$mine_class} >" . __( 'Mine', 'upstream' ) . "</a>({$mine_count})";

        // links for other statuses
        $option         = get_option( 'upstream_bugs' );
        $statuses       = isset( $option['statuses'] ) ? $option['statuses'] : '';
        $counts         = self::count_statuses();

        if( $statuses ) {
            // check if user wants to hide completed bugs
            $hide = get_user_option( 'upstream_completed_bugs', get_current_user_id() );

            foreach ($statuses as $status) {
                if ($hide === 'on' && self::hide_completed($status['name'])) {
                    continue;
                }

                $stati  = strtolower( $status['name'] );
                $class  = ( $current == $stati ? ' class="current"' : '' );
                $url    = add_query_arg( array( 'status' => $stati, 'view' => false, 'paged' => false ) );
                $count  = isset( $counts[$status['name']] ) ? $counts[$status['name']] : 0;
                $views[$stati] = "<a href='" . esc_url( $url ) . "' {$class} >{$status['name']}</a>({$count})";
            }
        }

        return $views;

    }

    public function extra_tablenav( $which ) {

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

        $severity = $this->get_severity_unique();
        if ( ! empty( $severity ) ) { ?>

            <select name='severity' id='severity' class='postform'>
                <option value=''><?php printf( __( 'Show all %s', 'upstream' ), 'severities' ) ?></option>
                <?php foreach ( $severity as $severiti ) { ?>
                    <option value="<?php echo strtolower( $severiti ) ?>" <?php isset( $_GET['severity'] ) ? selected( $_GET['severity'], $severiti ) : ''; ?>><?php echo $severiti ?></option>
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
        $bugs = self::get_bugs();
        if ( empty( $bugs ) )
            return;

        $items = wp_list_pluck( $bugs, 'project', 'project_id' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        return $items;
    }

    private function get_assigned_to_unique() {
        $bugs = self::get_bugs();
        if ( empty( $bugs ) )
            return;

        $items = wp_list_pluck( $bugs, 'assigned_to' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        $new_items = array();
        foreach ($items as $k => $v) {
            $new_items[$v] = upstream_users_name( $v );
        }
        return $new_items;
    }

    private function get_status_unique() {
        $bugs = self::get_bugs();
        if ( empty( $bugs ) )
            return;

        $items = wp_list_pluck( $bugs, 'status' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        return $items;
    }

    private function get_severity_unique() {
        $bugs = self::get_bugs();
        if ( empty( $bugs ) )
            return;

        $items = wp_list_pluck( $bugs, 'severity' );
        $items = array_unique( $items );
        $items = array_filter( $items );
        return $items;
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

                $output = '<a class="row-title" href="' . get_edit_post_link( $item['project_id'] ). '">' . $item['title'] . '</a>';

                return $output;

            case 'project':

                $owner = upstream_project_owner_name( $item['project_id'] ) ? '(' . upstream_project_owner_name( $item['project_id'] ) . ')' : '';

                $output = '<a href="' . get_edit_post_link( $item['project_id'] ). '">' . $item['project'] . '</a>';
                $output .= '<br>' . $owner;

                return $output;

            case 'assigned_to':

                $user = upstream_user_data( $item['assigned_to'], true );
                $output = $user['full_name'];

                if ( $item['assigned_to'] == get_current_user_id() ){
                    $output = '<span class="mine">' . esc_html( $output ) . '</span>';
                }
                return $output;

            case 'due_date':
                $output = '<span class="end-date">' . upstream_format_date( $item['due_date'] ) . '</span>';
                return $output;

            case 'status':

                if( ! $item['status'] )
                    return null;

                $colors = upstream_project_bug_statuses_colors( $item['project_id'] );
                $color  = isset( $colors[$item['status']] ) ? $colors[$item['status']] : '#aaaaaa';
                $output = '<span style="border-color:' . esc_attr( $color ) . '" class="status ' . esc_attr( strtolower( $item['status'] ) ) . '"><span class="count" style="background-color:' . esc_attr( $color ) . '">1</span>' . $item['status'] . '</span>';

                return $output;

            case 'severity':

                if( ! $item['severity'] )
                    return null;

                $colors = upstream_project_bug_severity_colors( $item['project_id'] );
                $color  = isset( $colors[$item['severity']] ) ? $colors[$item['severity']] : '#aaaaaa';
                $output = '<span style="border-color:' . esc_attr( $color ) . '" class="status ' . esc_attr( strtolower( $item['severity'] ) ) . '"><span class="count" style="background-color:' . esc_attr( $color ) . '">1</span>' . $item['severity'] . '</span>';

                return $output;

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
            'due_date'      => array( 'due_date', false ),
            'status'        => array( 'status', false ),
            'severity'      => array( 'severity', false ),
        );

        return $sortable_columns;
    }

    /**
     * Retrieve all bugs from all projects.
     *
     * @return array
     */
    public static function get_bugs() {

        $args = array(
            'post_type'     => 'project',
            'post_status'   => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key'     => '_upstream_project_bugs',
                    'compare' => 'EXISTS',
                )
            ),
        );

        // The Query
        $the_query = new WP_Query( $args );

        // The Loop
        if ( ! $the_query->have_posts() )
            return;

        $bugs = array();
        while ( $the_query->have_posts() ) : $the_query->the_post();

            $post_id = get_the_ID();

            if (upstream_are_bugs_disabled($post_id)) {
                continue;
            }

            $meta = get_post_meta( $post_id, '_upstream_project_bugs', true );
            $owner = get_post_meta( $post_id, '_upstream_project_owner', true );

            if( $meta ) :
                foreach ( $meta as $meta_val => $bug) {
                    // set up the data for each column
                    $bug['title']          = isset( $bug['title'] ) ? $bug['title'] : __( '(no title)', 'upstream' );
                    $bug['project']        = get_the_title( $post_id );
                    $bug['owner']          = $owner;
                    $bug['assigned_to']    = isset( $bug['assigned_to'] ) ? $bug['assigned_to'] : 0;
                    $bug['due_date']       = isset( $bug['due_date'] ) ? $bug['due_date'] : '';
                    $bug['status']         = isset( $bug['status'] ) ? $bug['status'] : '';
                    $bug['severity']       = isset( $bug['severity'] ) ? $bug['severity'] : '';
                    $bug['description']    = isset( $bug['description'] ) ? $bug['description'] : '';
                    $bug['project_id']     = $post_id; // add the post id to each bug

                    // check if we can add the bug to the list
                    $user_id    = get_current_user_id();
                    // $option     = get_option( 'upstream_bugs' );
                    // $hide       = $option['hide_closed'];

                    // // check if user wants to hide completed bugs
                    // if ( $hide == 'on' && self::hide_completed( $bug['status'] ) )
                    //     continue;

                    $bugs[] = $bug;

                }

            endif;

        endwhile;

        return $bugs;

    }

    /**
     *
     *
     * @return null|int
     */
    public static function hide_completed( $status ) {

        $option     = get_option( 'upstream_bugs' );
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
     * Output bugs
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function output_bugs( $per_page = 10, $page_number = 1 ) {

        // get the bugs
        $bugs = self::get_bugs();

        // sort & filter the bugs
        $bugs = self::sort_filter( $bugs );

        // does the paging
        if( ! $bugs ) {
            $output = 0;
        } else {
            $output = array_slice( $bugs, ( $page_number - 1 ) * $per_page, $per_page );
        }

        return $output;

    }

    public static function sort_filter( $bugs = array() ) {

        // filtering
        $the_bugs = $bugs; // store the bugs array
        $status = ( isset( $_REQUEST['status']) ? $_REQUEST['status'] : 'all' );
        if( $status != 'all' ) {
            if ( ! empty( $the_bugs ) ) {
                $bugs = array(); // reset the bugs array
                foreach( $the_bugs as $key => $bug ) {
                    if( strtolower( $bug['status'] ) == $status )
                        $bugs[] = $bug;
                }
            }
        }
        $severity = ( isset( $_REQUEST['severity']) ? $_REQUEST['severity'] : 'all' );
        if( $severity != 'all' ) {
            if ( ! empty( $the_bugs ) ) {
                // $bugs = array(); // reset the bugs array
                foreach( $the_bugs as $key => $bug ) {
                    if( strtolower( $bug['severity'] ) == $severity )
                        $bugs[] = $bug;
                }
            }
        }
        $mine = ( isset( $_REQUEST['view'] ) ? $_REQUEST['view'] : 'all' );
        if( $mine == 'mine' ) {
            $user_id = get_current_user_id();
            if ( ! empty( $bugs ) ) {
                foreach( $bugs as $key => $bug ) {
                    if( $bug['assigned_to'] != $user_id )
                        unset( $bugs[$key] );
                }
            }
        }

        $project = ( isset( $_REQUEST['project'] ) ? $_REQUEST['project'] : '' );
        if ( ! empty( $bugs ) && ! empty( $project ) ) {
            foreach( $bugs as $key => $bug ) {
                if( $bug['project_id'] != $project )
                    unset( $bugs[$key] );
            }
        }
        $assigned_to = ( isset( $_REQUEST['assigned_to'] ) ? $_REQUEST['assigned_to'] : '' );
        if ( ! empty( $bugs ) && ! empty( $assigned_to ) ) {
            foreach( $bugs as $key => $bug ) {
                if( $bug['assigned_to'] != $assigned_to )
                    unset( $bugs[$key] );
            }
        }

        // sorting the bugs
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            if( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'asc' ) {
                $tmp = Array();
                foreach($bugs as &$ma)
                    $tmp[] = &$ma[esc_html($_REQUEST['orderby'])];
                array_multisort($tmp, SORT_ASC, $bugs);
            }
            if( ! empty( $_REQUEST['order'] ) && $_REQUEST['order'] == 'desc' ) {
                $tmp = Array();
                foreach($bugs as &$ma)
                    $tmp[] = &$ma[esc_html($_REQUEST['orderby'])];
                array_multisort($tmp, SORT_DESC, $bugs);
            }
        }

        return $bugs;

    }



    /**
     * Returns the count of each status
     *
     * @return array
     */
    public static function count_statuses() {

        $bugs = self::get_bugs();
        if( ! $bugs )
            return null;

        $statuses = wp_list_pluck( $bugs, 'status' );
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



    /** Text displayed when no customer data is available */
    public function no_items() {
        printf( __( 'No %s avaliable.', 'upstream' ), strtolower( $this->bug_label_plural ) );
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        $this->_column_headers = $this->get_column_info();

        $per_page     = $this->get_items_per_page( 'bugs_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = upstream_count_total( 'bugs' );

        $this->set_pagination_args( array(
            'total_items' => $total_items, //We have to calculate the total number of items
            'per_page'    => $per_page //We have to determine how many items to show on a page
        ) );

        $this->items = self::output_bugs( $per_page, $current_page );
    }

    protected function get_table_classes() {
        return array( 'widefat', 'striped', $this->_args['plural'] );
    }
}


class Upstream_Admin_Bugs_Page {

    // class instance
    static $instance;

    // customer WP_List_Table object
    public $bugs_obj;

    // class constructor`
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

    public function set_screen( $status, $option, $value ) {
        if ( 'upstream_completed_bugs' == $option ) {
            $value = $_POST['upstream_hide_completed'];
        }
        return $value;
    }


    /**
     * Screen options
     */
    public function screen_option() {

        $option = 'per_page';
        $args   = array(
            'label'   => upstream_bug_label_plural(),
            'default' => 10,
            'option'  => 'bugs_per_page'
        );

        add_screen_option( $option, $args );

        $screen = get_current_screen();
        if( $screen->id == 'project_page_bugs' )
            $this->bugs_obj = new Upstream_Bug_List();
    }


    public function plugin_menu() {

        $hook = add_submenu_page(
            'edit.php?post_type=project',
            upstream_bug_label_plural(),
            upstream_bug_label_plural(),
            'edit_projects',
            'bugs',
            array( $this, 'plugin_settings_page' )
        );

        add_action( "load-$hook", array( $this, 'screen_option' ) );

        global $submenu;

        $count = upstream_count_assigned_to_open( 'bugs' );
        $proj = isset( $submenu['edit.php?post_type=project'] ) ? $submenu['edit.php?post_type=project'] : '';
        if( $proj ) {
            foreach ($proj as $key => $value) {
                if( in_array( 'bugs', $value ) ) {
                    $i = (int) $key;
                    $submenu['edit.php?post_type=project'][$i][0] .= $count ? " <span class='update-plugins count-1'><span class='update-count'>$count</span></span>" : '';
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
            <h1><?php echo upstream_bug_label_plural(); ?></h1>

                    <div id="post-body-content">

                        <div class="meta-box-sortables ui-sortable">
                        <?php $this->bugs_obj->views(); ?>
                        <?php //$this->bugs_obj->display_tablenav( 'top' ); ?>
                        <?php //$this->bugs_obj->search_box('search', 'search_id'); ?>
                            <form method="post">
                                <?php
                                $this->bugs_obj->prepare_items();
                                $this->bugs_obj->display(); ?>
                            </form>
                        </div>
                    </div>

                <br class="clear">
        </div>
    <?php
    }

}

add_action( 'plugins_loaded', function () {
    if( upstream_disable_bugs() )
        return;
    Upstream_Admin_Bugs_Page::get_instance();
} );
