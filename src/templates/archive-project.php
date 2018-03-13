<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

/**
 * The Template for displaying all projects
 *
 * This template can be overridden by copying it to wp-content/themes/yourtheme/upstream/archive-project.php.
 */

upstream_get_template_part('global/header.php');
upstream_get_template_part('global/sidebar.php');
upstream_get_template_part('global/top-nav.php');

$user = upstream_user_data(@$_SESSION['upstream']['user_id']);

$projects = isset($user['projects']) && !empty($user['projects']) ? $user['projects'] : array();
$projectsCount = count($projects);

$areClientsEnabled = !is_clients_disabled();
$clients = array();

$l = array(
  'LB_PROJECTS'   => upstream_project_label_plural(),
  'LB_PROJECT'    => upstream_project_label(),
  'LB_TITLE'      => __('Title', 'upstream'),
  'LB_CLIENT'     => upstream_client_label(),
  'LB_CLIENTS'    => upstream_client_label_plural(),
  'LB_ENDS_AT'    => __('Ends at', 'upstream'),
  'LB_STATUS'     => __('Status', 'upstream'),
  'LB_STATUSES'   => __('Statuses', 'upstream'),
  'LB_NONE_UCF'   => __('None', 'upstream'),
  'LB_NONE'       => __('none', 'upstream'),
  'LB_CATEGORIES' => __('Categories'),
  'LB_COMPLETE'   => __('%s Complete', 'upstream')
);

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

        $data->categories = array();
        $categories = (array)wp_get_object_terms($data->id, 'project_category');
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                $data->categories[$category->term_id] = $category->name;
            }
        }
        unset($categories);

        $data->timeframe = $data->startDate;
        if (!empty($data->endDate)) {
            if (!empty($data->timeframe)) {
                $data->timeframe .= ' - ';
            } else {
                $data->timeframe = '<i>' . $l['LB_ENDS_AT'] . '</i>';
            }

            $data->timeframe .= $data->endDate;
        }

        $rowset[] = $data;
    }

    $projects = $rowset;
    unset($rowset);
}

$statuses = upstream_project_statuses_colors();

$categories = (array)get_terms(array(
    'taxonomy'   => 'project_category',
    'hide_empty' => false
));
?>

