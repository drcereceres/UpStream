<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/* ======================================================================================
                                        METABOX FIELD VALIDATION
   ====================================================================================== */
/*
 * CMB2 js validation for "required" fields
 * Uses js to validate CMB2 fields that have the 'data-validation' attribute set to 'required'
 */
/**
 * Documentation in the wiki:
 * @link https://github.com/WebDevStudios/CMB2/wiki/Plugin-code-to-add-JS-validation-of-%22required%22-fields
 */
function upstream_form_do_js_validation( $post_id, $cmb ) {

    static $added = false;
    // Only add this to the page once (not for every metabox)
    if ( $added ) {
        return;
    }
    $added = true;
    ?>

    <script type="text/javascript">

        jQuery(document).ready(function($) {

            $form = $( document.getElementById( 'post' ) );
            $htmlbody = $( 'html, body' );
            $toValidate = $( '[data-validation]' );

            if ( ! $toValidate.length ) {
                return;
            }

            function checkValidation( evt ) {

                var labels = [];
                var $first_error_row = null;
                var $row = null;

                function add_required( $row, $this ) {

                    setTimeout(function() {
                        $row.css({
                            'box-shadow': '0 0 2px #dc3232',
                            'border-right' : '4px solid #dc3232'
                        });
                        $this.css({ 'border-color': '#dc3232' });
                    }, 500);

                    $first_error_row = $first_error_row ? $first_error_row : $this;

                    // if it has been deleted dynamically
                    if( $(document).find($first_error_row).length == 0) {
                        $first_error_row = null;
                    }

                }

                function remove_required( $row, $this ) {
                    $row.css({ background: '' });
                }

                $toValidate.each( function() {

                    var $this = $(this);
                    var val = $this.val();

                    if( $this.parents( '.cmb-repeatable-grouping' ) ){
                        $item   = $this.parents( '.cmb-repeatable-grouping' );
                        $row    = $item.find( '.cmb-group-title' );

                        if( $item.is(":hidden") ) {
                            return true;
                        }
                    }

                    if ( $this.is( '[type="button"]' ) || $this.is( '.cmb2-upload-file-id' ) ) {
                        return true;
                    }

                    if ( 'required' === $this.data( 'validation' ) ) {

                        if ( $row.is( '.cmb-type-file-list' ) ) {
                            var has_LIs = $row.find( 'ul.cmb-attach-list li' ).length > 0;
                            if ( ! has_LIs ) {
                                add_required( $row, $this );
                            } else {
                                remove_required( $row, $this );
                            }
                        } else {
                            if ( ! val ) {
                                add_required( $row, $this );
                            } else {
                                remove_required( $row, $this );
                            }
                        }
                    }

                });

                if ( $first_error_row ) {
                    evt.preventDefault();

                    $( '#major-publishing-actions .notice' ).remove();
                    $( '#major-publishing-actions' ).append( $('<div class="notice notice-error"><?php _e( 'Missing some required fields', 'upstream' ) ?></div>' ).hide().fadeIn(500) );

                    $htmlbody.delay(500).animate({
                        scrollTop: ( $first_error_row.offset().top - 100 )
                    }, 500);
                } else {
                    $form.find('input, textarea, button, select').prop({ 'disabled': false, 'readonly': false });
                }

            }

            $form.on( 'submit', checkValidation );

        });
    </script>

    <?php
}
add_action( 'cmb2_after_form', 'upstream_form_do_js_validation', 10, 2 );

/* ======================================================================================
                                        OVERVIEW
   ====================================================================================== */

/**
 * Returns data for the overview section.
 *
 * @return
 */
function upstream_output_overview_counts( $field_args, $field ) {

    $post_id = $field->object_id ? $field->object_id : isset( $_GET['post'] ) ? $_GET['post'] : 'n/a';
    $type = str_replace("_upstream_project_", "", $field_args['id'] );
    $total_open = upstream_count_total_open( $type, $post_id );
    $mine_open  = upstream_count_assigned_to_open( $type, $post_id );
    $mine_class = $mine_open > 0 && $type != 'milestones' ? 'mine' : '';
    ?>
    <div class="counts <?php echo esc_attr( $type ); ?>">

        <h4><span class="count open total"><?php echo upstream_count_total_open( $type, $post_id ) ?></span> <?php _e( 'Open', 'upstream' ) ?></h4>
        <h4><span class="count open <?php echo esc_attr( $mine_class ); ?>"><?php echo (int) $mine_open ?></span> <?php _e( 'Mine', 'upstream' ) ?></h4>

    </div>

    <?php

}
/* ======================================================================================
                                        ACTIVITY
   ====================================================================================== */

