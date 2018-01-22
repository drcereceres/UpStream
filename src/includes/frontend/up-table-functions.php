<?php
namespace UpStream\WIP;

function arrayToAttrs($data)
{
    $attrs = array();

    foreach ($data as $attrKey => $attrValue) {
        $attrs[] = sprintf('%s="%s"', $attrKey, esc_attr($attrValue));
    }

    return implode(' ', $attrs);
}

function getMilestonesTableColumns()
{
    $tableColumns = array(
        'milestone'   => array(
            'type'        => 'raw',
            'isOrderable' => true,
            'label'       => upstream_milestone_label()
        ),
        'assigned_to' => array(
            'type'        => 'user',
            'isOrderable' => true,
            'label'       => __('Assigned To', 'upstream')
        ),
        'tasks'       => array(
            'type'  => 'custom',
            'label' => upstream_task_label_plural(),
            'renderCallback' => function($columnName, $columnValue, $column, $row, $rowType, $projectId) {
                $tasksOpenCount = isset($row['task_open']) ? (int)$row['task_open'] : 0;
                $tasksCount = isset($row['task_count']) ? (int)$row['task_count'] : 0;

                return sprintf(
                    '%d %s / %d %s',
                    $tasksOpenCount,
                    _x('Open', '@todo', 'upstream'),
                    $tasksCount,
                    _x('Total', '@todo', 'upstream')
                );
            }
        ),
        'progress'    => array(
            'type'        => 'percentage',
            'isOrderable' => true,
            'label'       => __('Progress', 'upstream')
        ),
        'start_date'  => array(
            'type'        => 'date',
            'isOrderable' => true,
            'label'       => __('Start Date', 'upstream')
        ),
        'end_date'    => array(
            'type'        => 'date',
            'isOrderable' => true,
            'label'       => __('End Date', 'upstream')
        )
    );

    $hiddenTableColumns = array(
        'notes'       => array(
            'type'     => 'wysiwyg',
            'label'    => __('Notes', 'upstream'),
            'isHidden' => true
        ),
        'comments'    => array(
            'type'     => 'comments',
            'label'    => __('Comments'),
            'isHidden' => true
        )
    );

    $schema = array(
        'visibleColumns' => $tableColumns,
        'hiddenColumns'  => $hiddenTableColumns
    );

    return $schema;
}

function renderTableHeaderColumn($identifier, $data)
{
    $attrs = array(
        'data-column' => $identifier,
        'class'       => isset($data['class']) ? (is_array($data['class']) ? implode(' ', $data['class']) : $data['class']) : '',
    );

    $isHidden = isset($data['isHidden']) && (bool)$data['isHidden'];
    if ($isHidden) return;

    $isOrderable = isset($data['isOrderable']) && (bool)$data['isOrderable'];
    if ($isOrderable) {
        $attrs['class'] .= ' is-clickable is-orderable';
        $attrs['role'] = 'button';
        $attrs['scope'] = 'col';
    }

    // @todo
    // $attrs = apply_filters('', $attrs, $identifier);
    ?>
    <th <?php echo arrayToAttrs($attrs); ?>>
      <?php echo isset($data['label']) ? $data['label'] : ''; ?>
      <?php if ($isOrderable): ?>
        <span class="pull-right o-order-direction">
          <i class="fa fa-sort"></i>
        </span>
      <?php endif; ?>
    </th>
    <?php
}

/*
$table = array(
    'id'              => 'milestones',
    'type'            => 'milestone',
    'data-ordered-by' => 'start_date',
    'data-order-dir'  => 'DESC'
);
*/

function renderTableHeader($columns = array())
{
    ob_start(); ?>
    <thead>
      <?php if (!empty($columns)): ?>
      <tr scope="row">
        <?php
        foreach ($columns as $columnIdentifier => $column) {
            echo renderTableHeaderColumn($columnIdentifier, $column);
        }
        ?>
      </tr>
      <?php endif; ?>
    </thead>
    <?php
    $html = ob_get_contents();
    ob_end_clean();

    echo $html;
}

