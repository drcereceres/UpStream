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

$user = upstream_user_data(@$_SESSION['upstream']['user_id']);
$projects = isset($user['projects']) && !empty($user['projects']) ? $user['projects'] : array();

$areClientsDisabled = is_clients_disabled();
?>

<!-- page content -->
<div class="right_col" role="main">

    <div class="">

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
                    <?php if (count($projects) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped projects">
                            <thead>
                                <tr>
                                    <th style="width: 18%"><?php esc_html_e( upstream_project_label() ); ?></th>
                                    <?php if (!$areClientsDisabled): ?>
                                    <th style="width: 10%"><?php esc_html_e( upstream_client_label() ); ?></th>
                                    <th><?php esc_html_e( sprintf( __( '%s Users', 'upstream' ), upstream_client_label() ) ); ?></th>
                                    <?php endif; ?>
                                    <th><?php printf( __( '%s Members', 'upstream' ), upstream_project_label() ); ?></th>
                                    <th><?php _e( 'Progress', 'upstream' ); ?></th>
                                    <th><?php _e( 'Status', 'upstream' ); ?></th>
                                    <th><?php _e( 'View', 'upstream' ); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project_id => $project):
                                $startDate = (string) upstream_format_date(upstream_project_start_date($project_id));
                                $endDate = (string) upstream_format_date(upstream_project_end_date($project_id));
                                $permalink = esc_url(get_the_permalink($project_id));
                                $progress = (float)upstream_project_progress($project_id);

                                $timeframe = $startDate;
                                if (!empty($endDate)) {
                                    if (!empty($timeframe)) {
                                        $timeframe .= ' - ';
                                    } else {
                                        $timeframe = '<i>' . __('Ends at', 'upstream') . '</i> ';
                                    }

                                    $timeframe .= $endDate;
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $permalink; ?>"><?php esc_html_e( $project->post_title ); ?></a>
                                        <?php if (!empty($timeframe)): ?>
                                        <br />
                                        <small><?php echo $timeframe; ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <?php if (!$areClientsDisabled): ?>
                                    <td>
                                        <?php esc_html_e( upstream_project_client_name( $project_id ) ); ?>
                                    </td>
                                    <td>
                                        <?php upstream_output_client_users( $project_id ); ?>
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <?php upstream_output_project_members( $project_id ); ?>
                                    </td>
                                    <td>
                                        <?php $progressString = $progress . __('% Complete', 'upstream'); ?>
                                        <div class="progress" style="margin-bottom: 0; height: 10px;">
                                            <div class="progress-bar<?php echo $progress >= 100 ? ' progress-bar-success' : ""; ?>" role="progressbar" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress; ?>%;">
                                                <span class="sr-only"><?php echo $progressString; ?></span>
                                            </div>
                                        </div>
                                        <small><?php echo $progressString; ?></small>
                                    </td>
                                    <td>
                                        <?php
                                            $status = upstream_project_status_color( $project_id );
                                            if( $status['status'] ) {
                                        ?>
                                            <button type="button" class="btn btn-success btn-xs" style="border: none;background-color:<?php echo esc_attr( $status['color'] ); ?>"><?php echo $status['status'] ?></button>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo $permalink; ?>" class="btn btn-primary btn-xs"><?php _e( 'View', 'upstream' ); ?> <i class="fa fa-chevron-right"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p><?php echo __("It seems that you're not participating in any project right now.", 'upstream'); ?></p>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php do_action('upstream:frontend.renderAfterProjectsList'); ?>
</div>
<!-- /page content -->

<?php
/**
 * upstream_after_project_content hook.
 *
 */
do_action( 'upstream_after_project_list_content' ); ?>

<?php upstream_get_template_part( 'global/footer.php' ); ?>
