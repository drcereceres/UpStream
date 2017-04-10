<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>



	<div class="col-md-9 col-sm-9 col-xs-12 discussion-panel">
		<div class="x_panel">
			<div class="x_title">
				<h2><i class="fa fa-comments"></i> <?php printf( __( '%s Discussion', 'upstream' ), upstream_project_label() ); ?></h2>
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