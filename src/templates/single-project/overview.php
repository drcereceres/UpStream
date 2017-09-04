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

<?php if ($areMilestonesEnabled || $areTasksEnabled || $areBugsEnabled): ?>
<div class="row">
  <?php if ($areMilestonesEnabled): ?>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <?php $milestoneLabelPlural = upstream_milestone_label_plural(); ?>
          <span class="label label-primary" data-toggle="tooltip" title="<?php printf('%s %s', __('Open', 'upstream'), $milestoneLabelPlural); ?>"><?php echo $milestonesCounts['open']; ?></span> <?php echo $milestoneLabelPlural; ?>
        </h3>
        <ul class="list-unstyled">
          <li>
            <strong><?php echo $milestonesCounts['mine']; ?></strong> <?php _e('assigned to me', 'upstream'); ?>
          </li>
          <li class="text-danger">
            <strong><?php echo $milestonesCounts['overdue']; ?></strong> <?php _e('overdue', 'upstream'); ?>
          </li>
          <li>
            <strong><?php echo $milestonesCounts['finished']; ?></strong> <?php _e('finished', 'upstream'); ?>
          </li>
          <li class="text-muted">
            <strong><?php echo $milestonesCounts['total']; ?></strong> <?php _e('in total', 'upstream'); ?>
          </li>
        </ul>
        <i class="fa fa-flag"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($areTasksEnabled): ?>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <?php $taskLabelPlural = upstream_task_label_plural(); ?>
          <span class="label label-primary" data-toggle="tooltip" title="<?php printf('%s %s', __('Open', 'upstream'), $taskLabelPlural); ?>"><?php echo $tasksCounts['open']; ?></span> <?php echo $taskLabelPlural; ?>
        </h3>
        <ul class="list-unstyled">
          <li>
            <strong><?php echo $tasksCounts['mine']; ?></strong> <?php _e('assigned to me', 'upstream'); ?>
          </li>
          <li class="text-danger">
            <strong><?php echo $tasksCounts['overdue']; ?></strong> <?php _e('overdue', 'upstream'); ?>
          </li>
          <li>
            <strong><?php echo $tasksCounts['closed']; ?></strong> <?php _e('closed', 'upstream'); ?>
          </li>
          <li class="text-muted">
            <strong><?php echo $tasksCounts['total']; ?></strong> <?php _e('in total', 'upstream'); ?>
          </li>
        </ul>
        <i class="fa fa-wrench"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php if ($areBugsEnabled): ?>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <?php $bugLabelPlural = upstream_bug_label_plural(); ?>
          <span class="label label-primary" data-toggle="tooltip" title="<?php printf('%s %s', __('Open', 'upstream'), $bugLabelPlural); ?>"><?php echo $bugsCounts['open']; ?></span> <?php echo $bugLabelPlural; ?>
        </h3>
        <ul class="list-unstyled">
          <li>
            <strong><?php echo $bugsCounts['mine']; ?></strong> <?php _e('assigned to me', 'upstream'); ?>
          </li>
          <li class="text-danger">
            <strong><?php echo $bugsCounts['overdue']; ?></strong> <?php _e('overdue', 'upstream'); ?>
          </li>
          <li>
            <strong><?php echo $bugsCounts['closed']; ?></strong> <?php _e('closed', 'upstream'); ?>
          </li>
          <li class="text-muted">
            <strong><?php echo $bugsCounts['total']; ?></strong> <?php _e('in total', 'upstream'); ?>
          </li>
        </ul>
        <i class="fa fa-bug"></i>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>
