<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
} ?>


	<div class="col-md-3 col-sm-3 col-xs-12 details-panel">
		
		<div class="x_panel">
			
			<div class="x_title">
				<h2><?php printf( __( '%s Details', 'upstream' ), upstream_project_label() ); ?></h2>
				<ul class="nav navbar-right panel_toolbox">
					<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				</ul>
				<div class="clearfix"></div>
			</div>

			<div class="x_content">

				<div class="project_detail">
					
					<small><?php echo upstream_project_progress(); ?><?php _e( '% Complete', 'upstream' ); ?></small>
					<div class="progress progress_sm">
						<div class="progress-bar bg-green" role="progressbar" data-transitiongoal="<?php echo upstream_project_progress(); ?>"></div>
					</div>

					<?php if ( upstream_project_start_date() || upstream_project_end_date() ) { ?>
						<p class="title"><?php _e( 'Timeframe', 'upstream' ); ?></p>
						<p><?php echo upstream_format_date( upstream_project_start_date() ); ?> - <?php echo upstream_format_date( upstream_project_end_date() ); ?></p>
					<?php } ?>

					<?php if ( upstream_project_client_name() ) { ?>
						<p class="title"><?php echo upstream_client_label(); ?></p>
						<p><?php echo upstream_project_client_name(); ?></p>
					<?php } ?>


					<p class="title"><?php printf( __( '%s Users', 'upstream' ), upstream_client_label() ); ?></p>
					<?php upstream_output_client_users(); ?>


					<?php if ( upstream_project_owner_id() ) { ?>
						<p class="title"><?php printf( __( '%s Owner', 'upstream' ), upstream_project_label() ); ?></p>
						<ul class="list-inline">
					        <li><?php echo upstream_user_avatar( upstream_project_owner_id() ); ?></li>
					    </ul>
					<?php } ?>


					<p class="title"><?php printf( __( '%s Members', 'upstream' ), upstream_project_label() ); ?></p>
					<?php upstream_output_project_members(); ?>


					<div class="files">
						<p class="title"><?php printf( __( '%s Files', 'upstream' ), upstream_project_label() ); ?></p>
					    <div class="project_files">
					        <?php echo upstream_output_file_list(); ?>
					    </div>
					</div>
					
				</div>

			</div>
		</div>
	</div>
