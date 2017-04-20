
    <?php
    $text = '&copy; ' . get_bloginfo( 'name' ) . ' ' . date('Y');
    $footer_text = apply_filters( 'upstream_footer_text', $text ); ?>

            <footer>
                <div class="pull-right"><?php echo esc_html( $footer_text ) ?></div>
                <div class="clearfix"></div>
            </footer>

        </div>
    </div>

    <?php wp_footer(); ?>

    </body>
</html>
