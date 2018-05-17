<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function upstream_hide_meta_boxes() {
    remove_meta_box( 'authordiv', 'project', 'normal' );
}

add_action( 'admin_menu', 'upstream_hide_meta_boxes' );

/**
 * UpStream_Roles Class
 *
 * This class handles the role creation and assignment of capabilities for those roles.
 *
 *
 * @since 1.0.0
 */
class UpStream_Roles {

    /**
     * Add new shop roles with default WP caps
     * Called during installation
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function add_roles() {
        add_role( 'upstream_manager', __( 'UpStream Manager', 'upstream' ), [
            'read'                   => true,
            'edit_posts'             => true,
            'delete_posts'           => true,
            'unfiltered_html'        => true,
            'upload_files'           => true,
            'export'                 => true,
            'import'                 => true,
            'delete_others_pages'    => true,
            'delete_others_posts'    => true,
            'delete_pages'           => true,
            'delete_private_pages'   => true,
            'delete_private_posts'   => true,
            'delete_published_pages' => true,
            'delete_published_posts' => true,
            'edit_others_pages'      => true,
            'edit_others_posts'      => true,
            'edit_pages'             => true,
            'edit_private_pages'     => true,
            'edit_private_posts'     => true,
            'edit_published_pages'   => true,
            'edit_published_posts'   => true,
            'manage_categories'      => true,
            'manage_links'           => true,
            'moderate_comments'      => true,
            'publish_pages'          => true,
            'publish_posts'          => true,
            'read_private_pages'     => true,
            'read_private_posts'     => true,
        ] );
        add_role( 'upstream_user', __( 'UpStream User', 'upstream' ), [
            'read'         => true,
            'edit_posts'   => true,
            'upload_files' => true,
        ] );

        self::addClientUsersRole();
    }

    /**
     * Method responsible for creating the 'upstream_client_user' role if it doesn't exist yet.
     *
     * @since   1.11.0
     * @static
     *
     * @global  $wp_roles
     */
    public static function addClientUsersRole() {
        global $wp_roles;

        $theRoleIndetifier = 'upstream_client_user';

        if ( ! $wp_roles->is_role( $theRoleIndetifier ) ) {
            add_role( $theRoleIndetifier, __( 'UpStream Client User', 'upstream' ), [
                'read'         => true,
                'upload_files' => true,
            ] );
        }
    }

