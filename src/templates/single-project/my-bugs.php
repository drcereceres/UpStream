<?php
if (!defined('ABSPATH')) exit;

$sectionTitle = upstream_bug_label_plural() . '<small>'. __(' assigned to me', 'upstream') .'</small>';
$projectId = get_the_ID();
$itemAlias = 'bugs';
?>

<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>
                <i class="fa fa-bug"></i> <?php echo $sectionTitle; ?>
            </h2>
            <ul class="nav navbar-right panel_toolbox">
                <li>
                    <a class="collapse-link">
                        <i class="fa fa-chevron-up"></i>
                    </a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <table id="my-bugs" class="datatable table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%" data-order="[[ 5, &quot;asc&quot; ]]">
                <thead>
                    <?php echo upstream_output_table_header($itemAlias); ?>
                </thead>
                <tbody>
                    <?php echo upstream_output_table_rows($projectId, $itemAlias, true); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
