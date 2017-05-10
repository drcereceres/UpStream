<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/*
 * Data Types: text, name, date, textarea, select, radio, checkbox, id, actions, files, tasks
 * Note that these are not actual field 'types', but rather a way
 * to format the output of data correctly.
 */

/*
 * The frontend table settings for milestones.
 * These settings alter the output for each row and column in the table.
 */
function upstream_milestone_table_settings() {

    /*
     * display | bool | True to show this column in the table
     * type | string | The type of data this column has. This ensures we format the data correctly
     * heading | string | The text to be displayed as the column heading
     * heading_class | string | A custom class for the column heading
     * row_class | string | A custom class for the row
     */
    $settings = apply_filters( 'upstream_milestone_table_settings', array(
        'id' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_by' => array(
            'display'       => false,
            'type'          => 'name',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_time' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'milestone' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => upstream_milestone_label(),
            'heading_class' => '',
            'row_class'     => 'test',
        ),
        'assigned_to' => array(
            'display'       => true,
            'type'          => 'name',
            'heading'       => __( 'Assigned To', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'tasks' => array(
            'display'       => true,
            'type'          => 'tasks',
            'heading'       => upstream_task_label_plural(),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'progress' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Progress', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'start_date' => array(
            'display'       => true,
            'type'          => 'date',
            'heading'       => __( 'Start Date', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'end_date' => array(
            'display'       => true,
            'type'          => 'date',
            'heading'       => __( 'End Date', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'notes' => array(
            'display'       => true,
            'type'          => 'textarea',
            'heading'       => __( 'Notes', 'upstream' ),
            'heading_class' => 'none',
            'row_class'     => '',
        ),
    ));

    return $settings;

}

/*
 * The frontend table settings for tasks.
 * These settings alter the output for each row and column in the table.
 */
function upstream_task_table_settings() {

    /*
     * display | bool | True to show this column in the table
     * type | string | The type of data this column has. This ensures we format the data correctly
     * heading | string | The text to be displayed as the column heading
     * heading_class | string | A custom class for the column heading
     * row_class | string | A custom class for the row
     */
    $settings = apply_filters( 'upstream_task_table_settings', array(
        'id' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_by' => array(
            'display'       => false,
            'type'          => 'name',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_time' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'title' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => upstream_task_label(),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'assigned_to' => array(
            'display'       => true,
            'type'          => 'name',
            'heading'       => __( 'Assigned To', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'status' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Status', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'progress' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Progress', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'milestone' => array(
            'display'       => true,
            'type'          => 'id',
            'heading'       => upstream_milestone_label(),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'start_date' => array(
            'display'       => true,
            'type'          => 'date',
            'heading'       => __( 'Start Date', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'end_date' => array(
            'display'       => true,
            'type'          => 'date',
            'heading'       => __( 'End Date', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'notes' => array(
            'display'       => true,
            'type'          => 'textarea',
            'heading'       => __( 'Notes', 'upstream' ),
            'heading_class' => 'none',
            'row_class'     => '',
        ),
    ));

    return $settings;

}

/*
 * The frontend table settings for bugs.
 * These settings alter the output for each row and column in the table.
 */
function upstream_bug_table_settings() {

    /*
     * display | bool | True to show this column in the table
     * type | string | The type of data this column has. This ensures we format the data correctly
     * heading | string | The text to be displayed as the column heading
     * heading_class | string | A custom class for the column heading
     * row_class | string | A custom class for the row
     */
    $settings = apply_filters( 'upstream_bug_table_settings', array(
        'id' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_by' => array(
            'display'       => false,
            'type'          => 'name',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_time' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'title' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => upstream_bug_label(),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'assigned_to' => array(
            'display'       => true,
            'type'          => 'name',
            'heading'       => __( 'Assigned To', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'severity' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Severity', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'status' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Status', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'due_date' => array(
            'display'       => true,
            'type'          => 'date',
            'heading'       => __( 'Due Date', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'description' => array(
            'display'       => true,
            'type'          => 'textarea',
            'heading'       => __( 'Description', 'upstream' ),
            'heading_class' => 'none',
            'row_class'     => '',
        ),
        'file' => array(
            'display'       => true,
            'type'          => 'file',
            'heading'       => __( 'File', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
    ));

    return $settings;

}


/*
 * The frontend table settings for files.
 * These settings alter the output for each row and column in the table.
 */
function upstream_file_table_settings() {

    /*
     * display | bool | True to show this column in the table
     * type | string | The type of data this column has. This ensures we format the data correctly
     * heading | string | The text to be displayed as the column heading
     * heading_class | string | A custom class for the column heading
     * row_class | string | A custom class for the row
     */
    $settings = apply_filters( 'upstream_file_table_settings', array(
        'id' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_by' => array(
            'display'       => false,
            'type'          => 'name',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'created_time' => array(
            'display'       => false,
            'type'          => 'text',
            'heading'       => '',
            'heading_class' => '',
            'row_class'     => '',
        ),
        'title' => array(
            'display'       => true,
            'type'          => 'text',
            'heading'       => __( 'Title', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'description' => array(
            'display'       => true,
            'type'          => 'textarea',
            'heading'       => __( 'Description', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
        'file' => array(
            'display'       => true,
            'type'          => 'file',
            'heading'       => __( 'File', 'upstream' ),
            'heading_class' => '',
            'row_class'     => '',
        ),
    ));

    return $settings;

}


/*
 * Outputs the table header data for each group,
 * depending on the settings for each group.
 */
function upstream_output_table_header( $table ) {

    $output = null;

    switch ( $table ) {
        case 'milestones':
            $settings   = upstream_milestone_table_settings();
            break;
        case 'tasks':
            $settings   = upstream_task_table_settings();
            break;
        case 'bugs':
            $settings   = upstream_bug_table_settings();
            break;
        case 'files':
            $settings   = upstream_file_table_settings();
            break;
    }

    if( isset( $settings ) ) :

        $output .= '<tr>';
        foreach ($settings as $key => $setting) {

            if( isset( $setting['display'] ) && ! $setting['display'] )
                continue;

            $output .= "<th class='" . esc_attr( $setting['heading_class'] ) . "'>" . esc_html( $setting['heading'] ) . "</th>";

        }
        $output .= '</tr>';

    endif;

    return $output;

}

function upstream_output_table_rows( $id, $table, $filterRowsetByCurrentUser = false ) {
    // Make sure we're dealing with a bool-typed var.
    $filterRowsetByCurrentUser = (bool)$filterRowsetByCurrentUser;

    // Check if we should try to filter data by the current logged in user (comparing with `assigned_to` column).
    if ($filterRowsetByCurrentUser) {
        $currentUserId = (int)get_current_user_id();
        // Check if the user was logged via backend or frontend.
        if ($currentUserId <= 0 && isset($_SESSION['user_id'])) {
            $currentUserId = (int)$_SESSION['user_id'];
        }
    }

    switch ( $table ) {
        case 'milestones':
            $data       = upstream_are_milestones_disabled() ? array() : upstream_project_milestones( $id );
            $settings   = upstream_milestone_table_settings();
            break;
        case 'tasks':
            $data       = upstream_project_tasks( $id );
            $settings   = upstream_task_table_settings();
            $status_c   = upstream_project_task_statuses_colors();
            break;
        case 'bugs':
            $data       = upstream_project_bugs( $id );
            $settings   = upstream_bug_table_settings();
            $status_c   = upstream_project_bug_statuses_colors();
            $severity_c = upstream_project_bug_severity_colors();
            break;
        case 'files':
            $data       = upstream_project_files( $id );
            $settings   = upstream_file_table_settings();
            break;
    }

    if( empty( $data[0] ) )
        return;

    $data = array_reverse( $data );
    $output = null;

    foreach ( $data as $item ) {

        // Check if $item should be skipped if we want to filter data by the current logged in user.
        if ($filterRowsetByCurrentUser && isset($item['assigned_to']) && $currentUserId > 0 && (int)$item['assigned_to'] !== $currentUserId) {
            continue;
        }

        $output .= '<tr>';
        foreach ($settings as $key => $setting) {

            if( isset( $setting['display'] ) && ! $setting['display'] )
                continue;

            if( ! isset( $item[$key] ) )
                $item[$key] = '';

            $order = null;

            // get the raw value before formatting
            // will be used with the frontend edit plugin for getting actual values via JS
            $data_value = maybe_serialize( $item[$key] );

            // if we have a date field, set the data-order attribute to allow proper ordering in the table
            if( $setting['type'] == 'date' ) {
                $order = 'data-order="' . esc_attr( $data_value ) . '"';
            }
            if( $setting['type'] == 'file' ) {
                $data_value = '';
            }

            // now process and format the data for proper output
            $field_data = upstream_format_table_data( $item, $key, $setting );

            if( $key == 'status' ) {
                $color  = isset( $status_c[$field_data] ) ? $status_c[$field_data] : 'transparent';
                $field_data = '<span class="btn btn-xs" style="background: ' . esc_attr( $color ) . '">' . esc_html( $field_data ) . '</span>';
            }

            if( $key == 'severity' ) {
                $color      = isset( $severity_c[$field_data] ) ? $severity_c[$field_data] : 'transparent';
                $field_data = '<span class="btn btn-xs" style="background: ' . esc_attr( $color ) . '">' . esc_html( $field_data ) . '</span>';
            }

            if ($setting['type'] === 'date' && !empty($data_value)) {
                $data_value = upstream_format_date($data_value);
            }

            $output .= '<td data-name="' . esc_attr( $key ) . '" ' . $order . ' data-value="' . esc_attr( $data_value ) . '" class="' . esc_attr( $setting['row_class'] ) . '">' . $field_data . '</td>';

        }
        $output .= '</tr>';

    }

    return $output;

}



/**
 * format the data for output on the frontend tables
 *
 * @param  $item array contains the meta key => value pairs for the item being formatted
 * @param  $key string the column key (such as progress, assigned_to, id, end_date etc )
 * @param  $setting array the table settings for this field
 */
function upstream_format_table_data( $item, $key, $setting ) {

    $field_data = isset( $item[$key] ) ? $item[$key] : null;
    $output     = '';

    // type: name
    if( $setting['type'] == 'name' && ! empty( $field_data ) ) {
        $user = upstream_user_data( $field_data, true );
        $output = $user['full_name'];
    }

    // type: date
    if( $setting['type'] == 'date' && ! empty( $field_data ) ) {
        $output = upstream_format_date( $field_data );
    }

    // type: text
    if( $setting['type'] == 'text' && ! empty( $field_data ) ) {
        $output = esc_html( $field_data );
    }

    // type: radio
    if( $setting['type'] == 'radio' && ! empty( $field_data ) ) {
        $output = esc_html( $field_data );
    }

    // type: checkbox
    if( $setting['type'] == 'checkbox' && ! empty( $field_data ) ) {
        $output = esc_html( $field_data );
    }

    // type: multicheck
    if( $setting['type'] == 'multicheck' && ! empty( $field_data ) ) {
        foreach ( $field_data as $key => $value ) {
            $output .= esc_html( $value ) . '<br>';
        }
    }

    // type: textarea
    if( $setting['type'] == 'textarea' && ! empty( $field_data ) ) {
        $output = wp_kses_post( $field_data );
    }

    // type: id
    if( $setting['type'] == 'id' && ! empty( $field_data ) ) {
        $item = upstream_project_item_by_id( upstream_post_id(), $field_data );
        $output = isset( $item['title'] ) ? $item['title'] :  $item['milestone'];
    }

    // type: tasks
    if( $setting['type'] == 'tasks' ) {
        $open = isset( $item['task_open'] ) ? $item['task_open'] : '0';
        $output = sprintf( __( '%d %s / %d Open', 'upstream' ), $item['task_count'], upstream_task_label_plural(), $open );
    }

    // type: file
    if( $setting['type'] == 'file' && ! empty( $field_data ) ) {
        $file_list = null;
        if( isset( $item['file'] ) && isset( $item['file_id'] ) && $item['file'] != '' ) {
            $file_list .= upstream_get_file_preview( $item['file_id'], $item['file'] );
        }
        $output = $file_list;
    }

    // type: progress
    if( $key == 'progress' ) {
        $output = $field_data == 0 ? '0' : $field_data;
        $output .= '%';
    }


    // allows us to add extra checks for different data and field formatting
    $output = apply_filters( 'upstream_format_table_data', $output, $item, $key, $setting );


    return $output;

}
