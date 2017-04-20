<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
} ?>


    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2><i class="fa fa-flag"></i> <?php echo upstream_milestone_label_plural(); ?></h2>
                <ul class="nav navbar-right panel_toolbox">
                    <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                </ul>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">

                <table id="milestones" class="datatable table table-striped table-bordered dt-responsive nowrap" cellspacing="0" width="100%" data-order="[[ 4, &quot;asc&quot; ]]">
                    <thead>
                        <?php echo upstream_output_table_header( 'milestones' ); ?>
                    </thead>
                    <tbody>
                        <?php echo upstream_output_table_rows( get_the_ID(), 'milestones' ); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