/**
 * Returns the buttons for the activity section
 *
 * @return
 */
function upstream_activity_buttons( $field_args, $field ) {

    // active class
    $class  = ' button-primary';
    $_10    = '';
    $_20    = '';
    $_all   = '';

    if( ! isset( $_GET['activity_items'] ) || ( isset( $_GET['activity_items'] ) && $_GET['activity_items'] == '10' ) )
        $_10 = $class;
    if( isset( $_GET['activity_items'] ) && $_GET['activity_items'] == '20' )
        $_20 = $class;
    if( isset( $_GET['activity_items'] ) && $_GET['activity_items'] == 'all' )
        $_all = $class;

    $edit_buttons = '<div class="button-wrap">';
    $edit_buttons .= '<a class="button button-small' . esc_attr( $_10 ) . '" href="' . esc_url( add_query_arg( 'activity_items', '10' ) ) . '" >' . __( 'Last 10', 'upstream' ) . '</a> ';
    $edit_buttons .= '<a class="button button-small' . esc_attr( $_20 ) . '" href="' . esc_url( add_query_arg( 'activity_items', '20' ) ) . '" >' . __( 'Last 20', 'upstream' ) . '</a> ';
    $edit_buttons .= '<a class="button button-small' . esc_attr( $_all ) . '" href="' . esc_url( add_query_arg( 'activity_items', 'all' ) ) . '" >' . __( 'View All', 'upstream' ) . '</a> ';
    $edit_buttons .= '</div>';

    return $edit_buttons;
}

/**
 * Returns data for the activity section.
 *
 * @return
 */
function upstream_output_activity( $field_args, $field ) {
    $activity   = new UpStream_Project_Activity();
    $data       = $activity->get_activity( $field->object_id );
    return $data;
}

/* ======================================================================================
                                        MILESTONES
   ====================================================================================== */
/**
 * Returns the milestone types as set in the options.
 * Used in the Type dropdown within a milestone.
 *
 * @return
 */
function upstream_admin_get_options_milestones() {
    $option = get_option( 'upstream_milestones' );
    $milestones = isset( $option['milestones'] ) ? $option['milestones'] : '';
    $array = array();
    if( $milestones ) {
        foreach ($milestones as $milestone) {
            $array[$milestone['title']] = $milestone['title'];
        }
    }
    return $array;
}
/**
 * Outputs some hidden data in the metabox so we can use it dynamically
 *
 * @return
 */
function upstream_admin_output_milestone_hidden_data( $field_args, $field ) {
    $option     = get_option( 'upstream_milestones' );
    $milestones = isset( $option['milestones'] ) ? $option['milestones'] : '';

    if( $milestones ) {

        // get the current saved milestones
        $saved = get_post_meta( $field->object_id, '_upstream_project_milestones', true );
        if( ! $saved ) {
            $progress = '0';
        } else {
            $progress = wp_list_pluck( $saved, 'progress', 'milestone' );
        }
        echo '<ul class="hidden milestones">';
        foreach ($milestones as $milestone) {
            echo '<li>
                <span class="title">' . esc_html( $milestone['title'] ) . '</span>
                <span class="color">' . esc_html( $milestone['color'] ) . '</span>';
                if( isset( $progress[$milestone['title']] ) ) { // if we have progress
                    echo '<span class="m-progress">' . $progress[$milestone['title']] . '</span>';
                }
            echo '</li>';
        }
        echo '</ul>';
    }
}
/**
 * Returns the current saved milestones.
 * For use in dropdowns.
 */
function upstream_admin_get_project_milestones( $field ) {

    // get the current saved milestones
    $milestones = get_post_meta( $field->object_id, '_upstream_project_milestones', true );
    // if we have a milestone
    if( $milestones ) {
        $array = array();
        foreach ($milestones as $milestone) {
            if( isset( $milestone['milestone'] ) && isset( $milestone['id'] ) ) {
                $array[$milestone['id']] = $milestone['milestone'];
            }
        }
        return $array;
    }

    return null;

}

/* ======================================================================================
                                        TASKS
   ====================================================================================== */
/**
 * Returns the task status names as set in the options.
 * Used in the Status dropdown within a task.
 *
 * @return
 */
function upstream_admin_get_task_statuses() {
    $option = get_option( 'upstream_tasks' );
    $statuses = isset( $option['statuses'] ) ? $option['statuses'] : '';
    $array = array();
    if( $statuses ) {
        foreach ($statuses as $status) {
            $array[$status['name']] = $status['name'];
        }
    }
    return $array;
}
/**
 * Outputs some hidden data so we can use it dynamically
 *
 * @return
 */
