<?php
if (!defined('ABSPATH')) exit;
?>

<?php if (!upstream_are_tasks_disabled() && !upstream_disable_tasks()): ?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">

        <div class="x_title">
            <h2><i class="fa fa-wrench"></i> <?php echo upstream_task_label_plural(); ?></h2>

            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                <?php do_action( 'upstream_project_tasks_top_right' ); ?>
            </ul>

            <div class="clearfix"></div>
        </div>

        <div class="x_content">

            <table id="tasks" class="datatable table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%" data-order="[[ 5, &quot;asc&quot; ]]" data-type="task">
                <thead>
                    <?php echo upstream_output_table_header( 'tasks' ); ?>
                </thead>
                <tbody>
                    <?php echo upstream_output_table_rows( get_the_ID(), 'tasks' ); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>
