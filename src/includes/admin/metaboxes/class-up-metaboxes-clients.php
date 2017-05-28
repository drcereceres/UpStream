<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'UpStream_Metaboxes_Clients' ) ) :


/**
 * CMB2 Theme Options
 * @version 0.1.0
 */
class UpStream_Metaboxes_Clients {

    /**
     * Post type
     * @var string
     */
    public $type = 'client';

    /**
     * Post type
     * @var string
     */
    public $label = '';

    /**
     * Metabox prefix
     * @var string
     */
    public $prefix = '_upstream_client_';

    /**
     * Holds an instance of the object
     *
     * @var Myprefix_Admin
     **/
    public static $instance = null;

    public function __construct() {
        $this->label = upstream_client_label();
        $this->label_plural = upstream_client_label_plural();

        add_action('cmb2_render_password', array('UpStream_Metaboxes_Clients', 'render_password_field'), 10, 5);
        add_action('cmb2_sanitize_password', array('UpStream_Metaboxes_Clients', 'sanitize_password_field'), 10, 5);
    }

    /**
     * Returns the running object
     *
     * @return Myprefix_Admin
     **/
    public static function get_instance() {
        if( is_null( self::$instance ) ) {
            self::$instance = new self();
            self::$instance->users();
            self::$instance->sidebar_top();
        }
        return self::$instance;
    }

    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function users() {

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'users',
            'title'         => '<span class="dashicons dashicons-groups"></span> ' . sprintf( __( "%s Users", 'upstream' ), $this->label ),
            'object_types'  => array( $this->type ),
        ) );

        //Create a default grid
        $cmb2Grid = new \Cmb2Grid\Grid\Cmb2Grid($metabox);

        $group_field_id = $metabox->add_field( array(
            'id'          => $this->prefix . 'users',
            'type'        => 'group',
            'description' => '',
            'options'     => array(
                'group_title'   => __( "User {#}", 'upstream' ),
                'add_button'    => __( "Add Another User", 'upstream' ),
                'remove_button' => __( "Remove User", 'upstream' ),
            ),
        ) );

        $fields = array();

        $fields[0] = array(
            'id'            => 'id',
            'type'          => 'text',
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
            'name' => '<span class="dashicons dashicons-admin-users"></span> ' . __( "First Name", 'upstream' ),
            'id'   => 'fname',
            'type' => 'text',
            'attributes' => array(
                'class' => 'first-name',
            )
        );
        $fields[11] = array(
            'name' => '<span class="dashicons dashicons-admin-users"></span> ' . __( "Last Name", 'upstream' ),
            'id'   => 'lname',
            'type' => 'text',
        );

        // start row
        $fields[20] = array(
            'name'      => '<span class="dashicons dashicons-email"></span> ' . __( "Email Address", 'upstream' ),
            'id'        => 'email',
            'desc'      => __( "Users log in using their email address, so all email addresses for Client Users MUST be unique", 'upstream' ),
            'type'      => 'text_email',
            'attributes' => array(
                'class' => 'user-email',
            )
        );

        $fields[21] = array(
            'name' => '<span class="dashicons dashicons-phone"></span> ' . __( "Phone", 'upstream' ),
            'id'   => 'phone',
            'type' => 'text',
        );



        // set up the group grid plugin
        $cmb2GroupGrid = $cmb2Grid->addCmb2GroupGrid( $group_field_id );

        // define nuber of rows
        $rows = apply_filters( 'upstream_client_users_metabox_rows', 3 );

        // filter the fields & sort numerically
        $fields = apply_filters( 'upstream_client_users_metabox_fields', $fields );
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
                // probably don't need this to be filterable but will leave for now
                $row[$i]->addColumns(
                    apply_filters( "upstream_client_users_row_{$i}_columns", $array )
                );

            }

        }


    }

    /**
     * Add the metaboxes
     * @since  0.1.0
     */
    public function sidebar_top()
    {
        $client_id = (int) upstream_post_id();

        $metabox = new_cmb2_box( array(
            'id'            => $this->prefix . 'info',
            'title'         => '<span class="dashicons dashicons-admin-generic"></span> ' . sprintf( __( "%s Details", 'upstream' ), $this->label ),
            'object_types'  => array( $this->type ),
            'context'    => 'side',
            'priority'   => 'high',
        ) );

        $metabox->add_field( array(
            'name'       => __( 'Website', 'upstream' ),
            'desc'       => '',
            'id'         => $this->prefix . 'website',
            'type'       => 'text_url',
        ) );
        $metabox->add_field( array(
            'name'       => __( 'Phone', 'upstream' ),
            'desc'       => '',
            'id'         => $this->prefix . 'phone',
            'type'       => 'text',
        ) );

        $metabox->add_field( array(
            'name'       => __( 'Address', 'upstream' ),
            'desc'       => '',
            'id'         => $this->prefix . 'address',
            'type'       => 'textarea_small',
        ) );

        $metabox->add_field( array(
            'name'       => __( 'Logo', 'upstream' ),
            'desc'       => '',
            'id'         => $this->prefix . 'logo',
            'type'       => 'file',
        ) );

        $passwordFieldLabelText = $client_id > 0 ? __('New Password', 'upstream') : __('Password', 'upstream');

        $metabox->add_field( array(
            'name'     => $passwordFieldLabelText,
            'id'       => $this->prefix . 'password',
            'type'     => 'password',
            'required' => $client_id > 0 ? '' : 'required'
        ));
    }

    /**
     * Callback responsible for sanitizing Password type fields.
     *
     * @since   @todo
     * @static
     *
     * @param   string          $valueOverride  Sanitization override value to return
     * @param   string          $value          The value being passed
     * @param   int             $postId         The post id
     * @param   \CMB2_Sanitize  $sanitizer      CMB2 Sanitizer instance
     */
    public static function sanitize_password_field($valueOverride, $value, $postId = 0, $field, $sanitizer)
    {
        if (!empty($value)) {
            $value = password_hash($value, PASSWORD_BCRYPT);
        }

        var_dump($sanitizer); die();

        return $value;
    }

    /**
     * Method responsible for render Password typed fields.
     *
     * @since   @todo
     * @static
     *
     * @param   \CMB2_Field $field              The field object
     * @param   string      $value              The field value
     * @param   integer     $postId             The current post id
     * @param   string      $objectType         The type of object being worked with
     * @param   \CMB2_Types $fieldTypeInstance  The field type instance
     */
    public static function render_password_field($field, $value = "", $postId = 0, $objectType = "", $fieldTypeInstance = null)
    {
        if ($objectType === "post") {
            $inputAttrs = array(
                'type'    => 'password',
                'pattern' => '.{5,}',
                'desc'    => '<p class="cmb2-metabox-description">It must have at least 5 characters.</p>',
                'value'   => ""
            );

            if (isset($field->args['required']) && $field->args['required'] === 'required') {
                $inputAttrs['required'] = 'required';
            }

            echo $fieldTypeInstance->input($inputAttrs);
        }
    }
}

endif;
