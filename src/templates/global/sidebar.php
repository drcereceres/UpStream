<?php do_action( 'upstream_before_sidebar' ); ?>

<div class="col-md-3 left_col">
    <div class="left_col scroll-view">
        <div class="navbar nav_title">
            <a href="<?php echo get_bloginfo( 'url' ); ?>" class="site_title"><span><?php echo get_bloginfo( 'name' ); ?></span></a>
        </div>

        <div class="clearfix"></div>

        <!-- menu profile quick info -->
        <div class="profile">
            <div class="profile_pic">
                <img src="<?php echo upstream_current_user( 'avatar' ); ?>" alt="" class="img-circle profile_img">
            </div>
            <div class="profile_info">
                <h2><?php echo esc_html( upstream_current_user( 'fname' ) ); ?></h2>
                <p><?php echo esc_html( upstream_current_user( 'role' ) ); ?></p>
            </div>
        </div>
        <!-- /menu profile quick info -->

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">

                <h3>&nbsp;</h3>

                <ul class="nav side-menu">
                    <li><a><i class="fa fa-home"></i> <?php echo upstream_project_label_plural(); ?><span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                            <li><a href="<?php echo get_bloginfo( 'url' ); ?>/projects/"><?php printf( __( 'All %s', 'upstream' ), upstream_project_label_plural() ); ?></a></li>
                            <?php if( upstream_current_user( 'projects' ) ) {
                                foreach ( upstream_current_user( 'projects' ) as $key => $project_id ) { ?>
                                <li><a href="<?php echo get_permalink( $project_id ); ?>"><?php echo get_the_title( $project_id ); ?></a></li>
                            <?php } } ?>
                        </ul>
                    </li>
                </ul>

            </div>

            <?php if ( is_single() && get_post_type() == 'project' ) { ?>

                <div class="menu_section">

                    <h3><?php echo get_the_title( get_the_ID() ); ?></h3>

                    <ul class="nav side-menu">
                        <?php do_action( 'upstream_sidebar_before_single_menu' ); ?>
                        <li><a href="#milestones"><i class="fa fa-flag"></i> <?php echo upstream_milestone_label_plural(); ?></a></li>
                        <li><a href="#tasks"><i class="fa fa-wrench"></i> <?php echo upstream_task_label_plural(); ?></a></li>
                        <?php if( ! upstream_disable_bugs() ) { ?>
                        <li><a href="#bugs"><i class="fa fa-bug"></i> <?php echo upstream_bug_label_plural(); ?></a></li>
                        <?php do_action( 'upstream_sidebar_after_single_menu' ); ?>
                        <?php } ?>
                        <li><a href="#files"><i class="fa fa-file"></i> <?php echo upstream_file_label_plural(); ?></a></li>
                        <li>
                            <hr style="border-top-color: rgba(0, 0, 0, 0.2);" />
                        </li>
                        <li>
                            <a href="#my-tasks">
                                <i class="fa fa-wrench"></i> My <?php echo upstream_task_label_plural(); ?>
                            </a>
                        </li>
                    </ul>

                </div>

            <?php } ?>

        </div>
        <!-- /sidebar menu -->

            <!-- /menu footer buttons -->
            <div class="sidebar-footer hidden-small">
                <a href="<?php echo esc_url( get_bloginfo( 'url' ) . '/projects/' ); ?>" data-toggle="tooltip" data-placement="top" title="<?php printf( __( 'My %s', 'upstream' ), upstream_project_label_plural() ); ?>">
                    <i class="fa fa-home"></i>
                </a>
                <a href="<?php echo esc_url( 'mailto:' . upstream_admin_email() ); ?>" data-toggle="tooltip" data-placement="top" title="<?php _e( 'Send email to Admin', 'upstream' ); ?>">
                    <i class="fa fa-envelope-o"></i>
                </a>
                <a href="<?php echo esc_url( upstream_logout_url() ); ?>" data-toggle="tooltip" data-placement="top" title="<?php _e( 'Log Out', 'upstream' ); ?>">
                    <i class="fa fa-sign-out"></i>
                </a>
            </div>
            <!-- /menu footer buttons -->
          </div>
        </div>

<?php do_action( 'upstream_after_sidebar' ); ?>
