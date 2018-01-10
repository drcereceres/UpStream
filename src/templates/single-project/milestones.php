<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

if (!upstream_are_milestones_disabled()
    && !upstream_disable_milestones()):

$collapseBox = isset($pluginOptions['collapse_project_milestones'])
    && (bool)$pluginOptions['collapse_project_milestones'] === true;
$rowset = upstream_project_milestones(); // @todo: optimize

$users = upstreamGetUsersMap();

$itemType = 'milestone';
$currentUserId = get_current_user_id();
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
        <form class="form-inline c-data-table__filters" data-target="#milestones" style="margin-bottom: 15px;">
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-search"></i>
              </div>
              <input type="search" class="form-control" placeholder="<?php echo upstream_milestone_label(); ?>" data-column="milestone" data-compare-operator="contains">
            </div>
          </div>
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-user"></i>
              </div>
              <select id="kluster" class="form-control" data-column="assigned_to" data-placeholder="Assignee">
                <option value></option>
                <option value="__none__">Nobody</option>
                <option value="<?php echo $currentUserId; ?>"><?php _e('Me', 'upstream'); ?></option>
                <optgroup label="<?php _e('Users'); ?>">
                  <?php foreach ($users as $user_id => $userName): ?>
                  <?php if ($user_id === $currentUserId) continue; ?>
                  <option value="<?php echo $user_id; ?>"><?php echo $userName; ?></option>
                  <?php endforeach; ?>
                </optgroup>
              </select>
            </div>
          </div>
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" class="form-control o-datepicker" placeholder="Start Date" id="milestones-filter-start_date">
            </div>
            <input type="hidden" id="milestones-filter-start_date_timestamp" data-column="start_date" data-compare-operator=">=">
          </div>
          <div class="form-group">
            <div class="input-group">
              <div class="input-group-addon">
                <i class="fa fa-calendar"></i>
              </div>
              <input type="text" class="form-control o-datepicker" placeholder="End Date" id="milestones-filter-end_date">
            </div>
            <input type="hidden" id="milestones-filter-end_date_timestamp" data-column="end_date" data-compare-operator="<=">
          </div>
          <div class="form-group">
            <div class="btn-group">
              <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fa fa-download"></i>
                <span class="caret"></span>
              </button>
              <ul class="dropdown-menu dropdown-menu-right">
                <li>
                  <a href="#" data-action="export" data-type="txt">TXT</a>
                </li>
                <li>
                  <a href="#" data-action="export" data-type="csv">CSV</a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                  <a href="#">Copy to clipboard</a>
                </li>
              </ul>
            </div>
          </div>
        </form>
        <table
          summary="@todo: overview of table contents"
          id="milestones"
          class="o-data-table table table-striped table-bordered table-responsive is-orderable"
          cellspacing="0"
          width="100%"
          data-type="milestone"
          data-ordered-by="milestone"
          data-order-dir="DESC"
          >
          <caption>@todo: visible to screen readers only: table title</caption>
          <thead>
            <?php // echo upstream_output_table_header($itemType); ?>
            <tr scope="row">
              <th scope="col" class="is-clickable is-orderable" data-column="milestone" role="button">
                Milestone
                <span class="pull-right o-order-direction">
                  <i class="fa fa-sort"></i>
                </span>
              </th>
              <th scope="col" class="is-orderable" data-column="assigned_to" role="button">
                Assigned To
                <span class="pull-right o-order-direction">
                  <i class="fa fa-sort"></i>
                </span>
              </th>
              <th scope="col" class="" data-column="tasks">Tasks</th>
              <th scope="col" class="is-orderable" data-column="progress" role="button">
                Progress
                <span class="pull-right o-order-direction">
                  <i class="fa fa-sort"></i>
                </span>
              </th>
              <th scope="col" class="is-orderable" data-column="start_date" role="button">
                Start Date
                <span class="pull-right o-order-direction">
                  <i class="fa fa-sort"></i>
                </span>
              </th>
              <th scope="col" class="is-orderable" data-column="end_date" role="button">
                End Date
                <span class="pull-right o-order-direction">
                  <i class="fa fa-sort"></i>
                </span>
              </th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rowset as $row): ?>
            <tr class="is-expandable is-filtered" data-id="<?php echo $row['id']; ?>" aria-expanded="false">
              <td class="is-clickable" role="button">
                <i class="fa fa-angle-right" style="width: 8.36px;"></i>&nbsp;
                <span data-column="milestone" data-value="<?php echo $row['milestone']; ?>"><?php echo $row['milestone']; ?></span>
              </td>
              <td data-column="assigned_to" data-value="<?php echo $row['assigned_to']; ?>">
                <?php if (isset($row['assigned_to'])
                    && is_numeric($row['assigned_to'])
                    && (int)$row['assigned_to'] > 0
                ): ?>
                    <?php if (isset($users[$row['assigned_to']])): ?>
                    <?php echo $users[$row['assigned_to']]; ?>
                    <?php else: ?>
                    <i class="s-text-color-darkred">invalid user</i>
                    <?php endif; ?>
                <?php else: ?>
                <i class="s-text-color-gray"><?php _e('none', 'upstream'); ?></i>
                <?php endif; ?>
              </td>
              <td data-column="tasks">0 Tasks / 0 Open</td>
              <td data-column="progress" data-value="<?php echo $row['progress']; ?>"><?php echo $row['progress']; ?>%</td>
              <td data-column="start_date" data-value="<?php echo $row['start_date']; ?>"><?php echo upstream_convert_UTC_date_to_timezone($row['start_date'], false); ?></td>
              <td data-column="end_date" data-value="<?php echo $row['end_date']; ?>"><?php echo upstream_convert_UTC_date_to_timezone($row['end_date'], false); ?></td>
            </tr>
            <tr data-parent="<?php echo $row['id']; ?>" style="display: none;">
              <td colspan="6">
                <div class="hidden-xs">
                  <div class="form-group">
                    <label>Notes</label>
                    <?php
                    if (isset($row['notes'])
                        && strlen($row['notes']) > 0
                    ): ?>
                    <blockquote style="font-size: 1em; padding: 5px 10px;"><?php echo $row['notes']; ?></blockquote>
                    <?php else: ?>
                    <p>
                      <i style="color: #CCC;"><?php _e('none', 'upstream'); ?></i>
                    </p>
                    <?php endif; ?>
                  </div>
                  <div class="form-group">
                    <label>Comments</label>
                    <p>
                      <i style="color: #CCC;"><?php _e('none', 'upstream'); ?></i>
                    </p>
                  </div>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php // echo upstream_output_table_rows(get_the_ID(), $itemType); ?>
          </tbody>
        </table>
      </div>
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
<?php endif; ?>
