<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function upstream_client_logo($client_id = 0)
{
    $logoURL = "";
    $client_id = (int) $client_id;

    if ($client_id === 0) {
        $client_id = (int) upstream_project_client_id();
    }

    if ($client_id === 0) {
        $user_id = upstream_current_user_id();
        $client_id = (int) upstream_get_users_client_id($user_id);
    }

    if ($client_id > 0) {
        global $wpdb, $table_prefix;

        $logoURL = $wpdb->get_var(sprintf('
            SELECT `meta_value`
            FROM `%s`
            WHERE `post_id` = "%s"
                AND `meta_key` = "_upstream_client_logo"',
            $table_prefix . 'postmeta',
            $client_id
        ));
    }

    return apply_filters('upstream_client_logo', $logoURL, $client_id);
}

/**
 * Save post metadata when a post is saved.
 * Mainly used to update user ids
 *
 * @param int $post_id The post ID.
 * @param post $post The post object.
 * @param bool $update Whether this is an existing post being updated or not.
 */
function upstream_update_client_meta_values( $post_id, $post, $update ) {

    $slug = 'client';

    // If this isn't a 'client' post, don't update it.
    if ( $slug != $post->post_type ) {
        return;
    }

    // update the overall progress of the project
    if ( isset( $_POST['_upstream_client_users'] ) ) :

        $users = $_POST['_upstream_client_users'];

        // update the user with a unique id if one is not set
        $i = 0;
        if( $users ) :
            foreach ($users as $user) {
                if( ! isset( $user['id'] ) || empty( $user['id'] ) || $user['id'] == '' ){
                    $users[$i]['id'] = upstream_admin_set_unique_id();
                }
            $i++;
            }
        endif;

        update_post_meta( $post_id, '_upstream_client_users', $users );

    endif;

}
add_action( 'save_post', 'upstream_update_client_meta_values', 99999, 3 );
