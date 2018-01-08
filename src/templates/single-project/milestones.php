<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

if (!upstream_are_milestones_disabled()
    && !upstream_disable_milestones()):

$collapseBox = isset($pluginOptions['collapse_project_milestones'])
    && (bool)$pluginOptions['collapse_project_milestones'] === true;
$rowset = upstream_project_milestones(); // @todo: optimize

/*
function table($attrs = array(), $columns = array(), $rowset = array(), $orderedBy = null, $orderDir = 'DESC') {
    $tableAttrs = array();

    $attrs = array_merge(array(
        'class'           => ' table table-striped table-bordered table-hover o-data-table is-orderable ',
        'cellspacing'     => 0,
        'width'           => '100%',
        'data-ordered-by' => $orderedBy,
        'data-order-dir'  => $orderDir
    ), $attrs);

    foreach ($attrs as $attrName => $attrValue) {
        $tableAttrs[] = sprintf('%s="%s"', $attrName, $attrValue);
    }

    ob_start();
    ?>
    <table <?php echo implode(' ', $tableAttrs); ?>>
      <thead>
        <tr>
          <?php foreach ($columns as $columnName => $columnSettings):
          $columnAttrs = array(
              'class' => ''
          );

          if (isset($columnSettings->isOrderable)
            && (bool)$columnSettings->isOrderable
          ) {
            $columnAttrs['class'] .= ' is-orderable';
            $columnAttrs['role'] = 'button';
          }

          if ($columnName === $orderedBy) {
            $columnAttrs['class'] .= 'is-ordered';
            $columnAttrs['data-order-dir'] = $orderDir;
          }

          $trAttrs = array();
          foreach ($columnSettings->attrs as $attrName => $attrValue):
            $trAttrs[] = sprintf('%s="%s"', $attrName, $attrValue);
          endforeach; ?>
          <th <?php echo implode(' ', $trAttrs); ?>>
            <?php echo isset($columnSettings->label) ? $columnSettings->label : ''; ?>
            <?php if ($columnName === $orderedBy): ?>
            <span class="pull-right o-order-direction">
              <i class="fa fa-angle-<?php echo $orderDir === 'DESC' ? 'down' : 'up'; ?>"></i>
            </span>
            <?php endif; ?>
          </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rowset as $row): ?>
        <tr data-id="<?php echo $row->id; ?>">
          <?php foreach ($row as $columnName => $columnValue):
            if ($columnName === 'id') continue;
            $columnSettings = $columns[$columnName];
          ?>
          <td <?php echo $columnSettings->isOrderable ? ' data-column="' . $columnName . '"'; ?>>

          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php
    $html = ob_get_contents();
    ob_clean();

    return $html
}
*/

$itemType = 'milestone';

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
            <input type="search" class="form-control" placeholder="Search..." data-column="milestone" data-compare-operator="contains">
          </div>
          <div class="form-group">
            <select id="kluster" class="form-control" data-column="assigned_to">
              <option value="">Assignee</option>
              <option value="1">ID 1</option>
              <option value="321">ID 321</option>
            </select>
          </div>
          <div class="form-group">
            <input type="text" class="form-control o-datepicker datepicker" placeholder="Start Date" id="milestones-filter-start_date">
            <input type="hidden" id="milestones-filter-start_date_timestamp" data-column="start_date" data-compare-operator=">=">
          </div>
          <div class="form-group">
            <input type="text" class="form-control o-datepicker datepicker" placeholder="End Date" id="milestones-filter-end_date">
            <input type="hidden" id="milestones-filter-end_date_timestamp" data-column="end_date" data-compare-operator="<=">
          </div>
        </form>
        <table
          id="milestones"
          class="o-data-table table table-striped table-bordered table-hover is-orderable"
          cellspacing="0"
          width="100%"
          data-type="milestone"
          data-ordered-by="milestone"
          data-order-dir="DESC"
          >
          <thead>
            <?php echo upstream_output_table_header($itemType); ?>
          </thead>
          <tbody>
            <?php /* foreach ($rowset as $row): ?>
            <tr data-id="<?php echo $row['id']; ?>">
              <td>
                <a href="#" data-toggle="up-modal" data-up-target="#milestoneModal" data-column="milestone" data-value="<?php echo $row['milestone']; ?>"><?php echo $row['milestone']; ?></a>
              </td>
              <td data-column="assigned_to" data-value="<?php echo $row['assigned_to']; ?>"><?php echo $row['assigned_to']; ?></td>
              <td data-column="tasks">0 Tasks / 0 Open</td>
              <td data-column="progress" data-value="<?php echo $row['progress']; ?>"><?php echo $row['progress']; ?>%</td>
              <td data-column="start_date" data-value="<?php echo $row['start_date']; ?>"><?php echo date('Y-m-d', $row['start_date']); ?></td>
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
            <?php endforeach; */ ?>
            <?php echo upstream_output_table_rows(get_the_ID(), $itemType); ?>
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
