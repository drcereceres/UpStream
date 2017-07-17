<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Admin_Client_Columns' ) ) :

/**
 * Admin columns
 * @version 0.1.0
 */
class UpStream_Admin_Client_Columns {

    private $label;
    private $label_plural;

    /**
     * Constructor
     * @since 0.1.0
     */
    public function __construct() {
        $this->label = upstream_client_label();
        $this->label_plural = upstream_client_label_plural();
        return $this->hooks();
    }


    public function hooks() {
        add_filter( 'manage_client_posts_columns', array( $this, 'client_columns' ) );
        add_action( 'manage_client_posts_custom_column', array( $this, 'client_data' ), 10, 2 );
    }

    /**
     * Set columns for client
     */
    public function client_columns( $defaults ) {

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
        $defaults['title']      = $this->label;
        $defaults['logo']       = __( 'Logo', 'upstream' );
        $defaults['website']    = __( 'Website', 'upstream' );
        $defaults['phone']      = __( 'Phone', 'upstream' );
        $defaults['address']    = __( 'Address', 'upstream' );
        $defaults['users']      = __( 'Users', 'upstream' );
        return $defaults;
    }

    public function client_data( $column_name, $post_id ) {

        $client = new UpStream_Client( $post_id );

        if ( $column_name == 'logo' ) {
            $img = wp_get_attachment_image_src( $client->get_meta( 'logo_id' ) );
            echo '<img height="50" src="' . $img[0] . '" />';
        }

        if ( $column_name == 'website' ) {
            $website = $client->get_meta( 'website' );
            if( $website ) {
                echo '<a href="' . esc_url( $website ) . '" target="_blank">' . esc_html( $website ) . '</a>';
            }
        }

        if ( $column_name == 'phone' ) {
            echo $client->get_meta( 'phone' );
        }

        if ( $column_name == 'address' ) {
            echo wp_kses_post( wpautop( $client->get_meta( 'address' ) ) );
        }

        if ( $column_name == 'users' ) {
            upstream_client_render_users_column( $client->get_meta( 'users' ) );
        }

    }

}

new UpStream_Admin_Client_Columns;

endif;


/**
 * Manually render a field column display.
 *
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object
 */
function upstream_client_render_users_column( $value ) {

    if( ! $value )
        return;
    ?>
    <p>
        <?php

        $i = 0;
        $count = count( $value );
        foreach ( $value as $key => $user ) {

            echo $user['fname'] . ' ' . $user['lname'] . '<br>';

             // set limit on number of users to display
            if (++$i == 2 && $count > 2) :
                $more = $count - $i;
                printf( _n( '+%s more user', '+%s more users', $more, 'upstream'), $more );
                break;
            endif;

        }
        ?>
    </p>
    <?php
}
