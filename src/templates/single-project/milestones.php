<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

if (!upstream_are_milestones_disabled()
    && !upstream_disable_milestones()):

$collapseBox = isset($pluginOptions['collapse_project_milestones'])
    && (bool)$pluginOptions['collapse_project_milestones'] === true;
$rowset = upstream_project_milestones(); // @todo: optimize
?>
<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="x_panel">
    <div class="x_title">
      <h2><i class="fa fa-flag"></i> <?php echo upstream_milestone_label_plural(); ?></h2>
      <ul class="nav navbar-right panel_toolbox">
        <li><a class="collapse-link"><i class="fa fa-chevron-<?php echo $collapseBox ? 'down' : 'up'; ?>"></i></a></li>
        <?php do_action( 'upstream_project_milestones_top_right' ); ?>
      </ul>
      <div class="clearfix"></div>
    </div>
    <div class="x_content" style="display: <?php echo $collapseBox ? 'none' : 'block'; ?>;">
      <div class="c-data-table table-responsive">
        <form class="form-inline c-data-table__filters" style="margin-bottom: 15px;">
          <div class="form-group">
            <input type="search" class="form-control" placeholder="Search...">
          </div>
          <div class="form-group">
            <select class="form-control">
              <option>Status</option>
            </select>
          </div>
          <div class="form-group">
            <select class="form-control">
              <option>Assignee</option>
            </select>
          </div>
          <div class="form-group">
            <input type="date" class="form-control" placeholder="Start Date">
          </div>
          <div class="form-group">
            <input type="date" class="form-control" placeholder="End Date">
          </div>
        </form>
        <table
          class="o-data-table table table-striped table-bordered table-hover is-orderable"
          cellspacing="0"
          width="100%"
          data-type="milestone"
          data-ordered-by="milestone"
          data-order-dir="DESC"
          >
          <thead>
            <tr>
              <th class="is-ordered is-orderable" data-order-dir="DESC" role="button" data-column="milestone">
                Milestone
                <span class="pull-right o-order-direction">
                  <i class="fa fa-angle-down"></i>
                </span>
              </th>
              <th class="is-orderable" role="button" data-column="assigned_to">Assigned To</th>
              <th>Tasks</th>
              <th class="is-orderable" role="button" data-column="progress">Progress</th>
              <th class="is-orderable" role="button" data-column="start_date">Start Date</th>
              <th class="is-orderable" role="button" data-column="end_date">End Date</th>
              <th class="text-center">Notes</th>
              <th class="text-center">Comments</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rowset as $row): ?>
            <tr data-id="<?php echo $row['id']; ?>">
              <td>
                <a href="#" data-toggle="up-modal" data-up-target="#milestoneModal" data-column="milestone" data-value="<?php echo $row['milestone']; ?>"><?php echo $row['milestone']; ?></a>
              </td>
              <td data-column="assigned_to" data-value="<?php echo $row['assigned_to']; ?>"><?php echo $row['assigned_to']; ?></td>
              <td data-column="tasks">0 Tasks / 0 Open</td>
              <td data-column="progress" data-value="<?php echo $row['progress']; ?>"><?php echo $row['progress']; ?>%</td>
              <td data-column="start_date" data-value="<?php echo $row['start_date']; ?>"><?php echo $row['start_date']; ?></td>
              <td data-column="end_date" data-value="<?php echo $row['end_date']; ?>"><?php echo $row['end_date']; ?></td>
              <td class="text-center">
                <i class="fa fa-<?php echo strlen($row['notes']) > 0 ? 'check' : 'times'; ?>"></i>
                <div class="hide" data-column="notes">
                  <?php echo $row['notes']; ?></td>
                </div>
              <td class="text-center">
                <i class="fa fa-comments-o"></i> <?php echo mt_rand(0, 19); ?>
                <div class="hide" data-column="comments">
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal fade" id="milestoneModal" tabindex="-1" role="dialog" aria-labelledby="milestoneModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <h4 class="modal-title" id="milestoneModalLabel">
            <i class="fa fa-flag"></i> <span data-column="milestone"></span>
          </h4>
        </div>
        <div class="modal-body">
          <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#milestoneModalTabData" aria-controls="milestoneModalTabData" role="tab" data-toggle="tab">Milestone</a></li>
            <li role="presentation">
              <a href="#milestoneModalTabComments" aria-controls="milestoneModalTabComments" role="tab" data-toggle="tab">Comments</a>
            </li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="milestoneModalTabData">
              <dl class="dl-horizontal">
                <dt>Assigned to</dt>
                <dd data-column="assigned_to">Denison Martins</dd>
                <dt>Tasks</dt>
                <dd data-column="tasks">3 Tasks / 2 Open</dd>
                <dt>Progress</dt>
                <dd data-column="progress">75%</dd>
                <dt>Start Date</dt>
                <dd data-column="start_date">Jan 16, 2018</dd>
                <dt>End Date</dt>
                <dd data-column="end_date">Feb 23, 2018</dd>
              </dl>
              <div>
                <strong>Notes</strong>
                <blockquote data-column="notes" style="font-size: 1em;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam at elit risus. Aliquam mattis felis eu turpis feugiat sagittis. Morbi eget rutrum diam, ut malesuada ipsum. Nulla a purus porta, lacinia felis quis, eleifend mi. Curabitur vel magna dolor. Proin vulputate lacus quis nibh facilisis fringilla. Suspendisse ornare mauris magna, eu imperdiet lectus dignissim vel. Vivamus auctor quam interdum tempus fermentum.</blockquote>
              </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="milestoneModalTabComments">@todo</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
