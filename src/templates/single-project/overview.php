<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

$user_id = (int)get_current_user_id();
$project_id = (int)upstream_post_id();
$progressValue = upstream_project_progress();
$currentTimestamp = time();

$areMilestonesEnabled = !upstream_are_milestones_disabled() && !upstream_disable_milestones();
if ($areMilestonesEnabled) {
    $milestonesCounts = array(
      'open'     => 0,
      'mine'     => 0,
      'overdue'  => 0,
      'finished' => 0,
      'total'    => 0
    );

    $milestones = get_post_meta($project_id, '_upstream_project_milestones');
    $milestones = !empty($milestones) ? $milestones[0] : array();
    $milestonesCounts['total'] = count($milestones);
    foreach ($milestones as $milestone) {
      if (isset($milestone['assigned_to']) && (int)$milestone['assigned_to'] === $user_id) {
        $milestonesCounts['mine']++;
      }

      if (isset($milestone['end_date']) && (int)$milestone['end_date'] > 0 && (int)$milestone['end_date'] < $currentTimestamp) {
        $milestonesCounts['overdue']++;
      }

      if (isset($milestone['progress'])) {
        if ((float)$milestone['progress'] < 100) {
          $milestonesCounts['open']++;
        } else {
          $milestonesCounts['finished']++;
        }
      }
    }
}

$areTasksEnabled = !upstream_are_tasks_disabled() && !upstream_disable_tasks();
if ($areTasksEnabled) {
    $tasksCounts = array(
      'open'    => 0,
      'mine'    => 0,
      'overdue' => 0,
      'closed'  => 0,
      'total'   => 0
    );

    $tasksOptions = get_option('upstream_tasks');
    $tasksMap = array();
    foreach ($tasksOptions['statuses'] as $task) {
      $tasksMap[$task['name']] = $task['type'];
    }
    unset($tasksOptions);

    $tasks = get_post_meta($project_id, '_upstream_project_tasks');
    $tasks = !empty($tasks) ? $tasks[0] : array();
    $tasksCounts['total'] = count($tasks);
    foreach ($tasks as $task) {
      if (isset($task['assigned_to']) && (int)$task['assigned_to'] === $user_id) {
        $tasksCounts['mine']++;
      }

      if (isset($task['end_date']) && (int)$task['end_date'] > 0 && (int)$task['end_date'] < $currentTimestamp) {
        $tasksCounts['overdue']++;
      }

      if (isset($task['status'])) {
        if (!empty($task['status']) && $tasksMap[$task['status']] === 'closed') {
          $tasksCounts['closed']++;
        } else {
          $tasksCounts['open']++;
        }
      } else {
        $tasksCounts['open']++;
      }
    }
}

