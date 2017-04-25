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
?>

    <?php if (!empty($projectDescription)): ?>
    <div class="description col-xs-12">
        <p><?php echo $projectDescription; ?></p>
    </div>
    <?php endif; ?>

    <div class="<?php echo esc_attr( $width ); ?> col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-flag"></i>
            </div>
            <div class="count"><?php printf( _n( "<span>%s</span> <small>$milestone</small>", "<span>%s</span> <small>$milestones</small>", $m_count, 'upstream' ), number_format_i18n( $m_count ) ); ?></div>
            <h3><?php echo upstream_count_total_open( 'milestones', get_the_ID() ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <div class="<?php echo esc_attr( $width ); ?> col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-wrench"></i>
            </div>
            <div class="count"><?php printf( _n( "<span>%s</span> <small>$task</small>", "<span>%s</span> <small>$tasks</small>", $t_count, 'upstream' ), number_format_i18n( $t_count ) ); ?></div>
            <h3><?php echo upstream_count_total_open( 'tasks', get_the_ID() ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <?php if( ! upstream_disable_bugs() ) { ?>
    <div class="col-lg-4 col-md-4 col-xs-12">
        <div class="tile-stats">
            <div class="icon"><i class="fa fa-bug"></i>
            </div>
            <div class="count"><?php printf( _n( "<span>%s</span> <small>$bug</small>", "<span>%s</span> <small>$bugs</small>", $b_count, 'upstream' ), number_format_i18n( $b_count ) ); ?></div>
            <h3><?php echo upstream_count_total_open( 'bugs', get_the_ID() ); ?> <?php _e( 'Open', 'upstream' ); ?></h3>
        </div>
    </div>
    <?php } ?>
