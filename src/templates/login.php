<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$login = new UpStream_Login();
upstream_get_template_part( 'global/header.php' ); ?>


    <div class="col-xs-12 col-sm-4 col-sm-offset-4">

        <div class="account-wall">

            <h3 class="text-center"><?php echo upstream_login_heading(); ?></h3>

                <?php do_action( 'upstream_login_before_form' ); ?>

                <form class="loginform" action="" method="post">

                    <input type="text" class="form-control" placeholder="<?php _e( 'Your Email', 'upstream' ); ?>" name="user_email" required autofocus>

                    <input type="password" class="form-control" placeholder="<?php _e( 'Password', 'upstream' ); ?>" name="user_password" required>

                    <input type="hidden" name="upstream_login_nonce" value="<?php echo wp_create_nonce('upstream-login-nonce'); ?>"/>
                    <input type="submit" class="btn btn-lg btn-primary btn-block" value="<?php _e( 'Sign In', 'upstream' ); ?>" name="login" />

                </form>

                <?php do_action( 'upstream_login_after_form' ); ?>

                <div class="text-center">
                    <?php echo upstream_login_text(); ?>
                </div>

        </div>

        <?php if ( $login->feedback ) { ?>
            <div class="alert alert-danger">
                <?php echo $login->feedback; ?>
            </div>
        <?php } ?>

    </div>
