<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

$user_id = (int)get_current_user_id();
$project_id = (int)upstream_post_id();
$progressValue = upstream_project_progress();

$milestonesCounts = array(
  'mine'        => 0,
  'in_progress' => 0,
  'finished'    => 0,
  'total'       => 0
);

$milestones = get_post_meta($project_id, '_upstream_project_milestones');
$milestones = !empty($milestones) ? $milestones[0] : array();
$milestonesCounts['total'] = count($milestones);
foreach ($milestones as $milestone) {
  if (isset($milestone['assigned_to']) && (int)$milestone['assigned_to'] === $user_id) {
    $milestonesCounts['mine']++;
  }

  if (isset($milestone['progress'])) {
    if ((float)$milestone['progress'] < 100) {
      $milestonesCounts['in_progress']++;
    } else {
      $milestonesCounts['finished']++;
    }
  }
}

$tasksCounts = array(
  'mine'        => 0,
  'in_progress' => 0,
  'finished'    => 0,
  'total'       => 0
);

$tasks = get_post_meta($project_id, '_upstream_project_tasks');
$tasks = !empty($tasks) ? $tasks[0] : array();
$tasksCounts['total'] = count($tasks);
foreach ($tasks as $task) {
  if (isset($task['assigned_to']) && (int)$task['assigned_to'] === $user_id) {
    $tasksCounts['mine']++;
  }

  if (isset($task['status'])) {
    if ($task['status'] === 'Completed' || (isset($task['progress']) && (float)$task['progress'] === 100)) {
      $tasksCounts['finished']++;
    } else {
      $tasksCounts['in_progress']++;
    }
  } else {
    $tasksCounts['in_progress']++;
  }
}

$bugsCounts = array(
  'mine'        => 0,
  'in_progress' => 0,
  'finished'    => 0,
  'total'       => 0
);

$bugs = get_post_meta($project_id, '_upstream_project_bugs');
$bugs = !empty($bugs) ? $bugs[0] : array();
$bugsCounts['total'] = count($bugs);
foreach ($bugs as $bug) {
  if (isset($bug['assigned_to']) && (int)$bug['assigned_to'] === $user_id) {
    $bugsCounts['mine']++;
  }

  if (isset($bug['status'])) {
    if ($bug['status'] === 'Completed') {
      $bugsCounts['finished']++;
    } else if ($bug['status'] === 'In Progress' || $bug['status'] === 'Overdue' || empty($bug['status'])) {
      $bugsCounts['in_progress']++;
    }
  } else {
    $bugsCounts['in_progress']++;
  }
}
?>

<div>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <span class="label label-primary" title="Not completed Milestones"><?php echo $milestonesCounts['in_progress']; ?></span> Milestones
        </h3>
        <ul>
          <li>
            <strong><?php echo $milestonesCounts['mine']; ?></strong> assigned to me
          </li>
          <li>
            <strong><?php echo $milestonesCounts['finished']; ?></strong> finished
          </li>
          <li class="text-muted">
            <strong><?php echo $milestonesCounts['total']; ?></strong> in total
          </li>
        </ul>
        <i class="fa fa-flag"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <span class="label label-primary" title="Not completed Tasks (OPEN: status none, in progress and overdue)"><?php echo $tasksCounts['in_progress']; ?></span> Tasks
        </h3>
        <ul>
          <li>
            <strong><?php echo $tasksCounts['mine']; ?></strong> assigned to me
          </li>
          <li>
            <strong><?php echo $tasksCounts['finished']; ?></strong> finished
          </li>
          <li class="text-muted">
            <strong><?php echo $tasksCounts['total']; ?></strong> in total
          </li>
        </ul>
        <i class="fa fa-wrench"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default c-overall-item-card">
      <div class="panel-body">
        <h3 style="margin-top: 0;">
          <span class="label label-primary" title="Not completed Bugs"><?php echo $bugsCounts['in_progress']; ?></span> Bugs
        </h3>
        <ul>
          <li>
            <strong><?php echo $bugsCounts['mine']; ?></strong> assigned to me
          </li>
          <li>
            <strong><?php echo $bugsCounts['finished']; ?></strong> completed
          </li>
          <li class="text-muted">
            <strong><?php echo $bugsCounts['total']; ?></strong> in total
          </li>
        </ul>
        <i class="fa fa-bug"></i>
      </div>
    </div>
  </div>
</div>
