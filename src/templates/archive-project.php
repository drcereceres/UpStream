<?php
/**
 * The Template for displaying all projects
 *
 * This template can be overridden by copying it to wp-content/themes/yourtheme/upstream/archive-project.php.
 *
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

upstream_get_template_part( 'global/header.php' );
upstream_get_template_part( 'global/sidebar.php' );
upstream_get_template_part( 'global/top-nav.php' );

$user = upstream_user_data(); ?>

<!-- page content -->
<div class="right_col" role="main">

    <div class="">

        <div class="page-title">
            <div class="title_left">
                <h3><?php printf( __( 'My %s', 'upstream' ), upstream_project_label_plural() ); ?></h3>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="row">

            <div class="col-md-12">

                <div class="x_panel">

                    <div class="x_title">
                        <h2><?php echo upstream_project_label_plural(); ?></h2>

                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>

                        </ul>
                    <div class="clearfix"></div>
                    </div>

                    <div class="x_content">

                    <!-- start project list -->
                    <div class="table-responsive">
                    <table class="table table-striped projects">
                        <thead>
                            <tr>
                                <th style="width: 1%">#</th>
                                <th style="width: 18%"><?php esc_html_e( upstream_project_label() ); ?></th>
                                <th style="width: 10%"><?php esc_html_e( upstream_client_label() ); ?></th>
                                <th><?php esc_html_e( sprintf( __( '%s Users', 'upstream' ), upstream_client_label() ) ); ?></th>
                                <th><?php printf( __( '%s Members', 'upstream' ), upstream_project_label() ); ?></th>
                                <th><?php _e( 'Progress', 'upstream' ); ?></th>
                                <th><?php _e( 'Status', 'upstream' ); ?></th>
                                <th><?php _e( 'View', 'upstream' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                      <?php
                      if( $user['projects'] ) :
                          foreach ( $user['projects'] as $key => $id ) { ?>

                            <tr>
                                <td>
                                    <?php esc_html_e( $id ); ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( get_the_permalink( $id ) ); ?>"><?php esc_html_e( get_the_title( $id ) ); ?></a>
                                    <br />
                                    <small><?php echo upstream_format_date( upstream_project_start_date( $id ) ); ?> - <?php echo upstream_format_date( upstream_project_end_date( $id ) ); ?></small>
                                </td>
                                <td>
                                    <?php esc_html_e( upstream_project_client_name( $id ) ); ?>
                                </td>
                                <td>
                                    <?php upstream_output_client_users( $id ); ?>
                                </td>
                                <td>
                                    <?php upstream_output_project_members( $id ); ?>
                                </td>
                                <td class="project_progress">
                                    <div class="progress progress_sm">
                                        <div class="progress-bar bg-green" role="progressbar" data-transitiongoal="<?php echo upstream_project_progress( $id ); ?>"></div>
                                    </div>
                                    <small><?php echo upstream_project_progress( $id ); ?><?php _e( '% Complete', 'upstream' ); ?></small>
                                </td>
                                <td>
                                    <?php
                                        $status = upstream_project_status_color( $id );
                                        if( $status['status'] ) {
                                    ?>
                                        <button type="button" class="btn btn-success btn-xs" style="border: none;background-color:<?php echo esc_attr( $status['color'] ); ?>"><?php echo $status['status'] ?></button>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="<?php echo get_the_permalink( $id ); ?>" class="btn btn-primary btn-xs"><?php _e( 'View', 'upstream' ); ?> <i class="fa fa-chevron-right"></i></a>
                                </td>
                            </tr>

                        <?php } endif; ?>

                        </tbody>
                    </table>
                    </div>
                    <!-- end project list -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /page content -->

<?php
/**
 * upstream_after_project_content hook.
 *
 */
do_action( 'upstream_after_project_list_content' ); ?>

<?php upstream_get_template_part( 'global/footer.php' ); ?>
