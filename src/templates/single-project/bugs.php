<?php
if (!defined('ABSPATH')) exit;
?>

<?php if (!upstream_disable_bugs() && !upstream_are_bugs_disabled()): ?>
<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">

        <div class="x_title">
            <h2><i class="fa fa-bug"></i> <?php echo upstream_bug_label_plural(); ?></h2>

            <ul class="nav navbar-right panel_toolbox">
                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                <?php do_action( 'upstream_project_bugs_top_right' ) ?>
            </ul>

            <div class="clearfix"></div>
        </div>

        <div class="x_content">

            <table id="bugs" class="table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%" data-order="[[ 4, &quot;asc&quot; ]]">
                <thead>
                    <?php echo upstream_output_table_header( 'bugs' ); ?>
                </thead>
                <tbody>
                    <?php echo upstream_output_table_rows( get_the_ID(), 'bugs' ); ?>
                </tbody>
            </table>
        </div>

    </div>
</div>
<?php endif; ?>
