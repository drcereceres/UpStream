<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

$project_id = (int)upstream_post_id();
$project = getUpStreamProjectDetailsById($project_id);

$projectTimeframe = '<i class="text-muted">(' . __('none', 'upstream') . ')</i>';
$projectDateStartIsNotEmpty = $project->dateStart > 0;
$projectDateEndIsNotEmpty = $project->dateEnd > 0;
if ($projectDateStartIsNotEmpty || $projectDateEndIsNotEmpty) {
  if (!$projectDateEndIsNotEmpty) {
    $projectTimeframe = '<i class="text-muted">' . __('Start Date', 'upstream') . ': </i>' . upstream_format_date($project->dateStart);
  } else if (!$projectDateStartIsNotEmpty) {
    $projectTimeframe = '<i class="text-muted">' . __('End Date', 'upstream') . ': </i>' . upstream_format_date($project->dateEnd);
  } else {
    $projectTimeframe = upstream_format_date($project->dateStart) . ' - ' . upstream_format_date($project->dateEnd);
  }
}

$pluginOptions = get_option('upstream_general');
$collapseDetails = isset($pluginOptions['collapse_project_details']) && (bool)$pluginOptions['collapse_project_details'] === true;
?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
  <div class="x_panel details-panel">
    <div class="x_title">
      <h2><?php printf('<i class="fa fa-info-circle"></i> ' . __('%s Details', 'upstream'), upstream_project_label()); ?></h2>
      <ul class="nav navbar-right panel_toolbox">
        <li>
          <a class="collapse-link"><i class="fa fa-chevron-<?php echo $collapseDetails ? 'down' : 'up'; ?>"></i></a>
        </li>
      </ul>
      <div class="clearfix"></div>
    </div>
    <?php include 'overview.php'; ?>
    <div class="x_content" style="display: <?php echo $collapseDetails ? 'none' : 'block'; ?>;">
      <div class="row">
        <div class="col-md-4">
          <p class="title"><?php _e('Timeframe', 'upstream'); ?></p>
          <span><?php echo $projectTimeframe; ?></span>
        </div>
        <div class="col-md-4">
          <p class="title"><?php _e('Client', 'upstream'); ?></p>
          <span><?php echo $project->client_id > 0 && !empty($project->clientName) ? $project->clientName : '<i class="text-muted">(' . __('none', 'upstream') . ')</i>' ; ?></span>
        </div>
        <div class="col-md-4">
          <p class="title"><?php _e('Progress', 'upstream'); ?></p>
          <span><?php echo $project->progress; ?>% <?php _e('complete', 'upstream'); ?></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4">
          <p class="title"><?php _e('Owner', 'upstream'); ?></p>
          <span><?php echo $project->owner_id > 0 ? upstream_user_avatar($project->owner_id) : '<i class="text-muted">(' . __('none', 'upstream') . ')</i>'; ?></span>
        </div>
        <div class="col-md-4">
          <p class="title"><?php _e('Client Users', 'upstream'); ?></p>
          <?php if (count($project->clientUsers) > 0): ?>
          <?php upstream_output_client_users() ?>
          <?php else: ?>
          <span><i class="text-muted">(<?php _e('none', 'upstream'); ?>)</i></span>
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <p class="title"><?php _e('Members', 'upstream'); ?></p>
          <?php upstream_output_project_members(); ?>
        </div>
      </div>
      <?php if (!empty($project->description)): ?>
      <div>
        <p class="title"><?php _e('Description'); ?></p>
        <blockquote style="font-size: 1em;"><?php echo $project->description; ?></blockquote>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
