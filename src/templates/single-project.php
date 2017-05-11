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
    wp_redirect( get_bloginfo( 'url' ) . '/projects' );
}


upstream_get_template_part( 'global/header.php' );
upstream_get_template_part( 'global/sidebar.php' );
upstream_get_template_part( 'global/top-nav.php' );

/*
 * upstream_single_project_before hook.
 */
do_action( 'upstream_single_project_before' );

while ( have_posts() ) : the_post(); ?>

<!-- page content -->
<div class="right_col" role="main">

    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3><?php echo get_the_title( get_the_ID() ); ?>
                <?php
                    $status = upstream_project_status_color( $id );
                    if( $status['status'] ) {
                ?>
                    <button type="button" class="btn btn-success btn-xs" style="border: none;background-color:<?php echo esc_attr( $status['color'] ); ?>"><?php echo $status['status'] ?></button>
                <?php } ?> </h3>
            </div>
        </div>

        <div class="clearfix"></div>


            <div class="row">
                <?php do_action( 'upstream_single_project_before_overview' ); ?>

                <?php upstream_get_template_part( 'single-project/overview.php' ); ?>
            </div>

            <div class="row">
                <?php do_action( 'upstream_single_project_before_discussion' ); ?>

                <?php upstream_get_template_part( 'single-project/discussion.php' ); ?>

                <?php do_action( 'upstream_single_project_before_details' ); ?>

                <?php upstream_get_template_part( 'single-project/details.php' ); ?>
            </div>

            <?php if (!upstream_are_milestones_disabled()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_milestones' ); ?>

                <?php upstream_get_template_part( 'single-project/milestones.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_are_tasks_disabled()): ?>
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

            <?php if (!upstream_are_files_disabled()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_files' ); ?>

                <?php upstream_get_template_part( 'single-project/files.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_are_tasks_disabled() || (!upstream_disable_bugs() && !upstream_are_bugs_disabled())): ?>
            <hr />
            <?php endif; ?>

            <?php if (!upstream_are_tasks_disabled()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_tasks' ); ?>

                <?php upstream_get_template_part( 'single-project/my-tasks.php' ); ?>
            </div>
            <?php endif; ?>

            <?php if (!upstream_disable_bugs() && !upstream_are_bugs_disabled()): ?>
            <div class="row">
                <?php do_action( 'upstream_single_project_before_bugs' ); ?>

                <?php upstream_get_template_part( 'single-project/my-bugs.php' ); ?>
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
