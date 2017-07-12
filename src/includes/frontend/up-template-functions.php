<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * Set the path to be used in the theme folder.
 * Templates in this folder will override the plugins frontend templates.
 */
function upstream_template_path() {
    return apply_filters( 'upstream_template_path', 'upstream/' );
}


/**
 * Hide admin bar
 */
function upstream_hide_admin_bar( $show ) {
    if ( get_post_type() == 'project' ) :
        return false;
    endif;
    return $show;
}
add_filter( 'show_admin_bar', 'upstream_hide_admin_bar' );


/*
 * Check relevant directories for template parts.
 * Looks in child theme first, then parent theme, then plugin.
 */
function upstream_get_template_part( $part ) {

    if ( $part ) {
        $check_dirs = apply_filters( 'upstream_check_template_directory', array(
            trailingslashit( get_stylesheet_directory() ) . upstream_template_path(),
            trailingslashit( get_template_directory() ) . upstream_template_path(),
            UPSTREAM_PLUGIN_DIR . 'templates/'
        ));
        foreach ( $check_dirs as $dir ) {
            if ( file_exists( trailingslashit( $dir ) . $part ) ) {
                load_template( $dir . $part );
                return;
            }
        }
    }
    return $part;
}


// output list of the client users avatars
function upstream_output_client_users( $id = null  ) {

    $users = upstream_project_client_users( $id );
    if( $users ) :
    ?>

    <ul class="list-inline">
        <?php foreach ( $users as $user_id ) { ?>
            <li>
                <?php echo upstream_user_avatar( $user_id ); ?>
            </li>
        <?php } ?>
    </ul>

    <?php
    else :
        echo '<p>' . __( 'No users', 'upstream' ) . '</p>';
    endif;

}
// output list of the project members avatars
function upstream_output_project_members( $id = null ) {

    $users = upstream_project_users();
    if( $users ) :
    ?>

    <ul class="list-inline">
        <?php foreach ( $users as $user_id ) { ?>
            <li>
                <?php echo upstream_user_avatar( $user_id ); ?>
            </li>
        <?php } ?>
    </ul>

    <?php
    else :
        echo '<p>' . __( 'No members', 'upstream' ) . '</p>';
    endif;

}

function upstream_get_file_preview( $attachment_id, $attachment_url ) {

    $filetype = wp_check_filetype( $attachment_url );
    $filename = basename( $attachment_url );

    if( wp_get_attachment_image( $attachment_id, 'thumbnail' ) ) {

        $output = '<li><a target="_blank" href="' . esc_url( $attachment_url ) . '">' . wp_get_attachment_image(
            $attachment_id,
            array( 32, 32 ),
            false,
            array(
                'title' => esc_attr( $filename ),
                'data-toggle' => 'tooltip',
                'data-placement' => 'top',
                'data-fileid' => (int) $attachment_id,
                'data-fileurl' => esc_attr( $attachment_url ),
                'class' => 'avatar itemfile'
                )
            ) . '</a></li>';

    } else {

        switch ( $filetype['ext'] ) {
            case 'pdf': $icon = 'fa-file-pdf-o'; break;
            case 'csv': case 'xls': case 'xlsx': $icon = 'fa-file-excel-o'; break;
            case 'doc': case 'docx': $icon = 'fa-file-word-o'; break;
            case 'ppt': case 'pptx': case 'pps': case 'ppsx': case 'key': $icon = 'fa-file-powerpoint-o'; break;
            case 'zip': case 'rar': case 'tar': $icon = 'fa-file-zip-o'; break;
            case 'mp3': case 'm4a': case 'ogg': case 'wav': $icon = 'fa-file-audio-o'; break;
            case 'mp4': case 'm4v': case 'mov': case 'wmv': case 'avi': case 'mpg': case 'ogv': case '3gp': case '3g2': $icon = 'fa-file-video-o'; break;
            default: $icon = 'fa-file-text-o'; break;
        };

        $output = '<li><a target="_blank" href="' . esc_url( $attachment_url ) . '"><i class="itemfile fa ' . esc_attr( $icon ) . '" data-toggle="tooltip" data-placement="top" data-fileid="' . (int) $attachment_id . '" data-fileurl="' . esc_attr( $attachment_url ) . '" title="' . esc_attr( $filename ) . '"></i></a></li>';
    }

    return $output;

}

function upstream_output_file_list( $img_size = 'thumbnail' ) {
    // Get the list of files
    $files  = get_post_meta( upstream_post_id(), '_upstream_project_files', true );
    $file   = array();

    if ( $files ) {
        foreach ($files as $i => $filedata) {
            if( isset( $filedata['file'] ) && isset( $filedata['file_id'] ) && $filedata['file'] != '' ) {
                $file[] = upstream_get_file_preview( $filedata['file_id'], $filedata['file'] );
            }
        }
    }

    if ( $file ) {
        // loop through the rows
        $output = '<ul class="list-inline">';
        foreach ($file as $li) {
            if( isset( $filedata['file'] ) && isset( $filedata['file_id'] ) && $filedata['file'] != '' ) {
                $output .= $li;
            }
        }
        $output .= '</ul>';
    } else {
        $output = '<p>' . __( 'Currently no files', 'upstream' ) . '</p>';
    }

    return $output;

}


function upstream_output_comments( $id ) {

    $comments = upstream_project_discussion( $id );

    if( $comments ) {
        global $wp_embed;

        $comments = array_reverse( $comments );
        foreach ($comments as $comment) {

            $user       = upstream_user_data( $comment['created_by'] );
            $time       = date_i18n( get_option( 'time_format' ), $comment['created_time'] ) . ' ' . upstream_format_date( $comment['created_time'] );
            $time_ago   = sprintf( _x( '%s ago', '%s = human-readable time difference', 'upstream' ), human_time_diff( $comment['created_time'], current_time( 'timestamp', false ) ) );
            $tooltip    = 'data-toggle="tooltip" data-placement="top"';
            ?>

            <li>
                <?php echo upstream_user_avatar( $comment['created_by'], false ); ?>
                <div class="message_date">
                    <h3 class="date text-info" title="<?php echo esc_attr( $time ); ?>" <?php echo $tooltip; ?> ><?php echo date_i18n( 'd', $comment['created_time'] ) ?></h3>
                    <p class="month" title="<?php echo esc_attr( $time ); ?>"><?php echo date_i18n( 'M', $comment['created_time'] ) ?></p>
                </div>
                <div class="message_wrapper">
                    <h4 class="heading"><?php echo esc_html($user['display_name']); ?>
                    <small><?php echo esc_html( $time_ago ); ?></small></h4>

                    <?php do_action( 'upstream_before_single_message', $id, $comment ); ?>

                    <blockquote class="message"><?php echo $wp_embed->autoembed(wpautop($comment['comment'])); ?></blockquote>

                    <?php do_action( 'upstream_after_single_message', $id, $comment ); ?>

                </div>
            </li>

        <?php }

    } else {

        echo '<p>' . __( 'Currently no messages', 'upstream' ) . '</p>';

    }

}