function upstream_admin_output_task_hidden_data() {
    $option     = get_option( 'upstream_tasks' );
    $statuses   = isset( $option['statuses'] ) ? $option['statuses'] : '';
    if( $statuses ) {
        echo '<ul class="hidden statuses">';
        foreach ($statuses as $status) {
            echo '<li>
                <span class="status">' . esc_html( $status['name'] ) . '</span>
                <span class="color">' . esc_html( $status['color'] ) . '</span>
                </li>';
        }
        echo '</ul>';
    }
}


/* ======================================================================================
                                        BUGS
   ====================================================================================== */
/**
 * Returns the bug status names as set in the options.
 * Used in the Status dropdown within a bug.
 *
 * @return
 */
function upstream_admin_get_bug_statuses() {
    $option = get_option( 'upstream_bugs' );
    $statuses = isset( $option['statuses'] ) ? $option['statuses'] : '';
    $array = array();
    if( $statuses ) {
        foreach ($statuses as $status) {
            $array[$status['name']] = $status['name'];
        }
    }
    return $array;
}
/**
 * Returns the bug severity names as set in the options.
 * Used in the Severity dropdown within a bug.
 *
 * @return
 */
function upstream_admin_get_bug_severities() {
    $option = get_option( 'upstream_bugs' );
    $severities = isset( $option['severities'] ) ? $option['severities'] : '';
    $array = array();
    if( $severities ) {
        foreach ($severities as $severity) {
            $array[$severity['name']] = $severity['name'];
        }
    }
    return $array;
}
/**
 * Outputs some hidden data in the metabox so we can use it dynamically
 *
 * @return
 */
function upstream_admin_output_bug_hidden_data() {
    $option     = get_option( 'upstream_bugs' );
    $statuses   = isset( $option['statuses'] ) ? $option['statuses'] : '';
    $severities = isset( $option['severities'] ) ? $option['severities'] : '';
    if( $statuses ) {
        echo '<ul class="hidden statuses">';
        foreach ($statuses as $status) {
            echo '<li>
                <span class="status">' . esc_html( $status['name'] ) . '</span>
                <span class="color">' . esc_html( $status['color'] ) . '</span>
            </li>';
        }
        echo '</ul>';
    }
    if( $severities ) {
        echo '<ul class="hidden severities">';
        foreach ($severities as $severity) {
            echo '<li>
                <span class="severity">' . esc_html( $severity['name'] ) . '</span>
                <span class="color">' . esc_html( $severity['color'] ) . '</span>
            </li>';
        }
        echo '</ul>';
    }
}

/* ======================================================================================
                                        DISCUSSION
   ====================================================================================== */
/**
 * Outputs comments in the admin.
 */
function upstream_admin_display_messages() { ?>

    <ul class="admin-discussion">

    <?php
    $post_id = isset( $_GET['post'] ) ? $_GET['post'] : null;
    if( ! $post_id )
        return;

    $comments = get_post_meta( $post_id, '_upstream_project_discussion', true );
    if( $comments ) {
        $comments = array_reverse( get_post_meta( $post_id, '_upstream_project_discussion', true ) );
        foreach ($comments as $comment) {
            upstream_admin_display_message_item( $post_id, $comment );
        }
    } ?>

    </ul>

    <?php
}

/**
 * Outputs comments in the admin.
 */
function upstream_admin_display_message_item( $post_id, $comment ) {

    if( ! $post_id )
        return;

    $user       = upstream_user_data( $comment['created_by'] );
    $time       = date_i18n( get_option( 'time_format' ), $comment['created_time'] ) . ' ' . upstream_format_date( $comment['created_time'] );
    $time_ago   = sprintf( _x( '%s ago', '%s = human-readable time difference', 'upstream' ), human_time_diff( $comment['created_time'], current_time( 'timestamp', false ) ) );
    $tooltip    = 'data-toggle="tooltip" data-placement="top"';
    ?>

    <li>
        <img width="36" height="36" src="<?php echo $user['avatar']; ?>" />
        <span class="name"><?php echo esc_html( $user['full_name'] ); ?></span>
        <span class="date"><?php echo esc_html( $time_ago ); ?> <small>(<?php echo esc_html( $time ); ?>)</small></span>
        <span class="comment"><?php echo wpautop( $comment['comment'] ); ?></span>
        <a data-id="<?php echo esc_attr( $comment['id'] ); ?>" class="button cmb-remove-group-row alignright" id="delete_message"><?php _e( 'Delete', 'upstream' ) ?></a>
    </li>

    <?php

}


