<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$milestones = upstream_milestone_label_plural();
$milestone  = upstream_milestone_label();
$m_count    = upstream_count_total( 'milestones', get_the_ID() );
$tasks      = upstream_task_label_plural();
$task       = upstream_task_label();
$t_count    = upstream_count_total( 'tasks', get_the_ID() );
$bugs       = upstream_bug_label_plural();
$bug        = upstream_bug_label();
$b_count    = upstream_count_total( 'bugs', get_the_ID() );

$width = upstream_disable_bugs() ? 'col-lg-6 col-md-6 ' : 'col-lg-4 col-md-4 ';

$projectDescription = upstream_project_description();

$postID = (int)get_the_ID();
?>

    <?php if (!empty($projectDescription)): ?>
    <div class="description col-xs-12">
        <p><?php echo $projectDescription; ?></p>
    </div>
    <?php endif; ?>

    <?php if (!upstream_are_milestones_disabled($postID) && !upstream_disable_milestones()): ?>
    <div class="<?php echo esc_attr( $width ); ?> col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-flag"></i>
            </div>
            <div class="count">
                <span><?php echo number_format_i18n($m_count); ?></span> <small><?php echo $m_count === 1 ? $milestone : $milestones; ?></small>
            </div>
            <h3><?php echo upstream_count_total_open( 'milestones', $postID ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!upstream_are_tasks_disabled($postID) && !upstream_disable_tasks()): ?>
    <div class="<?php echo esc_attr( $width ); ?> col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-wrench"></i>
            </div>
            <div class="count">
                <span><?php echo number_format_i18n($t_count); ?></span> <small><?php echo $t_count === 1 ? $task : $tasks; ?></small>
            </div>
            <h3><?php echo upstream_count_total_open( 'tasks', $postID ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <?php endif; ?>

    <?php if( !upstream_disable_bugs() && !upstream_are_bugs_disabled()) { ?>
    <div class="col-lg-4 col-md-4 col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-bug"></i>
            </div>
            <div class="count">
                <span><?php echo number_format_i18n($b_count); ?></span> <small><?php echo $b_count === 1 ? $bug : $bugs; ?></small>
            </div>
            <h3><?php echo upstream_count_total_open( 'bugs', $postID ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <?php } ?>
