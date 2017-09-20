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
      <div class="c-comments" data-label-empty="<?php _e('Currently no messages.', 'upstream'); ?>">
        <?php if (count($comments) === 0): ?>
          <p data-no-data><?php _e('Currently no messages.', 'upstream'); ?></p>
        <?php else: ?>
          <?php foreach ($comments as $rowIndex => $row): ?>
          <?php do_action('upstream:project.discussion:before_comment', $project_id, $row); ?>
          <div class="media o-comment">
            <div class="media-left">
              <img class="media-object" src="<?php echo $row->created_by->avatar; ?>" alt="<?php echo $row->created_by->name; ?>" width="40" />
            </div>
            <div class="media-body">
              <div class="o-comment__heading">
                <div>
                  <h5 class="media-heading"><?php echo $row->created_by->name; ?></h5>
                  <time datetime="<?php echo $row->created_at->iso_8601; ?>" data-delay="500" data-toggle="tooltip" data-placement="top" title="<?php echo $row->created_at->formatted; ?>"><?php echo $row->created_at->human; ?></time>
                </div>
                <div>
                  <ul class="list-inline">
                    <?php do_action('upstream:project.discussion:comment.controls', $project_id, $row, $rowIndex); ?>
                  </ul>
                </div>
              </div>
              <div><?php echo $row->comment; ?></div>
            </div>
          </div>
          <?php do_action('upstream:project.discussion:after_comment', $project_id, $row); ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