/**
 * Outputs the post new comment button in the admin.
 */
function upstream_admin_discussion_button() {
    echo '<p><button class="button" id="new_message" type="button">' . __( 'New Message', 'upstream ') . '</button></p>';
}

/**
 * AJAX function to post a new comment in the admin.
 */
add_action('wp_ajax_upstream_admin_new_message', 'upstream_admin_new_message');
function upstream_admin_new_message() {

    if( ! wp_verify_nonce( $_POST['upstream_security'], 'ajax_nonce' ) ) {
        die( -1 );
    }

    if( ! upstream_admin_permissions( 'publish_project_discussion' ) ) {
        die( -1 );
    }

    if( isset( $_POST['content'] ) && $_POST['content'] != '' ) {

        $post_id        = (int) $_POST['post_id'];
        $existing       = get_post_meta( $post_id, '_upstream_project_discussion', true );
        $new_comment    = wp_kses_post( $_POST['content'] );
        $data = array(
            'comment'   => $new_comment,
            'is_client' => 0,
        );

        $existing[] = $data;
        $update = update_post_meta( $post_id, '_upstream_project_discussion', $existing );

        // run this to update all missing meta data such as
        // created date, id, project members, progress etc
        $project = new UpStream_Project( $post_id );
        $project->update_project_meta();

        $last       = get_post_meta( $post_id, '_upstream_project_discussion', true );
        $last_key   = key( array_slice( $last, -1, 1, TRUE ) );

        upstream_admin_display_message_item( $post_id, $last[$last_key] );

    }

    exit;

}

/**
 * AJAX function to delete a new comment in the admin.
 */
add_action('wp_ajax_upstream_admin_delete_message', 'upstream_admin_delete_message');
function upstream_admin_delete_message() {

    if( ! wp_verify_nonce( $_POST['upstream_security'], 'ajax_nonce' ) ) {
        die( -1 );
    }

    if( ! upstream_admin_permissions( 'delete_project_discussion' ) ) {
        die( -1 );
    }

    if( isset( $_POST['item_id'] ) && $_POST['item_id'] != '' ) {

        $post_id        = (int) $_POST['post_id'];
        $item_id        = esc_html( $_POST['item_id'] );
        $data           = get_post_meta( $post_id, '_upstream_project_discussion', true );
        $existing       = $data;

        if( $existing ) :
            foreach ($existing as $key => $value) {
                if( $item_id == $value['id'] ) {
                    unset( $data[$key] );
                }
            }
        endif;

        // reset array keys back into sequential order
        $data       = array_values($data);
        $deleted    = update_post_meta( $post_id, '_upstream_project_discussion', $data );

        echo '1';

    }

    exit;

}



/* ======================================================================================
                                        GENERAL
   ====================================================================================== */

/*
 * Adds field attributes, and permissions data (mainly) depending on users capabilities.
 * Used heavily in JS to enable/disable fields, groups and delete buttons.
 * Also used to add Avatars to group items.
 */
function upstream_add_field_attributes( $args, $field ) {

    /*
     * Add the disabled/readonly attributes to the field
     * if the user does not have permission for that field
     */
    if( isset( $args['permissions'] ) ) {
        if( ! upstream_admin_permissions( $args['permissions'] ) ) {
            $field->args['attributes']['disabled'] = 'disabled';
            $field->args['attributes']['readonly'] = 'readonly';
            $field->args['attributes']['data-disabled'] = 'true';
        } else {
            $field->args['attributes']['data-disabled'] = 'false';
        }
    }

    /*
     * Adding/removing attributes for repeatable groups.
     */
    if( isset( $field->group->args['repeatable'] ) && $field->group->args['repeatable'] == '1' ) :

        $i              = filter_var( $field->args['id'], FILTER_SANITIZE_NUMBER_INT );
        $assigned_to    = isset( $field->group->value[$i]['assigned_to'] ) ? $field->group->value[$i]['assigned_to'] : null;
        $created_by     = isset( $field->group->value[$i]['created_by'] ) ? $field->group->value[$i]['created_by'] : null;

        // if the user is assigned to or item is created by
        if( $assigned_to == upstream_current_user_id() || $created_by == upstream_current_user_id() ) {

            // clear the disabled attributes
            unset( $field->args['attributes']['disabled'] );
            unset( $field->args['attributes']['readonly'] );
            $field->args['attributes']['data-disabled'] = 'false';

            // data-owner attribute is used for the delete button
            if( $field->args['_id'] == 'id' ) {
                $field->args['attributes']['data-owner'] = 'true';
            }

        }
        // to ensure admin and managers can delete anything
        if( upstream_admin_permissions() ) {
            $field->args['attributes']['data-owner'] = 'true';
        }

        // add users avatars
        $user_assigned  = upstream_user_data( $assigned_to, true );
        $user_createdby = upstream_user_data( $created_by, true );
        if( $field->args['_id'] == 'id' ) {
            $field->args['attributes']['data-user_assigned']        = $user_assigned['full_name'];
            $field->args['attributes']['data-user_created_by']      = $user_createdby['full_name'];
            $field->args['attributes']['data-avatar_assigned']      = $user_assigned['avatar'];
            $field->args['attributes']['data-avatar_created_by']    = $user_createdby['avatar'];
        }

    endif;

}

