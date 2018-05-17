<?php
/**
 * The Template for displaying a single project
 *
 * This template can be overridden by copying it to yourtheme/upstream/single-project.php.
 *
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// redirect to projects if no permissions for this project
if ( ! upstream_user_can_access_project( get_current_user_id(), upstream_post_id() ) ) {
    wp_redirect( get_post_type_archive_link( 'project' ) );
    exit;
}

set_time_limit( 120 );

$pluginOptions     = get_option( 'upstream_general' );
$pageTitle         = get_bloginfo( 'name' );
$siteUrl           = get_bloginfo( 'url' );
$projectsListUrl   = get_post_type_archive_link( 'project' );
$supportUrl        = upstream_admin_support( $pluginOptions );
$logOutUrl         = upstream_logout_url();
$areClientsEnabled = ! is_clients_disabled();

$currentUser = (object) upstream_user_data();

$projectsList = [];
if ( isset( $currentUser->projects ) ) {
    if ( is_array( $currentUser->projects ) && count( $currentUser->projects ) > 0 ) {
        foreach ( $currentUser->projects as $project_id => $project ) {
            $data = (object) [
                'id'          => $project_id,
                'author'      => (int) $project->post_author,
                'created_at'  => (string) $project->post_date_gmt,
                'modified_at' => (string) $project->post_modified_gmt,
                'title'       => $project->post_title,
                'slug'        => $project->post_name,
                'status'      => $project->post_status,
                'permalink'   => get_permalink( $project_id ),
            ];

            $projectsList[ $project_id ] = $data;
        }

        unset( $project, $project_id );
    }

    unset( $currentUser->projects );
}

$projectsListCount = count( $projectsList );

$i18n = [
    'LB_PROJECT'        => upstream_project_label(),
    'LB_PROJECTS'       => upstream_project_label_plural(),
    'LB_TASKS'          => upstream_task_label_plural(),
    'LB_BUGS'           => upstream_bug_label_plural(),
    'LB_LOGOUT'         => __( 'Log Out', 'upstream' ),
    'LB_ENDS_AT'        => __( 'Ends at', 'upstream' ),
    'MSG_SUPPORT'       => upstream_admin_support_label( $pluginOptions ),
    'LB_TITLE'          => __( 'Title', 'upstream' ),
    'LB_TOGGLE_FILTERS' => __( 'Toggle Filters', 'upstream' ),
    'LB_EXPORT'         => __( 'Export', 'upstream' ),
    'LB_PLAIN_TEXT'     => __( 'Plain Text', 'upstream' ),
    'LB_CSV'            => __( 'CSV', 'upstream' ),
    'LB_CLIENT'         => upstream_client_label(),
    'LB_CLIENTS'        => upstream_client_label_plural(),
    'LB_STATUS'         => __( 'Status', 'upstream' ),
    'LB_STATUSES'       => __( 'Statuses', 'upstream' ),
    'LB_CATEGORIES'     => __( 'Categories' ),
    'LB_PROGRESS'       => __( 'Progress', 'upstream' ),
    'LB_NONE_UCF'       => __( 'None', 'upstream' ),
    'LB_NONE'           => __( 'none', 'upstream' ),
    'LB_COMPLETE'       => __( '%s Complete', 'upstream' ),
];

upstream_get_template_part( 'global/header.php' );
include_once 'global/sidebar.php';
upstream_get_template_part( 'global/top-nav.php' );

/*
 * upstream_single_project_before hook.
 */
do_action( 'upstream_single_project_before' );

$user = upstream_user_data();

$options                = (array) get_option( 'upstream_general' );
$displayOverviewSection = ! isset( $options['disable_project_overview'] ) || (bool) $options['disable_project_overview'] === false;
$displayDetailsSection  = ! isset( $options['disable_project_details'] ) || (bool) $options['disable_project_details'] === false;
unset( $options );

while ( have_posts() ) : the_post(); ?>

    <!-- page content -->
    <div class="right_col" role="main">
        <div class="alerts">
        <?php do_action('upstream_frontend_projects_messages'); ?>
    </div>

        <div class="">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-5">
                    <h3 style="display: inline-block;"><?php echo get_the_title( get_the_ID() ); ?></h3>
                    <?php $status = upstream_project_status_color( $id ); ?>
                    <?php if ( ! empty( $status['status'] ) ): ?>
                        <span class="label up-o-label"
                              style="background-color: <?php echo esc_attr( $status['color'] ); ?>"><?php echo $status['status']; ?>
                </span>
                    <?php endif; ?>
                </div>

                <?php if ( $displayOverviewSection ): ?>
                    <?php include 'single-project/overview.php'; ?>
                <?php endif; ?>

                <?php if ( $displayDetailsSection ): ?>
                    <?php do_action( 'upstream_single_project_before_details' ); ?>
                    <?php upstream_get_template_part( 'single-project/details.php' ); ?>
                <?php endif; ?>
            </div>

            <div class="clearfix"></div>


            <?php do_action( 'upstream:frontend.project.renderAfterDetails' ); ?>

            <?php if ( ! upstream_are_milestones_disabled() && ! upstream_disable_milestones() ): ?>
                <div class="row">
                <?php do_action( 'upstream_single_project_before_milestones' ); ?>

                <?php upstream_get_template_part( 'single-project/milestones.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if ( ! upstream_are_tasks_disabled() && ! upstream_disable_tasks() ): ?>
                <div class="row">
                <?php do_action( 'upstream_single_project_before_tasks' ); ?>

                <?php upstream_get_template_part( 'single-project/tasks.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if ( ! upstream_disable_bugs() && ! upstream_are_bugs_disabled() ): ?>
                <div class="row">
                <?php do_action( 'upstream_single_project_before_bugs' ); ?>

                <?php upstream_get_template_part( 'single-project/bugs.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if ( ! upstream_are_files_disabled() && ! upstream_disable_files() ): ?>
                <div class="row">
                <?php do_action( 'upstream_single_project_before_files' ); ?>

                <?php upstream_get_template_part( 'single-project/files.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if ( upstreamAreProjectCommentsEnabled() ): ?>
                <div class="row">
                <?php do_action( 'upstream_single_project_before_discussion' ); ?>

                <?php upstream_get_template_part( 'single-project/discussion.php' ); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <input type="hidden" id="project_id" value="<?php echo upstream_post_id(); ?>">
<?php endwhile;
/**
 * upstream_after_project_content hook.
 *
 */
do_action( 'upstream_after_project_content' );

include_once 'global/footer.php';
?>