$areBugsEnabled = !upstream_disable_bugs() && !upstream_are_bugs_disabled();
if ($areBugsEnabled) {
    $bugsCounts = array(
      'open'    => 0,
      'mine'    => 0,
      'overdue' => 0,
      'closed'  => 0,
      'total'   => 0
    );

    $bugsOptions = get_option('upstream_tasks');
    $bugsMap = array();
    foreach ($bugsOptions['statuses'] as $bug) {
      $bugsMap[$bug['name']] = $bug['type'];
    }
    unset($bugsOptions);

    $bugs = get_post_meta($project_id, '_upstream_project_bugs');
    $bugs = !empty($bugs) ? $bugs[0] : array();
    $bugsCounts['total'] = count($bugs);
    foreach ($bugs as $bug) {
      if (isset($bug['assigned_to']) && (int)$bug['assigned_to'] === $user_id) {
        $bugsCounts['mine']++;
      }

      if (isset($bug['due_date']) && (int)$bug['due_date'] > 0 && (int)$bug['due_date'] < $currentTimestamp) {
        $bugsCounts['overdue']++;
      }

      if (isset($bug['status'])) {
        if (!empty($bug['status']) && $bugsMap[$bug['status']] === 'closed') {
          $bugsCounts['closed']++;
        } else {
          $bugsCounts['open']++;
        }
      } else {
        $bugsCounts['open']++;
      }
    }
}
?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-7 text-right">
  <?php if ($areMilestonesEnabled || $areTasksEnabled || $areBugsEnabled): ?>
    <?php if ($areBugsEnabled): ?>
    <div class="hidden-xs hidden-sm col-md-4 col-lg-4" style="min-width: 185px;">
      <div class="panel panel-default" style="margin-bottom: 10px;">
        <div class="panel-body" style="display: flex; position: relative;">
          <div data-toggle="tooltip" title="<?php _e('Open', 'upstream'); ?>">
            <span class="label label-primary" ><?php echo $bugsCounts['open']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Assigned to me', 'upstream'); ?>">
            <span class="label label-info"><?php echo $bugsCounts['mine']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Overdue', 'upstream'); ?>">
            <span class="label label-danger"><?php echo $bugsCounts['overdue']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Closed', 'upstream'); ?>">
            <span class="label label-success"><?php echo $bugsCounts['closed']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Total', 'upstream'); ?>">
            <span class="label" style="background-color: #ecf0f1; color: #3A4E66;"><?php echo $bugsCounts['total']; ?></span>
          </div>
          <i class="fa fa-bug fa-2x" data-toggle="tooltip" title="<?php printf('%s %s', upstream_bug_label_plural(), __('Overview', 'upstream')); ?>" style="position: absolute; color: #ECF0F1; right: 8px; margin-top: -2px"></i>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($areTasksEnabled): ?>
    <div class="hidden-xs hidden-sm col-md-4 col-lg-4" style="min-width: 185px;">
      <div class="panel panel-default" style="margin-bottom: 10px;">
        <div class="panel-body" style="display: flex; position: relative;">
          <div data-toggle="tooltip" title="<?php _e('Open', 'upstream'); ?>">
            <span class="label label-primary" ><?php echo $tasksCounts['open']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Assigned to me', 'upstream'); ?>">
            <span class="label label-info"><?php echo $tasksCounts['mine']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Overdue', 'upstream'); ?>">
            <span class="label label-danger"><?php echo $tasksCounts['overdue']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Closed', 'upstream'); ?>">
            <span class="label label-success"><?php echo $tasksCounts['closed']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Total', 'upstream'); ?>">
            <span class="label" style="background-color: #ecf0f1; color: #3A4E66;"><?php echo $tasksCounts['total']; ?></span>
          </div>
          <i class="fa fa-wrench fa-2x" data-toggle="tooltip" title="<?php printf('%s %s', upstream_task_label_plural(), __('Overview', 'upstream')); ?>" style="position: absolute; color: #ECF0F1; right: 8px; margin-top: -2px"></i>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($areMilestonesEnabled): ?>
    <div class="hidden-xs hidden-sm col-md-4 col-lg-4" style="min-width: 185px;">
      <div class="panel panel-default" style="margin-bottom: 10px;">
        <div class="panel-body" style="display: flex; position: relative;">
          <div data-toggle="tooltip" title="<?php _e('Open', 'upstream'); ?>">
            <span class="label label-primary" ><?php echo $milestonesCounts['open']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Assigned to me', 'upstream'); ?>">
            <span class="label label-info"><?php echo $milestonesCounts['mine']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Overdue', 'upstream'); ?>">
            <span class="label label-danger"><?php echo $milestonesCounts['overdue']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Finished', 'upstream'); ?>">
            <span class="label label-success"><?php echo $milestonesCounts['finished']; ?></span>
          </div>
          <div data-toggle="tooltip" title="<?php _e('Total', 'upstream'); ?>">
            <span class="label" style="background-color: #ecf0f1; color: #3A4E66;"><?php echo $milestonesCounts['total']; ?></span>
          </div>
          <i class="fa fa-flag fa-2x" data-toggle="tooltip" title="<?php printf('%s %s', upstream_milestone_label_plural(), __('Overview', 'upstream')); ?>" style="position: absolute; color: #ECF0F1; right: 8px; margin-top: -2px"></i>
        </div>
      </div>
    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
