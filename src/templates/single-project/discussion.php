<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
} ?>


<?php if (!upstream_disable_discussions() && !upstream_are_comments_disabled()): ?>
    <div class="col-md-12 col-sm-12 col-xs-12 discussion-panel">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-comments"></i> <?php _e('Discussion', 'upstream'); ?></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                    <?php do_action( 'upstream_project_discussion_top_right' ) ?>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <ul class="discussion">
                    <?php upstream_output_comments( get_the_ID() ); ?>
                </ul>
                <?php do_action( 'upstream_project_discussion_bottom' ); ?>
            </div>
        </div>
    </div>
<?php endif; ?>
