<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

/**
 * The Template for displaying all projects
 *
 * This template can be overridden by copying it to wp-content/themes/yourtheme/upstream/archive-project.php.
 */

upstream_get_template_part('global/header.php');
// upstream_get_template_part('global/sidebar.php');
upstream_get_template_part('global/top-nav.php');

$user = upstream_user_data(@$_SESSION['upstream']['user_id']);

$projects = isset($user['projects']) && !empty($user['projects']) ? $user['projects'] : array();
$projectsCount = count($projects);

$areClientsEnabled = !is_clients_disabled();
$clients = array();

if ($projectsCount > 0 && $areClientsEnabled) {
    $rowset = array();
    foreach ($projects as $project) {
        $data = new stdClass();
        $data->id = $project->ID;
        $data->startDateTimestamp = (int)upstream_project_start_date($data->id);
        $data->startDate = (string)upstream_format_date($data->startDateTimestamp);
        $data->endDateTimestamp = (int)upstream_project_end_date($data->id);
        $data->endDate = (string)upstream_format_date($data->endDateTimestamp);
        $data->permalink = esc_url(get_the_permalink($data->id));
        $data->progress = (float)upstream_project_progress($data->id);
        $data->title = esc_html($project->post_title);
        $data->slug = esc_html($project->post_name);

        $data->clientName = $areClientsEnabled ? trim(upstream_project_client_name($data->id)) : null;
        $data->clientName = strlen($data->clientName) > 0 ? $data->clientName : null;

        $data->status = upstream_project_status_color($data->id);
        $data->status = is_array($data->status) && !empty($data->status['status']) ? $data->status : null;

        $data->timeframe = $data->startDate;
        if (!empty($data->endDate)) {
            if (!empty($data->timeframe)) {
                $data->timeframe .= ' - ';
            } else {
                $data->timeframe = '<i>' . __('Ends at', 'upstream') . '</i>';
            }

            $data->timeframe .= $data->endDate;
        }

        $rowset[] = $data;
    }

    $projects = $rowset;
    unset($rowset);
}

var_dump($projects);
?>

<div class="right_col" role="main">
  <div class="">
    <div class="row">
      <div class="col-md-12">
        <div class="x_panel">
          <div class="x_title">
            <h2><?php echo upstream_project_label_plural(); ?></h2>
            <ul class="nav navbar-right panel_toolbox">
              <li>
                <a class="collapse-link">
                  <i class="fa fa-chevron-up"></i>
                </a>
              </li>
            </ul>
          <div class="clearfix"></div>
        </div>
        <div class="x_content">
          <?php if (count($projects) > 0): ?>
          <div class="c-data-table table-responsive">
            <form class="form-inline c-data-table__filters" data-target="#projects"></form>
            <table id="projects"
              class="o-data-table table table-bordered table-responsive table-hover is-orderable"
              cellspacing="0"
              width="100%"
              data-type="project"
              data-ordered-by=""
              data-order-dir="">
              <thead>
                <tr>
                  <th class="is-clickable is-orderable" data-column="title" role="button">
                    <?php echo upstream_project_label(); ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <?php if ($areClientsEnabled): ?>
                  <th class="is-clickable is-orderable" data-column="client" role="button">
                    <?php echo upstream_client_label(); ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th>
                    <?php printf(__('%s Users', 'upstream'), upstream_client_label()); ?>
                  </th>
                  <?php endif; ?>
                  <th>
                    <?php printf(__('%s Members', 'upstream'), upstream_project_label()); ?>
                  </th>
                  <th class="is-clickable is-orderable" data-column="progress" role="button">
                    <?php _e('Progress', 'upstream'); ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th class="is-clickable is-orderable" data-column="status" role="button">
                    <?php _e('Status', 'upstream'); ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th>
                    <?php _e('View', 'upstream'); ?>
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($projects as $project): ?>
                <tr data-id="<?php echo $project->id; ?>">
                  <td data-column="title" data-value="<?php echo esc_attr($project->title); ?>">
                    <a href="<?php echo $project->permalink; ?>">
                      <?php echo $project->title; ?>
                    </a>
                  </td>
                  <?php if ($areClientsEnabled): ?>
                  <td data-column="client" data-value="<?php echo $project->clientName !== null ? esc_attr($project->clientName) : '__none__'; ?>">
                    <?php if ($project->clientName !== null): ?>
                      <?php echo esc_html($project->clientName); ?>
                    <?php else: ?>
                      <i class="s-text-color-gray"><?php _e('none', 'upstream'); ?></i>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php upstream_output_client_users($project->id); ?>
                  </td>
                  <?php endif; ?>
                  <td>
                    <?php upstream_output_project_members($project->id); ?>
                  </td>
                  <td data-column="progress" data-value="<?php echo $project->progress; ?>">
                    <div class="progress" style="margin-bottom: 0; height: 10px;">
                      <div class="progress-bar<?php echo $project->progress >= 100 ? ' progress-bar-success' : ""; ?>" role="progressbar" aria-valuenow="<?php echo $project->progress; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $project->progress; ?>%;">
                        <span class="sr-only"><?php printf(__('%s Complete', 'upstream'), $project->progress . '%'); ?></span>
                      </div>
                    </div>
                    <small><?php printf(__('%s Complete', 'upstream'), $project->progress . '%'); ?></small>
                  </td>
                  <td data-column="status" data-value="<?php echo $project->status !== null ? esc_attr($project->status['status']) : ''; ?>">
                    <?php if ($project->status !== null): ?>
                      <span class="label" style="border: none; background-color: <?php echo esc_attr($project->status['color']); ?>;"><?php echo esc_html($project->status['status']); ?></span>
                    <?php else: ?>
                      <i class="s-text-color-gray"><?php _e('none', 'upstream'); ?></i>
                    <?php endif; ?>
                  </td>
                  <td>
                    <a href="<?php echo $project->permalink; ?>" class="btn btn-primary btn-xs">
                      <?php _e('View', 'upstream'); ?>
                      <i class="fa fa-chevron-right"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <p><?php _e("It seems that you're not participating in any project right now.", 'upstream'); ?></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php do_action('upstream:frontend.renderAfterProjectsList'); ?>
</div>

<?php
do_action('upstream_after_project_list_content');

upstream_get_template_part( 'global/footer.php' );
