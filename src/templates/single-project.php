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
if( ! upstream_permissions( 'view_project' ) ) {
    wp_redirect(get_post_type_archive_link('project'));
}


upstream_get_template_part( 'global/header.php' );
upstream_get_template_part( 'global/sidebar.php' );
upstream_get_template_part( 'global/top-nav.php' );

/*
 * upstream_single_project_before hook.
 */
do_action( 'upstream_single_project_before' );

$user = upstream_user_data();

while ( have_posts() ) : the_post(); ?>

<!-- page content -->
<div class="right_col" role="main">
    <div class="alerts">
        <?php do_action('upstream_frontend_projects_messages'); ?>
    </div>

    <div class="">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <h3 style="display: inline-block;"><?php echo get_the_title( get_the_ID() ); ?></h3>
                <?php $status = upstream_project_status_color($id); ?>
                <?php if (!empty($status['status'])): ?>
                <button type="button" class="btn btn-success btn-xs" style="border: none;background-color:<?php echo esc_attr( $status['color'] ); ?>"><?php echo $status['status'] ?></button>
                <?php endif; ?>
            </div>
            <?php include 'single-project/overview.php'; ?>
        </div>

        <div class="clearfix"></div>

            <div class="row">
                <?php do_action( 'upstream_single_project_before_details' ); ?>

                <?php upstream_get_template_part( 'single-project/details.php' ); ?>
            </div>

            <?php if (!upstream_are_milestones_disabled() && !upstream_disable_milestones()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_milestones' ); ?>

                <?php upstream_get_template_part( 'single-project/milestones.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_are_tasks_disabled() && !upstream_disable_tasks()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_tasks' ); ?>

                <?php upstream_get_template_part( 'single-project/tasks.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_disable_bugs() && !upstream_are_bugs_disabled()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_bugs' ); ?>

                <?php upstream_get_template_part( 'single-project/bugs.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_are_files_disabled() && !upstream_disable_files()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_files' ); ?>

                <?php upstream_get_template_part( 'single-project/files.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_disable_discussions()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_discussion' ); ?>

                <?php upstream_get_template_part( 'single-project/discussion.php' ); ?>
            </div>
            <?php endif; ?>
    </div>
</div>

<?php endwhile;
    /**
     * upstream_after_project_content hook.
     *
     */
    do_action( 'upstream_after_project_content' );

    upstream_get_template_part( 'global/footer.php' );
    ?>