/**
 * Check if a group is empty.
 *
 * @return
 */
function upstream_empty_group( $type ) {

    if( isset( $_GET['post_type'] ) && $_GET['post_type'] != 'project' )
        return '';

    $meta = get_post_meta( upstream_post_id(), "_upstream_project_{$type}", true );
    if( $meta == null || empty( $meta ) || empty( $meta[0] ) ) {
        return '1';
    } else {
        return '';
    }
}

/**
 * Returns the project status names as set in the options.
 * Used in the Status dropdown for the project.
 *
 * @return
 */
function upstream_admin_get_project_statuses() {
    $option = get_option( 'upstream_projects' );
    $statuses = isset( $option['statuses'] ) ? $option['statuses'] : '';
    $array = array();
    if( $statuses ) {
        foreach ($statuses as $status) {
            $array[$status['name']] = $status['name'];
        }
    }
    return $array;
}


/**
 * Returns all users with select roles.
 * For use in dropdowns.
 */
function upstream_admin_get_all_project_users() {
    $args = apply_filters( 'upstream_user_roles_for_projects', array(
        'role__in' => array(
            'upstream_manager',
            'upstream_user',
            'administrator',
            'developer'
        ),
    ) );
    $users = get_users( $args );
    //$array = array( '' => __( 'Not Assigned', 'upstream' ) );
    if( $users ) {
        foreach ($users as $user) {
            $array[$user->ID] = $user->first_name . ' ' . $user->last_name;
        }
    }
    return $array;
}

/**
 * Returns array of all clients.
 * For use in dropdowns.
 */
function upstream_admin_get_all_clients( $field ) {
    $args = array(
        'post_type' => 'client',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'no_found_rows' => true, // for performance
    );
    $clients = get_posts( $args );
    $array = array( '' => __( 'Not Assigned', 'upstream' ) );
    if( $clients ) {
        foreach ($clients as $client) {
            $array[$client->ID] = $client->post_title;
        }
    }
    return $array;
}

/**
 * Returns the current saved clients users.
 * For use in dropdowns.
 */
function upstream_admin_get_all_clients_users( $field ) {

    // get the currently selected client id
    $client_id = get_post_meta( $field->object_id, '_upstream_project_client', true );

    // if we have a client
    if( $client_id ) {

        $users = get_post_meta( $client_id, '_upstream_client_users', true );
        $array = array();

        if( $users ) {
            foreach ($users as $user) {

                $output = upstream_users_name( $user['id'], true );

                $array[$user['id']] = $output;
            }
        }
        return $array;
    }

}


/**
 * AJAX function to return all selected clients users.
 * For use in dropdowns.
 */
add_action('wp_ajax_upstream_admin_ajax_get_clients_users', 'upstream_admin_ajax_get_clients_users');
function upstream_admin_ajax_get_clients_users() {

    $client_id = (int) $_POST['client_id'];
    $array = array();

    if( ! $client_id ) {
        wp_send_json_error( array( 'msg' => __( 'No client selected', 'upstream' ) ) );
    }

    $users = get_post_meta( $client_id, '_upstream_client_users', true );

    if( ! $users ) {
        wp_send_json_error( array( 'msg' => __( 'No users found', 'upstream' ) ) );
    }

    $count = 1;
    $output = '';
    foreach ($users as $user) {

        $username = upstream_users_name( $user['id'], true );

        $output .= sprintf( '<li><input type="checkbox" value="%s" id="_upstream_project_client_users%d" name="_upstream_project_client_users[]"  class="cmb2-option"> <label for="_upstream_project_client_users%d">%s</label></li>', $user['id'], $count, $count, $username );

        $count++;
    }

    if( ! empty( $output ) ){
        wp_send_json_success( $output );
    }
    wp_send_json_error();

}
