<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;
?>

<?php if (!upstream_disable_discussions() && !upstream_are_comments_disabled()):
$pluginOptions = get_option('upstream_general');
$collapseBox = isset($pluginOptions['collapse_project_discussion']) && (bool)$pluginOptions['collapse_project_discussion'] === true;
$project_id = get_the_ID();
$comments = getProjectComments($project_id);
?>

<div class="col-xs-12 col-sm-12 col-md-12">
  <div class="x_panel">
    <div class="x_title">
      <h2>
        <i class="fa fa-comments"></i> <?php _e('Discussion', 'upstream'); ?>
      </h2>
      <ul class="nav navbar-right panel_toolbox">
        <li>
          <a class="collapse-link">
            <i class="fa fa-chevron-<?php echo $collapseBox ? 'down' : 'up'; ?>"></i>
          </a>
        </li>
        <?php do_action('upstream_project_discussion_top_right'); ?>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div class="x_content">
      <div class="c-discussion" data-label-empty="<?php _e('Currently no messages.', 'upstream'); ?>">
        <?php if (count($comments) === 0): ?>
          <p data-no-data><?php _e('Currently no messages.', 'upstream'); ?></p>
        <?php else:
          foreach ($comments as $rowIndex => $row) {
            upstream_frontend_output_comment($row, $rowIndex, $project_id);
          }
        endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