function renderTableColumnValue($columnName, $columnValue, $column, $row, $rowType, $projectId)
{
    $html = sprintf('<i class="s-text-color-gray">%s</i>', __('none', 'upstream'));

    $columnType = isset($column['type']) ? $column['type'] : 'raw';
    if ($columnType === 'user') {
        $columnValue = (int)$columnValue;

        if ($columnValue > 0) {
            // @todo: cache?
            $user = get_userdata($columnValue);

            if ($user instanceof \WP_User) {
                $html = esc_html($user->display_name);
            } else {
                $html = sprintf('<i class="@todo">%s</i>', __('invalid user', 'upstream'));
            }

            unset($user);
        }
    } else if ($columnType === 'percentage') {
        $html = sprintf('%d%%', (int)$columnValue);
    } else if ($columnType === 'date') {
        $columnValue = (int)$columnValue;
        if ($columnValue > 0) {
            $html = upstream_convert_UTC_date_to_timezone($columnValue, false);
        }
    } else if ($columnType === 'wysiwyg') {
        $columnValue = trim((string)$columnValue);
        if (strlen($columnValue) > 0) {
            $html = sprintf('<blockquote>%s</blockquote>', $columnValue);
        } else {
            $html = '<br>' . $html;
        }
    } else if ($columnType === 'comments') {
        $html = upstreamRenderCommentsBox($row['id'], $rowType, $projectId, false, true);
    } else if ($columnType === 'custom') {
        if (isset($column['renderCallback']) && is_callable($column['renderCallback'])) {
            $html = call_user_func($column['renderCallback'], $columnName, $columnValue, $column, $row, $rowType, $projectId);
        }
    } else {
        $columnValue = trim($columnValue);
        if (strlen($columnValue) > 0) {
            $html = esc_html($columnValue);
        }
    }

    // @todo: filter?

    echo $html;
}

function renderTableBody($data, $visibleColumnsSchema, $hiddenColumnsSchema, $rowType, $projectId)
{
    $visibleColumnsSchemaCount = count($visibleColumnsSchema);
    ob_start(); ?>
    <tbody>
      <?php if (count($data) > 0): ?>
        <?php foreach ($data as $id => $row):
        $rowAttrs = array(
            'class'   => 'is-filtered',
            'data-id' => $id
        );

        if (!empty($hiddenColumnsSchema)) {
            $rowAttrs['class'] .= ' is-expandable';
            $rowAttrs['aria-expanded'] = 'false';
        }

        $isFirst = true;
        ?>
        <tr <?php echo arrayToAttrs($rowAttrs); ?>>
          <?php foreach ($visibleColumnsSchema as $columnName => $column):
          $columnValue = isset($row[$columnName]) ? $row[$columnName] : null;

          $columnAttrs = array(
              'data-column' => $columnName,
              'data-value'  => $columnValue
          );

          if ($isFirst) {
              $columnAttrs['class'] = 'is-clickable';
              $columnAttrs['role'] = 'button';
          }

          // @todo: filter
          ?>
          <td <?php echo arrayToAttrs($columnAttrs); ?>>
            <?php if ($isFirst): ?>
            <i class="fa fa-angle-right"></i>&nbsp;
            <?php endif; ?>

            <?php renderTableColumnValue($columnName, $columnValue, $column, $row, $rowType, $projectId); ?>
          </td>
          <?php $isFirst = false; ?>
          <?php endforeach; ?>
        </tr>

        <?php if (!empty($hiddenColumnsSchema)): ?>
        <tr data-parent="<?php echo $id; ?>" aria-expanded="false" style="display: none;">
          <td colspan="<?php echo $visibleColumnsSchemaCount; ?>">
            <div class="hidden-xs">
              <?php foreach ($hiddenColumnsSchema as $columnName => $column):
              $columnValue = isset($row[$columnName]) ? $row[$columnName] : null;
              ?>
              <div class="form-group">
                <label><?php echo isset($column['label']) ? $column['label'] : ''; ?></label>
                <?php renderTableColumnValue($columnName, $columnValue, $column, $row, $rowType, $projectId); ?>
              </div>
              <?php endforeach; ?>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      <?php else: ?>
      <tr data-empty>
        <td colspan="<?php echo $visibleColumnsSchemaCount; ?>">
          <?php _e('@todo', 'upstream'); ?>
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
    <?php
    $html = ob_get_contents();
    ob_end_clean();

    echo $html;
}

function renderTable($tableAttrs = array(), $columns = array(), $data = array(), $itemType = '', $projectId = 0)
{
    $tableAttrs['class'] = array_filter(isset($tableAttrs['class']) ? (!is_array($tableAttrs['class']) ? explode(' ', $tableAttrs['class']) : (array)$tableAttrs['class']) : array());
    $tableAttrs['class'] = array_unique(array_merge($tableAttrs['class'], array(
        'o-data-table', 'table', 'table-bordered', 'table-responsive', 'table-hover', 'is-orderable'
    )));

    $tableAttrs['cellspacing'] = 0;
    $tableAttrs['width'] = '100%';

    // $tableAttrs = apply_filters('@todo', $tableAttrs);

    $visibleColumnsSchema = $columns['visibleColumns'];
    $hiddenColumnsSchema = $columns['hiddenColumns'];

    // $visibleColumnsSchema = apply_filters('@todo', $visibleColumnsSchema);
    // $hiddenColumnsSchema = apply_filters('@todo', $hiddenColumnsSchema);

    $tableAttrs['class'] = implode(' ', $tableAttrs['class']);
    ?>
    <table <?php echo arrayToAttrs($tableAttrs); ?>>
      <?php renderTableHeader($visibleColumnsSchema); ?>
      <?php renderTableBody($data, $visibleColumnsSchema, $hiddenColumnsSchema, $itemType, $projectId); ?>
    </table>
    <?php
}