<div class="right_col" role="main">
  <div class="">
    <div class="row">
      <div class="col-md-12">
        <div class="x_panel">
          <div class="x_title">
            <h2><i class="fa fa-briefcase"></i> <?php echo $l['LB_PROJECTS']; ?></h2>
            <ul class="nav navbar-right panel_toolbox">
              <li>
                <a class="collapse-link">
                  <i class="fa fa-chevron-up"></i>
                </a>
              </li>
              <?php do_action('upstream_project_project_top_right'); ?>
            </ul>
          <div class="clearfix"></div>
        </div>
        <div class="x_content">
          <?php if (count($projects) > 0): ?>
          <div class="c-data-table table-responsive">
            <form class="form-inline c-data-table__filters" data-target="#projects">
              <div class="hidden-xs">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-search"></i>
                    </div>
                    <input type="search" class="form-control" placeholder="<?php echo $l['LB_TITLE']; ?>" data-column="title" data-compare-operator="contains">
                  </div>
                </div>
                <div class="form-group">
                  <div class="btn-group">
                    <a href="#projects-filters" role="button" class="btn btn-default btn-xs" data-toggle="collapse" aria-expanded="false" aria-controls="projects-filters">
                      <i class="fa fa-filter"></i> <?php _e('Toggle Filters', 'upstream'); ?>
                    </a>
                    <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa fa-download"></i> <?php _e('Export', 'upstream'); ?>
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li>
                        <a href="#" data-action="export" data-type="txt">
                          <i class="fa fa-file-text-o"></i>&nbsp;&nbsp;<?php _e('Plain Text', 'upstream'); ?>
                        </a>
                      </li>
                      <li>
                        <a href="#" data-action="export" data-type="csv">
                          <i class="fa fa-file-code-o"></i>&nbsp;&nbsp;<?php _e('CSV', 'upstream'); ?>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="visible-xs">
                <div>
                  <a href="#projects-filters" role="button" class="btn btn-default btn-xs" data-toggle="collapse" aria-expanded="false" aria-controls="projects-filters">
                    <i class="fa fa-filter"></i> <?php _e('Toggle Filters', 'upstream'); ?>
                  </a>
                  <div class="btn-group">
                    <button type="button" class="btn btn-default dropdown-toggle btn-xs" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fa fa-download"></i> <?php _e('Export', 'upstream'); ?>
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                      <li>
                        <a href="#" data-action="export" data-type="txt">
                          <i class="fa fa-file-text-o"></i>&nbsp;&nbsp;<?php _e('Plain Text', 'upstream'); ?>
                        </a>
                      </li>
                      <li>
                        <a href="#" data-action="export" data-type="csv">
                          <i class="fa fa-file-code-o"></i>&nbsp;&nbsp;<?php _e('CSV', 'upstream'); ?>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div id="projects-filters" class="collapse">
                <div class="form-group visible-xs">
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-search"></i>
                    </div>
                    <input type="search" class="form-control" placeholder="<?php echo $l['LB_TITLE']; ?>" data-column="title" data-compare-operator="contains">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-user"></i>
                    </div>
                    <input type="search" class="form-control" placeholder="<?php echo $l['LB_CLIENTS']; ?>" data-column="client" data-compare-operator="contains">
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-bookmark"></i>
                    </div>
                    <select class="form-control o-select2" data-column="status" data-placeholder="<?php echo $l['LB_STATUS']; ?>" multiple>
                      <option value></option>
                      <option value="__none__"><?php echo $l['LB_NONE_UCF']; ?></option>
                      <optgroup label="<?php echo $l['LB_STATUSES'] ?>">
                        <?php foreach ($statuses as $statusName => $statusColor): ?>
                        <option value="<?php echo $statusName; ?>"><?php echo $statusName; ?></option>
                        <?php endforeach; ?>
                      </optgroup>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-addon">
                      <i class="fa fa-tags"></i>
                    </div>
                    <select class="form-control o-select2" data-column="categories" data-placeholder="<?php echo $l['LB_CATEGORIES']; ?>" multiple data-compare-operator="contains">
                      <option value></option>
                      <option value="__none__"><?php echo $l['LB_NONE_UCF']; ?></option>
                      <optgroup label="<?php echo $l['LB_CATEGORIES'] ?>">
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category->term_id; ?>"><?php echo esc_html($category->name); ?></option>
                        <?php endforeach; ?>
                      </optgroup>
                    </select>
                  </div>
                </div>
              </div>
            </form>
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
                    <?php echo $l['LB_PROJECT']; ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <?php if ($areClientsEnabled): ?>
                  <th class="is-clickable is-orderable" data-column="client" role="button">
                    <?php echo $l['LB_CLIENT']; ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th>
                    <?php printf(__('%s Users', 'upstream'), $l['LB_CLIENT']); ?>
                  </th>
                  <?php endif; ?>
                  <th>
                    <?php printf(__('%s Members', 'upstream'), $l['LB_PROJECT']); ?>
                  </th>
                  <th class="is-clickable is-orderable" data-column="progress" role="button">
                    <?php _e('Progress', 'upstream'); ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th class="is-clickable is-orderable" data-column="status" role="button">
                    <?php echo $l['LB_STATUS']; ?>
                    <span class="pull-right o-order-direction">
                      <i class="fa fa-sort"></i>
                    </span>
                  </th>
                  <th style="max-width: 250px;">
                    <?php echo $l['LB_CATEGORIES']; ?>
                  </th>
                </tr>
              </thead>
              <tbody>
                <?php
                $isProjectIndexOdd = true;
                foreach ($projects as $projectIndex => $project): ?>
                <tr class="t-row-<?php echo $isProjectIndexOdd ? 'odd' : 'even'; ?>" data-id="<?php echo $project->id; ?>">
                  <td data-column="title" data-value="<?php echo esc_attr($project->title); ?>">
                    <a href="<?php echo $project->permalink; ?>">
                      <i class="fa fa-link"></i>
                      <?php echo $project->title; ?>
                    </a><br/>
                    <small><?php echo $project->timeframe; ?></small>
                  </td>
                  <?php if ($areClientsEnabled): ?>
                  <td data-column="client" data-value="<?php echo $project->clientName !== null ? esc_attr($project->clientName) : '__none__'; ?>">
                    <?php if ($project->clientName !== null): ?>
                      <?php echo esc_html($project->clientName); ?>
                    <?php else: ?>
                      <i class="s-text-color-gray"><?php echo $l['LB_NONE']; ?></i>
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
                        <span class="sr-only"><?php printf($l['LB_COMPLETE'], $project->progress . '%'); ?></span>
                      </div>
                    </div>
                    <small><?php printf($l['LB_COMPLETE'], $project->progress . '%'); ?></small>
                  </td>
                  <td data-column="status" data-value="<?php echo $project->status !== null ? esc_attr($project->status['status']) : '__none__'; ?>">
                    <?php if ($project->status !== null): ?>
                      <span class="label up-o-label" style="background-color: <?php echo esc_attr($project->status['color']); ?>;"><?php echo esc_html($project->status['status']); ?></span>
                    <?php else: ?>
                      <i class="s-text-color-gray"><?php echo $l['LB_NONE']; ?></i>
                    <?php endif; ?>
                  </td>
                  <td data-column="categories" data-value="<?php echo count($project->categories) ? esc_attr(implode(',', array_keys($project->categories))) : '__none__'; ?>">
                    <?php if (count($project->categories) > 0): ?>
                      <?php echo esc_attr(implode(', ', array_values($project->categories))); ?>
                    <?php else: ?>
                      <i class="s-text-color-gray"><?php echo $l['LB_NONE']; ?></i>
                    <?php endif;?>
                  </td>
                </tr>
                <?php
                $isProjectIndexOdd = !$isProjectIndexOdd;
                endforeach; ?>
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