    /**
     * Add new UpStream specific capabilities
     * Called during installation
     *
     * @access public
     * @since  1.0.0
     * @global WP_Roles $wp_roles
     * @return void
     */
    public function add_caps() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) ) {
            if ( ! isset( $wp_roles ) ) {
                $wp_roles = new WP_Roles();
            }
        }

        if ( is_object( $wp_roles ) ) {

            // Add the main post type capabilities
            $capabilities = $this->get_upstream_manager_caps();
            foreach ( $capabilities as $cap_group ) {
                foreach ( $cap_group as $cap ) {
                    $wp_roles->add_cap( 'upstream_manager', $cap );
                    $wp_roles->add_cap( 'administrator', $cap );
                }
            }

            // Add the main post type capabilities
            $capabilities = $this->get_upstream_user_caps();
            foreach ( $capabilities as $cap_group ) {
                foreach ( $cap_group as $cap ) {
                    $wp_roles->add_cap( 'upstream_user', $cap );
                }
            }


        }
    }

    /**
     * Gets the core post type capabilities
     *
     * @access public
     * @since  1.0.0
     * @return array $capabilities Core post type capabilities
     */
    public function get_upstream_manager_caps() {
        $capabilities = [];

        $capability_types = [ 'project', 'client' ];

        foreach ( $capability_types as $capability_type ) {
            $capabilities[ $capability_type ] = [
                // Post type
                "edit_{$capability_type}",
                "read_{$capability_type}",
                "delete_{$capability_type}",
                "edit_{$capability_type}s",
                "edit_others_{$capability_type}s",
                "publish_{$capability_type}s",
                "read_private_{$capability_type}s",
                "delete_{$capability_type}s",
                "delete_private_{$capability_type}s",
                "delete_published_{$capability_type}s",
                "delete_others_{$capability_type}s",
                "edit_private_{$capability_type}s",
                "edit_published_{$capability_type}s",

                // Terms
                "manage_{$capability_type}_terms",
                "edit_{$capability_type}_terms",
                "delete_{$capability_type}_terms",
                "assign_{$capability_type}_terms",

                "edit_project_author",
                "manage_upstream",

            ];
        }

        return $capabilities;
    }

    /**
     * Gets the core post type capabilities
     *
     * @access public
     * @since  1.0.0
     * @return array $capabilities Core post type capabilities
     */
    public function get_upstream_user_caps() {

        $capabilities['project'] = [
            'edit_project',
            'read_project',
            'edit_projects',
            // 'edit_others_projects',
            // 'read_private_projects',
            // 'edit_private_projects',
            'edit_published_projects',

            // === TERMS ===
            'assign_project_terms',
            'manage_project_terms',
            //'edit_project_terms',
            //'delete_project_terms',

            /*
             * Individual project fields.
             * Giving the role access to these fields, means that
             * they can edit OTHER users tasks, bugs, milestones
             * but only the fields added to their capabilities.
             * And this will only work in the WP admin. Not with the frontend extension.
             */
            //'project_status_field',
            //'project_owner_field',
            //'project_client_field',
            'project_users_field',
            //'project_start_date_field',
            //'project_end_date_field',

            // individual milestone fields
            //'milestone_milestone_field',
            //'milestone_assigned_to_field',
            //'milestone_start_date_field',
            //'milestone_end_date_field',
            //'milestone_notes_field',

            // individual task fields
            //'task_title_field',
            //'task_assigned_to_field',
            //'task_status_field',
            //'task_progress_field',
            //'task_start_date_field',
            //'task_end_date_field',
            //'task_notes_field',
            //'task_milestone_field',

            // individual bug fields
            // 'bug_title_field',
            // 'bug_assigned_to_field',
            // 'bug_description_field',
            // 'bug_status_field',
            // 'bug_severity_field',
            // 'bug_files_field',
            // 'bug_due_date_field',

            //'publish_project_milestones', // enables the 'Add Milestone' button within project
            'publish_project_tasks', // enables the 'Add Task' button within project
            'publish_project_bugs', // enables the 'Add Bug' button within project
            'publish_project_files', // enables the 'Add Files' button within project
            'publish_project_discussion',
            'delete_project_discussion',

            //'delete_project_milestones',
            //'delete_project_tasks',
            //'delete_project_bugs',
            //'delete_project_files',

            //'sort_project_milestones',
            //'sort_project_tasks',
            //'sort_project_bugs',
            //'sort_project_files',

        ];

        $capabilities['client'] = [
            'edit_client',
            'read_client',
            'edit_clients',
            'edit_others_clients',
            'publish_clients',
            // 'read_private_clients',
            // 'edit_private_clients',
            'edit_published_clients',
        ];

        return $capabilities;
    }

    /**
     * Remove core post type capabilities (called on uninstall)
     *
     * @access public
     * @since  1.5.2
     * @return void
     */
    public function remove_caps() {

        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) ) {
            if ( ! isset( $wp_roles ) ) {
                $wp_roles = new WP_Roles();
            }
        }

        if ( is_object( $wp_roles ) ) {

            // Add the main post type capabilities
            $manager_caps = $this->get_upstream_manager_caps();
            $manager_role = get_role( 'upstream_manager' );
            $admin_role   = get_role( 'administrator' );

            foreach ( $manager_caps as $post_type ) {
                foreach ( $post_type as $index => $cap ) {
                    if ( $manager_role ) {
                        $manager_role->remove_cap( $cap );
                    }
                    if ( $admin_role ) {
                        $admin_role->remove_cap( $cap );
                    }
                }
            }

            // Add the main post type capabilities
            $user_caps = $this->get_upstream_user_caps();
            $user_role = get_role( 'upstream_user' );
            foreach ( $user_caps as $post_type ) {
                foreach ( $post_type as $index => $cap ) {
                    if ( $user_role ) {
                        $user_role->remove_cap( $cap );
                    }
                }
            }

        }

    }
}
